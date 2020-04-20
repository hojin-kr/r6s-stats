<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\R6SStats;
use Aws\Sdk;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class R6SStatsController extends Controller
{
    public static function getR6SProfile($name)
    {
        $raw = file_get_contents("http://localhost:8001/getUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $data = static::r6SJsonParser($raw);

        $res['nickname'] = $data['players']['nickname'];
        $res['mmr'] = $data['players']['mmr'];
        $res['rank'] = $data['players']['rankInfo']['name'];
        $res['level'] = $data['players']['level'];
        $res['profileImg'] = 'https://trello-attachments.s3.amazonaws.com/5e84abc437ab1e40d7b6b462/5e9dc21b5e41ba8e1ecaf361/6babcd9830f72220071bd8b4a0bedf7e/image.png';
        $res['mostPick'] = 'caveira,jakal';
        return $res;

    }

    public static function getR6SRank($name)
    {
        $raw = file_get_contents("http://localhost:8001/getUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $data = static::r6SJsonParser($raw);

        $res['rank'] = $data['players']['rankInfo']['name'];
        $res['mmr'] = $data['players']['mmr'];
        $res['wins'] = $data['players']['wins'];
        $res['looses'] = $data['players']['losses'];
        $res['kills'] = $data['players']['kills'];
        $res['death'] = $data['players']['deaths'];
        return $res;
    }

    public static function getR6SOperators($name)
    {
        $raw = file_get_contents("http://localhost:8001/getOperators.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $data = static::r6SJsonParser($raw);
        return $data['players'];
    }

    public static function refreshR6SUser($name)
    {
        $raw = file_get_contents("http://localhost:8001/getUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $user = static::r6SJsonParser($raw);
        R6SStats::set($user);
        return R6SStats::get($user);
    }

    public static function getStats($profile_id, $start = 0)
    {
        return R6SStats::dynamoTimeQuery('stats', $profile_id, $start);
    }

    public static function refreshStats($profile_id)
    {
        $raw = file_get_contents("http://localhost:8001/getStats.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api");
        $stats = static::r6SJsonParser($raw);
        R6SStats::dynamoPut('stats', $stats['players'], $stats['profile_id']);
        return $stats['players'];
    }

    public static function getOperators($profile_id, $start = 0)
    {
        return R6SStats::dynamoTimeQuery('operators', $profile_id, $start);
    }

    public static function refreshOperators($profile_id)
    {
        $raw = file_get_contents("http://localhost:8001/getOperators.php?id=" . $profile_id . "&platform=uplay&appcode=r6s_api");
        $operators = static::r6SJsonParser($raw);
        R6SStats::dynamoPut('operators', $operators['players'], $operators['profile_id']);
        return $operators['players'];
    }

    public static function bestOperator($profile_id)
    {
        $op = ['capitao', 'castle', 'vigil', 'fuze', 'echo', 'blackbeard'];

        $operators = R6SStats::dynamoTimeQuery('operators', $profile_id, 0);
        foreach($operators[0] as $operator) {
            $test[] = $operator;
        }
        return $test;
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
