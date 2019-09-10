<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class SysConfig
{
    public static function get($key, $default = null)
    {
        $row = DB::query()
            ->select([
                'value',
            ])
            ->from('sys_config')
            ->where('key', '=', $key)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row ? $row->value : $default;
    }

    public static function all()
    {
        $rows = DB::query()
            ->select([
                'key',
                'value',
            ])
            ->from('sys_config')
            ->get()
        ;

        return array_column(obj2arr($rows), 'value', 'key');
    }
}
