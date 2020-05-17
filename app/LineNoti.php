<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineNoti extends Model
{
    const TEST = 'TthV2zUj0paRTFpDho2bvCmAgWqun3b61rSWt9e6R5q'; //테스트
    const SYSTEM = 'SZgzswoQMXFzfCditIaNHxHJvGFk6OE2qpoI1NenaUL'; //r6s

    public static function send($message, $type = 0) : bool
    {
        switch($type) {
            //시스템 메시지
            case 0:
                exec("curl -X POST -H 'Authorization: Bearer ".static::SYSTEM."' -F 'message=".$message."' https://notify-api.line.me/api/notify");
            break;
            //테스트 메시지
            case 1:
                exec("curl -X POST -H 'Authorization: Bearer ".static::TEST."' -F 'message=".$message."' https://notify-api.line.me/api/notify");
            break;
            default:
        }
        
        return true;
    }
}
