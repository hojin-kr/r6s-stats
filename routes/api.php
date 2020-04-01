<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\R6SUser;
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

Route::get('/refresh/user/{name}', function ($name) {
  $getUser = file_get_contents("http://localhost:8001/getUser.php?name=".$name."&platform=uplay&appcode=r6s_api");
  $row = json_decode($getUser, true);
  $profile_id = array_keys($row['players'])[0];
  $user = $row['players'][$profile_id];
  $keys = array_keys($user);

  $user['rank_image'] = $user['rankInfo']['image'];
  $user['rank_name']  = $user['rankInfo']['name'];
  $user['update_time'] = time();
  unset($user['rankInfo']);
  R6SUser::set($user);
  return R6SUser::get($name);
});

Route::get('/get/user/{name}', function ($name) {
  return R6SUser::get($name);
});
