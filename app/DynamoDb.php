<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Aws\Sdk;

class DynamoDb extends Model
{
    //
    public static function init() {
        $sdk = new Sdk([
            'region'   => 'ap-northeast-2',
            'version'  => 'latest'
        ]);
        return $sdk->createDynamoDB();
    }
}
