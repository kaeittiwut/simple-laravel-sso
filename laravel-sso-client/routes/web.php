<?php

use App\Http\Controllers\SSO\SSOController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/oauth/login', [SSOController::class, 'getLogin'])->name('oauth.login');

Route::get('/oauth/callback', [SSOController::class, 'getCallback'])->name('oauth.callback');

Route::get('/oauth/user', [SSOController::class, 'getUser'])->name('oauth.user');

/**
 * disable the client application's own authentication
 */
Auth::routes(['register' => false, 'reset' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
