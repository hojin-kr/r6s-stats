<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/get/user/{name}/', function ($name) {

  $userSql = DB::select('select * from r6s where nickname = ? order by id desc', [$name]);
  //mysql 갱신한지 최근이면 바로 리턴
  if (!empty($userSql) && $userSql[0]->update_time > (time() - 300)) {
    return $userSql;
  }

  $getUser = file_get_contents("http://localhost:8001/getUser.php?name=".$name."&platform=uplay&appcode=r6s_api");
  $row = json_decode($getUser, true);
  $profile_id = array_keys($row['players'])[0];
  $user = $row['players'][$profile_id];
  $keys = array_keys($user);

  $user['rank_image'] = $user['rankInfo']['image'];
  $user['rank_name']  = $user['rankInfo']['name'];
  $user['update_time'] = time();
  unset($user['rankInfo']);
  DB::table('r6s')->insert($user);

  return DB::select('select * from r6s where nickname = ? order by id desc', [$name]);
});
