<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    public function getLogin(Request $request)
    {
        /**
         * using a random "state" parameter with our request to block CSRF attacks.
         */
        $request->session()->put('state', $state = Str::random(40));

        /**
         * send a simple GET request to the SSO Server,
         * to get authorization code which will provide access tokens to us.
         */
        $query = http_build_query([
            'client_id' => config('auth.client_id'),
            'redirect_uri' => config('auth.callback'),
            'response_type' => 'code',
            'scope' => config('auth.scopes'),
            'state' => $state,
        ]);

        return redirect(config('auth.sso_host') . '/oauth/authorize?' . $query);
    }

    public function getCallback(Request $request)
    {
        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        /**
         * On the exchange of authorization code, client id and client secret the SSO server
         * provided us witht the access token and refresh token.
         */
        $response = Http::asForm()->post(config('auth.sso_host') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('auth.client_id'),
            'client_secret' => config('auth.client_secret'),
            'redirect_uri' => config('auth.callback'),
            'code' => $request->code,
        ]);

        /**
         * For the smoothness of token exchange, we are storing token data
         * in the user session and using it everytime when user try to connect
         * to SSO server.
         */
        $request->session()->put($response->json());

        // return $response->json();
        return redirect(route('oauth.user'));
    }

    public function getUser(Request $request)
    {
        /**
         * For fetching data from any OAuth 2.0 server, we need to pass the access token
         * in the headers as shown below.
         */
        $accessToken = $request->session()->get("access_token");
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get(config('auth.sso_host') . '/api/user');

        $userArray = $response->json();

        try {
            $email = $userArray['email'];
        } catch (\Throwable) {
            return redirect('login')->withErrors("Failed to get login information!, Please try again.");
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User;
            $user->name = $userArray['name'];
            $user->email = $userArray['email'];
            $user->save();
        }

        Auth::login($user);
        return redirect(route('home'));
    }
}
