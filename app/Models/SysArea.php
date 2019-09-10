<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class SysArea
{
    public static function codeToCity(string $code): string
    {
        $row = DB::query()
            ->select([
                'city',
            ])
            ->from('sys_area')
            ->where('code', '=', $code)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row ? $row->city : '';
    }

    public static function cityToCode(string $city): string
    {
        $row = DB::query()
            ->select([
                'code',
            ])
            ->from('sys_area')
            ->where('city', 'like', '%' . $city . '%')
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row ? $row->code : '';
    }
}
