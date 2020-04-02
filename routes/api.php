<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;
use App\R6SStats;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/refresh/user/{name}', 'R6SStatsController@refreshR6SUser');
Route::get('/get/user/{name}','R6SStatsController@getR6SUser');
Route::get('/get/operators/{name}','R6SStatsController@getOperators');
Route::get('/get/stats/{name}','R6SStatsController@getStats');
