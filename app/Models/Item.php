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

    // 使用药品
    public static function useDrug($item)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        // 获取属性信息
        $row = DB::query()
            ->select([
                'ur.*',
            ])
            ->from('user_role AS ur')
            ->where('ur.id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        $row->hp += $item->hp ?? 0;
        $row->mp += $item->mp ?? 0;

        $row->hp = $row->hp > $row->max_hp ? $row->max_hp : $row->hp;
        $row->mp = $row->mp > $row->max_mp ? $row->max_mp : $row->mp;

        DB::beginTransaction();

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'hp' => $row->hp,
                'mp' => $row->mp,
            ])
        ;

        DB::table('user_knapsack')
            ->where('user_role_id', '=', $user_role_id)
            ->where('item_id', '=', $item->id)
            ->update([
                'item_num' => DB::raw('`item_num` - ' . 1),
            ])
        ;

        DB::commit();

        return '使用成功，当前血量：' . $row->hp . '<br>' . '当前蓝量：' . $row->mp;
    }
}
