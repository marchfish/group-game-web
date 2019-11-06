<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use App\Models\Enemy;
use App\Models\Item;
use App\Models\UserKnapsack;

class ShopBusiness
{
    public static function update()
    {
        $shop_business = DB::query()
            ->select([
                'sb.*',
            ])
            ->from('shop_business AS sb')
            ->get()
        ;

        DB::beginTransaction();

        foreach ($shop_business as $item)
        {
            if ($item->user_role_id != 0) {
                $item->id = $item->item_id;
                UserKnapsack::addItems([0 => $item], $item->user_role_id);
            }
        }

        DB::table('shop_business')
            ->delete()
        ;

        DB::table('sys_date')
            ->where('id', '=', 1)
            ->update([
                'shop_business_expired_at' => date('Y-m-d H:i:s', strtotime('+15 day')),
            ])
        ;

        DB::commit();
    }
}
