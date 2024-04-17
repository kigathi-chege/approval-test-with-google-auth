<?php

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    Profile::create([
        'user_id' => $user->id,
    ]);

    return $user;
});

Route::post('/login', function (Request $request) {
    $loginUserData = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|min:8',
    ]);
    $user = User::where('email', $loginUserData['email'])->first();
    if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
        return response()->json([
            'message' => 'Invalid Credentials',
        ], 401);
    }
    $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
    return response()->json([
        'access_token' => $token,
    ]);
});

Route::get('/profiles', function (Request $request) {
    if ($request->user()->type !== "admin") {
        throw new Exception("You are not authorized to perform this action");
    }
    return Profile::with('user')->get();
})->middleware('auth:sanctum');

Route::get('/profile', function (Request $request) {
    return $request->user()->profile;
})->middleware('auth:sanctum');

Route::put('/profile', function (Request $request) {
    return $request->user()->profile()->update($request->validate([
        'first_name' => 'required',
        'last_name' => 'required',
    ]));
})->middleware('auth:sanctum');

Route::put('/request-approval', function (Request $request) {
    return $request->user()->profile()->update(['status' => 'is_requesting_for_approval']);
})->middleware('auth:sanctum');

Route::get('/requesting-approval', function (Request $request) {
    if ($request->user()->type !== "admin") {
        throw new Exception("You are not authorized to perform this action");
    }
    return Profile::where('status', 'is_requesting_for_approval')->get();
})->middleware('auth:sanctum');

Route::put('/profile/{profile}', function (Request $request, Profile $profile) {
    if ($request->user()->type !== "admin") {
        throw new Exception("You are not authorized to perform this action");
    }
    return $profile->update($request->validate([
        'status' => 'required|in:"not_approved","has_been_approved","has_been_denied_approval","is_approval_revoked"',
    ]));
})->middleware('auth:sanctum');

Route::get('/google/callback', function (Request $request) {
    $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->stateless()->user();
    $user = User::where('email', $googleUser->email)->first();
    if (!$user) {
        $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => Hash::make(rand(100000, 999999))]);
    }

    Auth::login($user);

    $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
    return response()->json([
        'access_token' => $token,
    ]);
})->name("google.redirect");