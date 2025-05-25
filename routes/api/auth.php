<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::group(['namespace' => 'Auth', 'middleware' => ['api.request.type']], function () {

    Route::get('/registerForm', 'RegisterController@showRegistrationForm');
    Route::post('/register/step/{step}', ['as' => 'register', 'uses' => 'RegisterController@stepRegister']);
    Route::post('/login', ['as' => 'login', 'uses' => 'LoginController@login']);

    Route::post('/forget-password', ['as' => 'forgot', 'uses' => 'ForgotPasswordController@sendEmail']);
    Route::post('/reset-password/{token}', ['as' => 'updatePassword', 'uses' => 'ResetPasswordController@updatePassword']);
    Route::post('/verification', ['as' => 'verification', 'uses' => 'VerificationController@confirmCode']);
    Route::get('/google', ['as' => 'google', 'uses' => 'SocialiteController@redirectToGoogle']);
    Route::get('/facebook', ['as' => 'google', 'uses' => 'SocialiteController@redirectToFacebook']);
    Route::post('/google/callback', ['as' => 'google_callback', 'uses' => 'SocialiteController@handleGoogleCallback']);
    Route::post('/facebook/callback', ['as' => 'facebook_callback', 'uses' => 'SocialiteController@handleFacebookCallback']);

    // Route::get('auth/google', [AuthController::class, 'redirectToGoogle']); // Redirect to Google
    // Route::get('google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Route::get('/reff/{code}', 'ReferralController@referral');

    // Route::post('register', [AuthController::class, 'register']);

});

Route::middleware('api.auth')->post('logout', [AuthController::class, 'logout']);

Route::middleware('api.auth')->get('profile', [AuthController::class, 'myProfile']);
// Route::post('/logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout', 'middleware' => ['api.auth']]);
