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
use Illuminate\Support\Facades\Log;

class R6SStatsController extends Controller
{
    const REDIS_EXPIRE_SHORT = 300; // 5분
    const REDIS_EXPIRE = 3600; // 1시간
    const REDIS_EXPIRE_LONG = 7776000; // 90일
    const REDIS_EXPIRE_ACTIVE_USER = 2592000; // 30일

    public function getR6SProfile($profile_id)
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
        Log::info('getR6SProfile:'.$profile_id);
        return $res;
    }

    public function getR6SRankInfo($profile_id)
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
        Log::info('getR6SRankInfo:'.$profile_id);
        return $res;
    }

    public function getR6SOperators($profile_id)
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
        Log::info('getR6SOperators:'.$profile_id);
        return $data['players'];
    }

    public function getProfileId($name) 
    {
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
        static::activeUser($profile_id);
        static::addSchedule($profile_id, 'seasonAllRenew');
        Log::info('getProfileId:'.$name.':'.$profile_id);
        static::lineNotify('profile_id 조회 '.$name.':'.$profile_id);
        return $result;
    }

    //전체 시즌 정보
    public function getSeasonAll($profile_id)
    {
        $data = Redis::get('seasonAll:'.$profile_id);
        if (!empty($data)) {
            return $data;
        }
        return 0;
    }

    // DB에 저장된 유저의 랭크 리스트
    public static function getRankList($profile_id, $start, $end) 
    {
        Log::info('getRankList:'.$profile_id.':'.$start.':'.$end);
        return Rank::getRankList($profile_id, $start, $end);
    }
    
    // DB에 저장된 유저의 오퍼레이터 리스트
    public static function getOperatorsList($profile_id, $start, $end) 
    {
        Log::info('getOperatorsList:'.$profile_id.':'.$start.':'.$end);
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

    //전체 시즌에 대해서 정보를 갱신
    public static function seasonAllRenew($profile_id) 
    {
        $season = ['Black Ice', 'Dust Line', 'Skull Rain', 'Red Crow', 'Velvet Shell', 'Health', 'Blood Orchid', 
        'White Noise', 'Chimera', 'Para Bellum', 'Grim Sky', 'Wind Bastion', 'Burnt Horizon', 'Phantom Sight'
        ,'Ember Rise', 'Shifting Tides', 'Void Edge'];

        $redis = Redis::get('seasonAllRenew:'.$profile_id);
        if ($redis !== null) {
            return true;
        } else {
            $seasonEach = [];
            try {
                foreach ($season as $key => $value) {
                    Log::info('seasonAllRenew:'.$profile_id.':'.$value);
                    $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api&season=".($key + 1));
                    $data = static::r6SJsonParser($raw);
                    $seasonEach[$key + 1]['rank'] = $data['players']['rankInfo']['name'];
                    $seasonEach[$key + 1]['mmr'] = $data['players']['mmr'];
                    $seasonEach[$key + 1]['max_mmr'] = $data['players']['max_mmr'];
                    $seasonEach[$key + 1]['wins'] = $data['players']['wins'];
                    $seasonEach[$key + 1]['looses'] = $data['players']['losses'];
                    $seasonEach[$key + 1]['kills'] = $data['players']['kills'];
                    $seasonEach[$key + 1]['death'] = $data['players']['deaths'];
                    $seasonEach[$key + 1]['season'] = $data['players']['season'];
                    $seasonEach[$key + 1]['season_name'] = $value;
                }
                Redis::set('seasonAllRenew:'.$profile_id, json_encode($seasonEach), 'EX', static::REDIS_EXPIRE_LONG);
                static::lineNotify($profile_id.'전체시즌 정보 갱신 완료');
            } catch(Exception $e) {
                Log::error('seasonAllRenew Error'.$e);
                return false;
            }
        }
        return true;
    }
    
    //활성유저 관리
    public static function activeUser($profile_id) 
    {
        Redis::rpush('active',$profile_id);
        Redis::expire('active', static::REDIS_EXPIRE_ACTIVE_USER); 
    }

    //백그라운드 갱신이 필요한 유저
    public static function addSchedule($profile_id, $job)
    {
        Redis::rpush('schedule:'.$job, $profile_id);
    }

    public static function lineNotify($message) : string
    {
        exec("curl -X POST -H 'Authorization: Bearer SZgzswoQMXFzfCditIaNHxHJvGFk6OE2qpoI1NenaUL' -F 'message=".$message."' https://notify-api.line.me/api/notify");
        return true;
    }
}
