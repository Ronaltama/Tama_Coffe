<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class IdGenerator
{
    public static function generate($table, $prefix)
    {
        $latestId = DB::table($table)->latest('id')->value('id');
        if (!$latestId) {
            return $prefix . '001';
        }

        $number = (int) substr($latestId, strlen($prefix)) + 1;
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
