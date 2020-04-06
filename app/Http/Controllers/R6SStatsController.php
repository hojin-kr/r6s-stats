<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\R6SStats;
use Aws\Sdk;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class R6SStatsController extends Controller
{
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
        $user = static::r6SJsonPaser($raw);
        if (isset($user['error'])) {
            return $user;
        }
        $user['rank_image'] = $user['rankInfo']['image'];
        $user['rank_name'] = $user['rankInfo']['name'];
        $user['update_time'] = time();
        unset($user['rankInfo']);
        R6SStats::set($user);
        return R6SStats::get($name);
    }

    public static function getStats($name)
    {
        $raw = file_get_contents("http://localhost:8001/getStats.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        return static::r6SJsonPaser($raw);
    }

    public static function getOperators($name)
    {
        $raw = file_get_contents("http://localhost:8001/getOperators.php?name=" . $name . "&platform=uplay&appcode=r6s_api");
        $row = json_decode($raw, true);
        $profile_id = array_keys($row['players'])[0];

        R6SStats::dynamoDbPut($raw, $profile_id);
        return R6SStats::dynamoDbQuery($profile_id);
    }

    private static function r6SJsonPaser($json)
    {
        $row = json_decode($json, true);
        $profile_id = array_keys($row['players'])[0];
        return $row['players'][$profile_id];
    }
}
