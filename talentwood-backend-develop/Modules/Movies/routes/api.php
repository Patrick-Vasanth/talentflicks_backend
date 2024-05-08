<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Movies\app\Http\Controllers\MoviesController;
use Modules\Movies\app\Http\Controllers\QuizController;
use Modules\Movies\app\Http\Controllers\TicketController;

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
//     Route::get('movies', fn (Request $request) => $request->user())->name('movies');
// });



Route::get('/home/allmovielist', [MoviesController::class, 'allmovielist']);
Route::get('/home/movielist', [MoviesController::class, 'movielist']);
Route::get('/home/banners', [MoviesController::class, 'banners']);
Route::get('/home/movie/{id}', [MoviesController::class, 'moviedetails']);
Route::get('/home/search', [MoviesController::class, 'searchMovies']);
Route::get("/goldenHourMovie", [MoviesController::class, "goldenHourMovie"]);

//Ticket Booking

Route::post('/prebook', [TicketController::class, 'prebook']);
//post for admin

Route::post('/home/banners', [MoviesController::class, 'uploadbanner'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::post('/home/movielist', [MoviesController::class, 'uploadmovie'])->withOutMiddleware([usertoken::class, checkuser::class]);





//Quizz
Route::get("/comp-details", [QuizController::class, "compdetails"]);
Route::any('/quizz/test/questions', [QuizController::class, 'testingquizz']);
Route::post('/quizz/useranswer', [QuizController::class, 'quizAnswer']);
Route::post('/quizz/test/user', [QuizController::class, 'usertest']);

//Google login api 

// Route::get('auth/google', [MoviesController::class, 'redirectgoogle'])->withOutMiddleware([usertoken::class, checkuser::class]);
// Route::get('auth/google/callback', [MoviesController::class, 'googlecallback'])->withOutMiddleware([usertoken::class, checkuser::class]);
