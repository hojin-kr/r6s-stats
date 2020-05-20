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
            $raw = file_get_contents(Controller::R6SAPIHOST."/getUser.php?id=" . $id . "&platform=uplay&appcode=".Controller::APPCODE);
        }
        $data = Controller::r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            Log::error('getProfile:일치하는 유저 찾을 수 없음');
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['nickname'] = $data['players']['nickname'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['level'] = $data['players']['level'];
        $ret['profileImg'] = 'https://ubisoft-avatars.akamaized.net/'.$data['profile_id'].'/default_256_256.png';
        Redis::set('profile:'.$id, $raw, 'EX', Controller::REDIS_EXPIRE);
        Log::info('getR6SProfile:'.$id);
        return $ret;
    }

    public function getId($name) : array 
    {
        $redis = Redis::get('profileId:'.$name);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents(Controller::R6SAPIHOST."/getSmallUser.php?name=" . $name . "&platform=uplay&appcode=".Controller::APPCODE);
        }
        $row = json_decode($raw, true);
        $id  = array_keys($row)[0];
        if ($name === $id) {
            Log::error('getId:일치하는 유저 찾을 수 없음');
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        $ret['profile_id'] = $id;
        Controller::activeUser($id);
        Controller::addSchedule($id, 'seasonAllRenew');
        Redis::set('profileId:'.$name, $raw, 'EX', Controller::REDIS_EXPIRE);
        Log::info('getProfileId:'.$name.':'.$id);
        return $ret;
    }
}
