<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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

    public static function addItems(array $items)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        $res = '';

        DB::beginTransaction();

        foreach ($items as $item) {
            // 查询是否存在物品
            $row = DB::query()
                ->select([
                    'i.id AS id',
                    'i.name AS name',
                    DB::raw('IFNULL(`uk`.`id`, 0) AS user_knapsack_id'),
                ])
                ->from('item AS i')
                ->leftJoin('user_knapsack AS uk', function ($join) use ($user_role_id) {
                    $join
                        ->on('uk.item_id', '=', 'i.id')
                        ->where('uk.user_role_id', '=', $user_role_id)
                    ;
                })
                ->where('i.id', '=', $item->id)
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row->user_knapsack_id != 0) {
                DB::table('user_knapsack')
                    ->where('id', '=', $row->user_knapsack_id)
                    ->update([
                        'item_num' => DB::raw('`item_num` + ' . $item->num),
                    ])
                ;

            }else {
                DB::table('user_knapsack')
                    ->insert([
                        'user_role_id' => $user_role_id,
                        'item_id'      => $item->id,
                        'item_num'     => $item->num
                    ])
                ;
            }

            $res .= $row->name . '*' . $item->num . '，';
        }

        DB::commit();

        return rtrim($res, "，");
    }
}
