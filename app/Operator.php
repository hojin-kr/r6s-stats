<?php

namespace App;

use Illuminate\Support\Facades\Redis;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;

class Operator
{
    const TABLE = 'operator';

    public static function setOperators($id, $data) {
        $sdk = new Sdk([
            'region'   => 'ap-northeast-2',
            'version'  => 'latest'
        ]);
        $db = $sdk->createDynamoDB();

        $marshaler = new Marshaler();
        
        $rank['timestamp'] = time();
        $rank['profile_id'] = $id;
        $rank['data'] = $data;
        
        $item = $marshaler->marshalJson('
        {
            "profile_id":"'.$id.'", 
            "timestamp": '.time().',
            "info":'.json_encode($rank).'
        }'
        );
        $params = [
            'TableName' => static::TABLE,
            'Item' => $item
        ];

        try {
            $result = $db->putItem($params);
        } catch (DynamoDbException $e) {
            return $e->getMessage() . "\n";
        }
    }

    public static function getOperatorsList($id, $start, $end) {
        $sdk = new Sdk([
            'region' => 'ap-northeast-2',
            'version' => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();
        $marshaler = new Marshaler();

        $eav = $marshaler->marshalJson('
        {
            ":pi": "'.$id.'",
            ":start": '.$start.',
            ":end": '.$end.'
        }
        ');

        $params = [
            'TableName' => static::TABLE,
            'KeyConditionExpression' =>
            'profile_id = :pi AND #ts between :start and :end',
            'ExpressionAttributeNames'=> [ '#ts' => 'timestamp' ],
            'ExpressionAttributeValues'=> $eav
        ];
        

        try {
            $result = $dynamodb->query($params);
        } catch (DynamoDbException $e) {
            return $e->getMessage() . "\n";
        }
        foreach($result['Items'] as $value) {
            $test = $value;
            $res[] = $marshaler->unmarshalItem($value)['info'];
        }
        if (empty($res)) {
            abort(403, 'There is no data during this period.');
        }
        return $res;
    }
}
