<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class profile extends Controller
{
    //
    public function getProfile($id) : array
    {
        $redis = Redis::get('profile:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $id . "&platform=uplay&appcode=r6s_api");
            Redis::set('profile:'.$id, $raw, 'EX', $this->REDIS_EXPIRE);
        }
        $data = $this->r6SJsonParser($raw);
        $ret['nickname'] = $data['players']['nickname'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['level'] = $data['players']['level'];
        $ret['profileImg'] = 'https://ubisoft-avatars.akamaized.net/'.$data['profile_id'].'/default_256_256.png';
        Log::info('getR6SProfile:'.$id);
        return $ret;
    }

    public function getId($name) : array 
    {
        $redis = Redis::get('profileNameToId:'.$name);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getSmallUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
            Redis::set('profileNameToId:'.$name, $raw, 'EX', $this->REDIS_EXPIRE);
        }
        $row = json_decode($raw, true);
        $id  = array_keys($row)[0];
        $ret['profile_id'] = $id;
        $this->activeUser($id);
        $this->addSchedule($id, 'seasonAllRenew');
        Log::info('getProfileId:'.$name.':'.$id);
        return $ret;
    }
}
