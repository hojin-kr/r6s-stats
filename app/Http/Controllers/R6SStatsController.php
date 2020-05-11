<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rank;
use App\Operators;
use App\R6SStats;
use Aws\Sdk;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Redis;

class R6SStatsController extends Controller
{
    const REDIS_EXPIRE = 300;

    public static function getR6SProfile($profile_id)
    {
        $redis = Redis::get('profile:'.$profile_id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api");
            Redis::set('profile:'.$profile_id, $raw, 'EX', static::REDIS_EXPIRE);
        }
        $data = static::r6SJsonParser($raw);
        $res['nickname'] = $data['players']['nickname'];
        $res['mmr'] = $data['players']['mmr'];
        $res['rank'] = $data['players']['rankInfo']['name'];
        $res['level'] = $data['players']['level'];
        $res['profileImg'] = 'https://ubisoft-avatars.akamaized.net/'.$data['profile_id'].'/default_256_256.png';
        
        return $res;
    }

    public static function getR6SRankInfo($profile_id)
    {
        $redis = Redis::get('rank:'.$profile_id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api");
            Redis::set('rank:'.$profile_id, $raw, 'EX', static::REDIS_EXPIRE);
            Rank::setRank($profile_id, $raw);
        }
        
        $data = static::r6SJsonParser($raw);

        $res['rank'] = $data['players']['rankInfo']['name'];
        $res['mmr'] = $data['players']['mmr'];
        $res['max_mmr'] = $data['players']['max_mmr'];
        $res['wins'] = $data['players']['wins'];
        $res['looses'] = $data['players']['losses'];
        $res['kills'] = $data['players']['kills'];
        $res['death'] = $data['players']['deaths'];
        $res['season'] = $data['players']['season'];
        return $res;
    }

    public static function getR6SOperators($profile_id)
    {
        $redis = Redis::get('operators:'.$profile_id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getOperators.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api");
            Redis::set('operators:'.$profile_id, $raw, 'EX', static::REDIS_EXPIRE);
            Operators::setOperators($profile_id, $raw);
        }
        $data = static::r6SJsonParser($raw);
        return $data['players'];
    }

    public static function getProfileId($name) {
        $redis = Redis::get('profileNameToId:'.$name);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getSmallUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
            Redis::set('profileNameToId:'.$name, $raw, 'EX', static::REDIS_EXPIRE);
        }
        $row = json_decode($raw, true);
        $profile_id  = array_keys($row)[0];
        $result['profile_id'] = $profile_id;
        return $result;
    }


    // DB에 저장된 유저의 랭크 리스트
    public static function getRankList($profile_id, $start, $end) {
        return Rank::getRankList($profile_id, $start, $end);
    }
    
    // DB에 저장된 유저의 오퍼레이터 리스트
    public static function getOperatorsList($profile_id, $start, $end) {
        return Operators::getOperatorsList($profile_id, $start, $end);
    }

    private static function r6SJsonParser($json)
    {
        $row = json_decode($json, true);
        $profile_id  = array_keys($row['players'])[0];
        $result['players'] = $row['players'][$profile_id];
        $result['profile_id'] = $profile_id;
        return $result;
    }
}
