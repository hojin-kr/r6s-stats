<?php

namespace App\Http\Controllers;

use App\Operator as OperatorModel;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class operator extends Controller
{
    //
    public function getOperstors($id) : array
    {
        $redis = Redis::get('operators:'.$id);
        if ($redis !== null) {
            $raw = $redis;
        } else {
            $raw = file_get_contents("http://localhost:8001/getOperators.php?id=" . $id . "&platform=uplay&appcode=r6s_api");
            Redis::set('operators:'.$id, $raw, 'EX', static::REDIS_EXPIRE);
            OperatorModel::setOperators($id, $raw);
        }
        $data = $this->r6SJsonParser($raw);
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
