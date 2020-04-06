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

    public static function dynamoDbPut($raw, $profile_id)
    {
        $sdk = new Sdk([
            'region'   => 'ap-northeast-2',
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $tableName = 'operators';
        $time = time();

        $item = $marshaler->marshalJson('
    {
        "profile_id": "' . $profile_id . '",
        "timestamp": '.$time.',
        "info": '.$raw.'
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

    public static function dynamoDbQuery($profile_id)
    {
        $sdk = new Sdk([
            'region' => 'ap-northeast-2',
            'version' => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $tableName = 'operators';

        $time = time();

        $eav = $marshaler->marshalJson('
    {
        ":pid": "' . $profile_id . '"
    }
');

        $params = [
            'TableName' => $tableName,
            'Select' => 'ALL_ATTRIBUTES',
            'KeyConditionExpression' => '#pid = :pid',
            'ExpressionAttributeNames' => ['#pid' => 'profile_id'],
            'ExpressionAttributeValues' => $eav
        ];

        try {
            $result = $dynamodb->query($params);
            $res = [];
            foreach ($result['Items'] as $value) {
                $res = $marshaler->unmarshalValue($value['info']);
            }
        } catch (DynamoDbException $e) {
            return $e->getMessage() . "\n";
        }
        return $res;
    }
}
