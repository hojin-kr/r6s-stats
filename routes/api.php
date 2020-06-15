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

//프로필
Route::get('/get/profile/{key}/{isCache}','profile@getProfile');
Route::post('/get/profile','profile@getProfile');
//프로필아이디
Route::get('/get/id/{key}/{isCache}','profile@getId');
Route::post('/get/id','profile@getId');
//랭크정보
Route::get('/get/rank/{key}','rank@getRank');
Route::post('/get/rank','rank@getRank');
//오퍼레이터정보
Route::get('/get/operators/{key}','operator@getOperstors');
Route::post('/get/operators','operator@getOperstors');
//랭키리스트
Route::get('/get/rank/list/{key}/{start_timestamp}/{end_timestamp}','rank@getRankList');
Route::post('/get/rank/list','rank@getRankList');
//오퍼레이터리스트
Route::get('/get/operators/list/{key}/{start_timestamp}/{end_timestamp}','operator@getOperatorsList');
Route::post('/get/operators/list','operator@getOperatorsList');
//전체시즌
Route::get('/get/season/all/{key}','rank@getSeasonAll');
Route::post('/get/season/all','rank@getSeasonAll');

