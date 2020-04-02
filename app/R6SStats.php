<?php

namespace App;

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
}
