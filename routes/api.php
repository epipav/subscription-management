<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\MockApiController;


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
Route::post('/register', [ApiController::class, 'register']);

Route::post('/webhook', [ApiController::class, 'show_event']);


Route::group([
    'middleware' => 'jwt'
  ], function() {
    Route::post('/purchase', [ApiController::class, 'purchase']);
    Route::post('/check_subscription', [ApiController::class, 'check_subscription']);
  });

Route::group([
    //'middleware' => 'jwt'
    'prefix' => 'mock'
  ], function() {
    Route::post('/google', [MockApiController::class, 'verify']);
    Route::post('/ios', [MockApiController::class, 'verify']);

  });
