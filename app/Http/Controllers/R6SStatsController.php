<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\R6SStats;
use Aws\Sdk;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class R6SStatsController extends Controller
{
    public static function getR6SSmallUser($name)
    {
        $raw = file_get_contents("http://localhost:8001/getSmallUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        return $raw;
    }
    public static function getR6SUser($name)
    {
        $user = R6SStats::get($name);
        if (!empty($user)) {
            return $user;
        }
        return static::refreshR6SUser($name);
    }

    public static function refreshR6SUser($name)
    {
        $raw = file_get_contents("http://localhost:8001/getUser.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $user = static::r6SJsonParser($raw);
        if (isset($user['players']['error'])) {
            return $user['players'];
        }
        $user['players']['rank_image'] = $user['players']['rankInfo']['image'];
        $user['players']['rank_name'] = $user['players']['rankInfo']['name'];
        $user['players']['update_time'] = time();
        unset($user['players']['rankInfo']);
        R6SStats::set($user['players']);
        return R6SStats::get($user['players']);
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

    private static function r6SJsonParser($json)
    {
        $row = json_decode($json, true);
        $profile_id  = array_keys($row['players'])[0];
        $result['players'] = $row['players'][$profile_id];
        $result['profile_id'] = $profile_id;
        return $result;
    }
}
