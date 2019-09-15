<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Item
{
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
