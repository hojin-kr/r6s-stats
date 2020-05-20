<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class profile extends Controller
{
    //
    public function getProfile($id) : array
    {
        $redis = Redis::get('profile:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = Http::get(static::R6SAPIHOST."/getUser.php?id=" . $id . "&platform=uplay&appcode=".static::APPCODE);
        }
        $data = $this->r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['nickname'] = $data['players']['nickname'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['level'] = $data['players']['level'];
        $ret['profileImg'] = 'https://ubisoft-avatars.akamaized.net/'.$data['profile_id'].'/default_256_256.png';
        Redis::set('profile:'.$id, $raw, 'EX', static::REDIS_EXPIRE);
        Log::info('getR6SProfile:'.$id);
        return $ret;
    }

    public function getId($name) : array 
    {
        $redis = Redis::get('profileNameToId:'.$name);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = Http::get(static::R6SAPIHOST."/getSmallUser.php?name=" . $name . "&platform=uplay&appcode=".static::APPCODE);
        }
        $row = json_decode($raw, true);
        $id  = array_keys($row)[0];
        if ($name === $id) {
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['profile_id'] = $id;
        $this->activeUser($id);
        $this->addSchedule($id, 'seasonAllRenew');
        Redis::set('profileNameToId:'.$name, $raw, 'EX', static::REDIS_EXPIRE);
        Log::info('getProfileId:'.$name.':'.$id);
        return $ret;
    }
}