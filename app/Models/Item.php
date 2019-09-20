<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Item
{
    // 获取物品信息
    public static function getItems(array $ids)
    {
        $items = DB::query()
            ->select([
                'i.*',
            ])
            ->from('item AS i')
            ->whereIn('i.id', $ids)
            ->get()
        ;
        return $items;
    }
}
