<?php

namespace App;

use Illuminate\Support\Facades\Redis;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;
use App\DynamoDb;

class Rank
{
    const TABLE = 'rank';

    public static function setRank($id, $data) {
        $db = $dynamodb = DynamoDb::init();
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

    public static function getRankList($id, $start, $end) {
        $dynamodb = $dynamodb = DynamoDb::init();
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
            $res[] = $marshaler->unmarshalItem($value)['info'];
        }
        if (empty($res)) {
            abort(403, 'There is no data during this period.');
        }
        return $res;
    }
}
