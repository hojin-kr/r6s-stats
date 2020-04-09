<?php

namespace App;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class R6SStats extends Model
{
    //
    public static function get($name)
    {
        return DB::select('select * from r6s where nickname = ? order by id desc', [$name]);
    }

    public static function set($user)
    {
        return DB::table('r6s')->insert($user);
    }

    public static function dynamoPut($tableName, $raw, $profile_id)
    {
        $sdk = new Sdk([
            'region'   => 'ap-northeast-2',
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $time = time();
        $info = json_encode($raw);
        $item = $marshaler->marshalJson('
        {
            "profile_id": "' . $profile_id . '",
            "insert_timestamp": '.$time.',
            "info": '.$info.'
        }
        ');
        $params = [
            'TableName' => $tableName,
            'Item' => $item
        ];

        try {
            $result = $dynamodb->putItem($params);
        } catch (DynamoDbException $e) {
            return $e->getMessage() . "\n";
        }
    }

    public static function dynamoTimeQuery($tableName, $profile_id, $start)
    {
        $sdk = new Sdk([
            'region' => 'ap-northeast-2',
            'version' => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();


        $eav = $marshaler->marshalJson('
    {
        ":pi": "'.$profile_id.'",
        ":it": '.$start.'
    }
');

        $params = [
            'TableName' => $tableName,
            'KeyConditionExpression' =>
                'profile_id = :pi AND insert_timestamp > :it',
            'ExpressionAttributeValues'=> $eav
        ];

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            return $e->getMessage() . "\n";
        }
        foreach($result['Items'] as $value) {
            $res[] = $marshaler->unmarshalItem($value);
        }
        return $res;
    }
}
