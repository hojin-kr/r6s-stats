<?php

namespace App\Http\Controllers;

use App\Rank as RankModel;
use App\LineNoti;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class rank extends Controller
{
    //
    public static function getRank($id) : array
    {
        $redis = Redis::get('rank:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents(static::R6SAPIHOST."/getUser.php?id=" . $id . "&platform=uplay&appcode=".static::APPCODE);
        }
        
        $data = static::r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            Log::error('getRank 일치하는 유저 찾을 수 없음',['raw' => $raw]);
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }

        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['max_mmr'] = $data['players']['max_mmr'];
        $ret['wins'] = $data['players']['wins'];
        $ret['looses'] = $data['players']['losses'];
        $ret['kills'] = $data['players']['kills'];
        $ret['death'] = $data['players']['deaths'];
        $ret['season'] = $data['players']['season'];
        Redis::set('rank:'.$id, $raw, 'EX', static::REDIS_EXPIRE_SHORT);
        $past = Redis::get('rank:past:'.$id);
        if ($past != $raw) {
            RankModel::setRank($id, $raw);
        }
        Redis::set('rank:past:'.$id, $raw);
        //랭킹
        $nickname = $data['players']['nickname'];
        if ($ret['kills'] != 0) {
            $kd = $ret['kills']/$ret['death'];
            Redis::zadd('rank:kd', $kd, $nickname);
        }
        Redis::zadd('rank:mmr', $ret['mmr'], $nickname);
        Log::info('getR6SRankInfo',['id' => $id]);
        return $ret;
    }

    //전체 시즌 정보
    public function getSeasonAll($id)
    {
        $data = Redis::get('seasonAll:'.$id);
        if (!empty($data)) {
            return $data;
        }
        return 0;
    }

    // DB에 저장된 유저의 랭크 리스트
    public static function getRankList($id, $start, $end) 
    {
        Log::info('Get RankList', ['id'=>$id, 'start'=>$start, 'end'=>$end]);
        return RankModel::getRankList($id, $start, $end);
    }

    //전체 시즌에 대해서 정보를 갱신
    public static function seasonAllRenew($id) 
    {
        $season = ['Black Ice', 'Dust Line', 'Skull Rain', 'Red Crow', 'Velvet Shell', 'Health', 'Blood Orchid', 
        'White Noise', 'Chimera', 'Para Bellum', 'Grim Sky', 'Wind Bastion', 'Burnt Horizon', 'Phantom Sight'
        ,'Ember Rise', 'Shifting Tides', 'Void Edge'];

        $redis = Redis::get('seasonAll:'.$id);
        if ($redis !== null) {
            return true;
        } else {
            $seasonEach = [];
            foreach ($season as $key => $value) {
                Log::info('seasonAllRenew:'.$id.':'.$value);
                $raw = file_get_contents(static::R6SAPIHOST."/getUser.php?id=" . $id . "&platform=uplay&appcode=".static::APPCODE."&season=".($key + 1));
                $data = static::r6SJsonParser($raw);
                if (isset($data['players']['error'])){
                    Log::error('seasonAllRenew Error', 1);
                    LineNoti::send($id.':전체시즌 정보 갱신 에러', 1);
                }
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
            Redis::set('seasonAll:'.$id, json_encode($seasonEach), 'EX', static::REDIS_EXPIRE_LONG);
            LineNoti::send($id.':전체시즌 정보 갱신 완료', 1);
            }
        return true;
    }
}
