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

Route::get('/get/profile/{name}','R6SStatsController@getR6SProfile');
Route::get('/get/rank/{name}','R6SStatsController@getR6SRank');
Route::get('/get/operators/{name}','R6SStatsController@getR6SOperators');
