<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhonePecontroller;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('phonepe', [PhonePecontroller::class, 'phonePePayment'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::post('phonepe-response', [PhonePecontroller::class, 'callBackAction'])->withOutMiddleware([usertoken::class, checkuser::class]);
Route::post('phonepe/id', [PhonePecontroller::class, 'checkstatus'])->withOutMiddleware([usertoken::class, checkuser::class]);
