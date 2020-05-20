<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\LineNoti;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const REDIS_EXPIRE_SHORT = 300; // 5분
    const REDIS_EXPIRE = 3600; // 1시간
    const REDIS_EXPIRE_LONG = 7776000; // 90일
    const REDIS_EXPIRE_ACTIVE_USER = 2592000; // 30일
    const R6SAPIHOST = 'http://localhost:8001';
    const APPCODE = 'r6s_api';

    public static function r6SJsonParser($json)
    {
        $row = json_decode($json, true);
        $profile_id  = array_keys($row['players'])[0];
        $result['players'] = $row['players'][$profile_id];
        $result['profile_id'] = $profile_id;
        return $result;
    }
    
    //활성유저 관리
    public static function activeUser($id) 
    {
        $active = Redis::lrange('active', 0, -1);
        if (!in_array($id, $active)) {
            Redis::rpush('active',$id);
            Redis::expire('active', static::REDIS_EXPIRE_ACTIVE_USER); 
            LineNoti::send('활성 유저 추가 activeUser:'.$id, 1);
            return true;
        }
        return false;
    }

    //백그라운드 갱신이 필요한 유저
    public static function addSchedule($id, $job)
    {
        $jobs = Redis::lrange('schedule:'.$job, 0, -1);
        if (!in_array($id, $jobs)) {
            Redis::rpush('schedule:'.$job, $id);
        }
    }
}
