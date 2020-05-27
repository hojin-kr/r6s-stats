<?php

namespace App\Http\Controllers;

use App\Operator as OperatorModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class operator extends Controller
{
    //
    public static function getOperstors($id) : array
    {
        $redis = Redis::get('operators:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents(static::R6SAPIHOST."/getOperators.php?id=" . $id . "&platform=uplay&appcode=".static::APPCODE);
        }
        $data = static::r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            Log::error('getOperstors 일치하는 유저 찾을 수 없음', $raw);
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        Redis::set('operators:'.$id, $raw, 'EX', static::REDIS_EXPIRE);
        // todo 이전 정보와 비교해서 같은면 저장안함
        OperatorModel::setOperators($id, $raw);
        Log::info('Get operators' ,['id' => $id]);
        return static::operatorAlign($data);
    }

    // DB에 저장된 유저의 오퍼레이터 리스트
    public static function getOperatorsList($id, $start, $end) 
    {
        Log::info('Get OperatorsList', ['id'=>$id, 'start'=>$start, 'end'=>$end]);
        return OperatorModel::getOperatorsList($id, $start, $end);
    }

    //형태를 변경해서 반환
    private static function operatorAlign ($data) {
        $ret = [];
        $operators = $data['players'];
        foreach ($operators as $operator => $values) {  
            if ($operator == 'profile_id') {
                break;
            }
            $ret[$operator]['operator'] = $operator;
            foreach ($values as $index => $value) {
                switch(explode('_', $index)[1]) {
                    case 'roundlost':
                        $ret[$operator]['roundlost'] = $value;
                    break;
                    case 'death':
                        $ret[$operator]['death'] = $value;
                    break;
                    case 'roundwon':
                        $ret[$operator]['roundwon'] = $value;
                    break;
                    case 'kills':
                        $ret[$operator]['kills'] = $value;
                    break;
                    case 'timeplayed':
                        $ret[$operator]['timeplayed'] = $value;
                    break;
                    default:
                        $ret[$operator]['operators_skill'][$index] = $value;
                }
            }
        }
        return $ret;
    }
}
