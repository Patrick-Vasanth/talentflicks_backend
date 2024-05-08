<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\User\app\Http\Controllers\ContactController;
use Modules\User\app\Http\Controllers\SocialLoginController;
use Modules\User\app\Http\Controllers\ContactUsController;
use Modules\User\app\Http\Controllers\MovieRegistrationWebController;
use Modules\User\app\Http\Controllers\UserController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

// Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
//     Route::get('user', fn (Request $request) => $request->user())->name('user');
// });


Route::group(['prefix' => 'user'], function () {


    Route::post('/signup', 'UserController@store')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::post('/login', 'LoginController@login')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::post('/loginwithotp', 'LoginController@loginWithOtp')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::get('/logout', 'LoginController@logout');
    Route::post('/user/update', 'UserController@store');
    Route::get('/attributes', 'UserCommonController@getUserAttributes')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::post('/sendotp', 'UserController@requestOtp')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::post('/verifyotp', 'UserController@verifyOtp')->withOutMiddleware([usertoken::class, checkuser::class]);
    Route::post('/forgetpassword', 'UserController@selfpasswordreset')->withOutMiddleware([usertoken::class, checkuser::class]);
});



// Route::group(['prefix' => 'web'], function () {

//     Route::post('/contactus', [Contactuscontroller::class, 'contactus']);
// });

// Route::post('/contactus', 'ContactUsController@contactus')->withOutMiddleware([usertoken::class, checkuser::class]);

// Route::post("/contactus", [Contact::class, 'contactus'])->withOutMiddleware([usertoken::class, checkuser::class]);



Route::post('/web/contactus', [ContactController::class, 'contactus'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::post('/web/interested', [ContactController::class, 'interested'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::post('web/movie-registration', [MovieRegistrationWebController::class, 'movieregistration'])->withOutMiddleware([usertoken::class, checkuser::class]);

Route::resource('/user', 'UserController');


Route::get('auth/{provider}/callback', [SocialLoginController::class, 'providerCallback'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::get('auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect')->withOutMiddleware([usertoken::class, checkuser::class]);



// Route::get('auth/google', [MoviesController::class, 'redirectgoogle'])->withOutMiddleware([usertoken::class, checkuser::class]);
// Route::get('auth/google/callback', [MoviesController::class, 'googlecallback'])->withOutMiddleware([usertoken::class, checkuser::class]);

//user-t

Route::get('/user/prebookdetails/{userId}', [UserController::class, 'getUserInfo']);
