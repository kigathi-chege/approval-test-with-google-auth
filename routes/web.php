<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/google/redirect', function () {
    return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
});
