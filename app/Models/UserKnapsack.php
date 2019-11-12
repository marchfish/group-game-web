<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserKnapsack
{
    // 添加物品
    public static function addItems(array $items, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

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

    // 使用物品
    public static function useItems(array $items, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        DB::beginTransaction();

        foreach ($items as $item) {
            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $item->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $item->num),
                ])
            ;
        }

        DB::table('user_knapsack')
            ->where('user_role_id', '=', $user_role_id)
            ->where('item_num', '<=', 0)
            ->delete()
        ;

        DB::commit();
    }

    // 判断是否有足够的物品数量
    public static function isHaveItems(array $items, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        foreach ($items as $item) {
            $my_item = DB::query()
                ->select([
                    'uk.*',
                ])
                ->from('user_knapsack AS uk')
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_id', '=', $item->id)
                ->get()
                ->first()
            ;

            if (!$my_item || $my_item->item_num < $item->num) {
                return false;
            }
        }

        return true;
    }

    // 整理物品
    public static function clearKnapsack()
    {
        $user_role_id = Session::get('user.account.user_role_id');

        DB::table('user_knapsack')
            ->where('user_role_id', '=', $user_role_id)
            ->where('item_num', '<=', 0)
            ->delete()
        ;
    }
}
