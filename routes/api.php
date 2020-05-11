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

Route::get('/get/profile/{profile_id}','R6SStatsController@getR6SProfile');
Route::get('/get/id/{name}','R6SStatsController@getProfileId');
Route::get('/get/rank/{profile_id}','R6SStatsController@getR6SRankInfo');
Route::get('/get/operators/{profile_id}','R6SStatsController@getR6SOperators');
Route::get('/get/rank/list/{profile_id}/{start_timestamp}/{end_timestamp}','R6SStatsController@getRankList');
Route::get('/get/operators/list/{profile_id}/{start_timestamp}/{end_timestamp}','R6SStatsController@getOperatorsList');