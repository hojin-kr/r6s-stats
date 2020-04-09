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

Route::get('/get/user/small/{name}','R6SStatsController@getR6SSmallUser');

Route::get('/refresh/user/{name}', 'R6SStatsController@refreshR6SUser');
Route::get('/get/user/{name}','R6SStatsController@getR6SUser');
Route::get('/get/operators/{profile_id}','R6SStatsController@getOperators');
Route::get('/get/operators/{profile_id}/{start}','R6SStatsController@getOperators');

Route::get('/get/stats/{profile_id}','R6SStatsController@getStats');
Route::get('/get/stats/{profile_id}/{start}','R6SStatsController@getStats');

Route::get('/refresh/operators/{profile_id}','R6SStatsController@refreshOperators');
Route::get('/refresh/stats/{profile_id}','R6SStatsController@refreshStats');
