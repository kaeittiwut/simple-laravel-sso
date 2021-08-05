<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function (Request $request) {
    /**
     * using a random "state" parameter with our request to block CSRF attacks.
     */
    $request->session()->put('state', $state = Str::random(40));

    /**
     * send a simple GET request to the SSO Server,
     * to get authorization code which will provide access tokens to us.
     */
    $query = http_build_query([
        'client_id' => '94110e0c-6400-42d8-b4a9-d6155f04cf1f',
        'redirect_uri' => 'http://localhost:8080/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
    ]);

    return redirect('http://localhost:8000/oauth/authorize?' . $query);
});

Route::get('/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );

    /**
     * On the exchange of authorization code, client id and client secret the SSO server
     * provided us witht the access token and refresh token.
     */
    $response = Http::asForm()->post('http://localhost:8000/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '94110e0c-6400-42d8-b4a9-d6155f04cf1f',
        'client_secret' => 'hebZezN6xHoCXGu96PQIScjwOWvazQUjws0Mlp7H',
        'redirect_uri' => 'http://localhost:8080/callback',
        'code' => $request->code,
    ]);

    /**
     * For the smoothness of token exchange, we are storing token data
     * in the user session and using it everytime when user try to connect
     * to SSO server.
     */
    $request->session()->put($response->json());

    // return $response->json();
    return redirect('/authuser');
});

Route::get('/authuser', function (Request $request) {
    /**
     * For fetching data from any OAuth 2.0 server, we need to pass the access token
     * in the headers as shown below.
     */
    $accessToken = $request->session()->get("access_token");
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $accessToken,
    ])->get('http://localhost:8000/api/user');

    return $response->json();
});
