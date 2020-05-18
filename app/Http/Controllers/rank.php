<?php

namespace App\Http\Controllers;

use App\Rank as RankModel;
use App\LineNoti;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class rank extends Controller
{
    //
    public function getRank($id) : array
    {
        $redis = Redis::get('rank:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $id . "&platform=uplay&appcode=r6s_api");
            Redis::set('rank:'.$id, $raw, 'EX', static::REDIS_EXPIRE);
            RankModel::setRank($id, $raw);
        }
        
        $data = $this->r6SJsonParser($raw);

        $ret['rank'] = $data['players']['rankInfo']['name'];
        $ret['mmr'] = $data['players']['mmr'];
        $ret['max_mmr'] = $data['players']['max_mmr'];
        $ret['wins'] = $data['players']['wins'];
        $ret['looses'] = $data['players']['losses'];
        $ret['kills'] = $data['players']['kills'];
        $ret['death'] = $data['players']['deaths'];
        $ret['season'] = $data['players']['season'];
        Log::info('getR6SRankInfo:'.$id);
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
        Log::info('getRankList:'.$id.':'.$start.':'.$end);
        return RankModel::getRankList($id, $start, $end);
    }

    //전체 시즌에 대해서 정보를 갱신
    public static function seasonAllRenew($id) 
    {
        $season = ['Black Ice', 'Dust Line', 'Skull Rain', 'Red Crow', 'Velvet Shell', 'Health', 'Blood Orchid', 
        'White Noise', 'Chimera', 'Para Bellum', 'Grim Sky', 'Wind Bastion', 'Burnt Horizon', 'Phantom Sight'
        ,'Ember Rise', 'Shifting Tides', 'Void Edge'];

        $redis = Redis::get('seasonAllRenew:'.$id);
        if ($redis !== null) {
            return true;
        } else {
            $seasonEach = [];
            try {
                foreach ($season as $key => $value) {
                    Log::info('seasonAllRenew:'.$id.':'.$value);
                    $raw = file_get_contents("http://localhost:8001/getUser.php?id=" . $id . "&platform=uplay&appcode=r6s_api&season=".($key + 1));
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
                Redis::set('seasonAllRenew:'.$id, json_encode($seasonEach), 'EX', $this->REDIS_EXPIRE_LONG);
                LineNoti::send($id.':전체시즌 정보 갱신 완료', 1);
            } catch(Exception $e) {
                Log::error('seasonAllRenew Error'.$e);
                LineNoti::send($id.':전체시즌 정보 갱신 에러', 1);
                return false;
            }
        }
        return true;
    }
}
