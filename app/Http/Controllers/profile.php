<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Http\Request;

class profile extends Controller
{
    // $key == $id
    public function getProfile(Request $request) : array
    {
        ['key'=>$key, 'isCache'=>$isCache] = $request;
        if (boolval($isCache)) {
            $redis = Redis::get('getProfile:'.$key);            
        }
        if (!empty($redis) && $redis != null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents(static::R6SAPIHOST."/getUser.php?id=" . $key . "&platform=uplay&appcode=".static::APPCODE);
        }
        $data = static::r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            Log::error('getProfile:일치하는 유저 찾을 수 없음', $raw);
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['nickname'] = $data['players']['nickname'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['level'] = $data['players']['level'];
        $ret['profileImg'] = 'https://ubisoft-avatars.akamaized.net/'.$key.'/default_256_256.png';
        Redis::set('getProfile:'.$key, $raw, 'EX', static::REDIS_EXPIRE);
        Log::info('Get profile' , ['raw' => $raw]);
        return $ret;
    }

    // $key == $name
    public function getId(Request $request) : array 
    {
        ['key'=>$key, 'isCache'=>$isCache] = $request;
        if (boolval($isCache)) {
            $redis = Redis::get('getId:'.$key);
        } 
        if (!empty($redis) && $redis != null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents(static::R6SAPIHOST."/getSmallUser.php?name=" . $key . "&platform=uplay&appcode=".static::APPCODE);
        }        
        $row = json_decode($raw, true);
        $id  = array_keys($row)[0];
        if ($key === $id) {
            Log::error('get Id 일치하는 유저 찾을 수 없음',['raw' => $raw]);
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['profile_id'] = $id;
        static::activeUser($id);
        static::addSchedule($id, 'seasonAllRenew');
        Redis::set('getId:'.$key, $raw, 'EX', static::REDIS_EXPIRE_ACTIVE_USER); // 30일
        Log::info('Get Id', ['name' => $key, 'id' => $id]);
        return $ret;
    }
}
