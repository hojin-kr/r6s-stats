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
            $raw = file_get_contents(Controller::R6SAPIHOST."/getOperators.php?id=" . $id . "&platform=uplay&appcode=".Controller::APPCODE);
        }
        $data = Controller::r6SJsonParser($raw);
        if (isset($data['players']['error'])){
            Log::error('getOperstors:일치하는 유저 찾을 수 없음');
            abort(400, '1:일치하는 유저를 찾을 수 없습니다.');
        }
        Redis::set('operators:'.$id, $raw, 'EX', Controller::REDIS_EXPIRE);
        OperatorModel::setOperators($id, $raw);
        Log::info('getR6SOperators:'.$id);
        return $data['players'];
    }

    // DB에 저장된 유저의 오퍼레이터 리스트
    public static function getOperatorsList($id, $start, $end) 
    {
        Log::info('getOperatorsList:'.$id.':'.$start.':'.$end);
        return OperatorModel::getOperatorsList($id, $start, $end);
    }
}
