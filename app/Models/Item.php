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

        return '使用成功 <br>当前血量：' . $row->hp . '<br>' . '当前蓝量：' . $row->mp;
    }

    // 名词转换
    public static function englishToChinese($name)
    {
        switch ($name){
            case 'hp':
                return '血量';
            case 'mp':
                return '蓝量';
            case 'max_hp':
                return '血量上限';
            case 'max_mp':
                return '蓝量上限';
            case 'weapon':
                return '武器';
            case 'helmet':
                return '头盔';
            case 'clothes':
                return '衣服';
            case 'earring':
                return '耳环';
            case 'necklace':
                return '项链';
            case 'bracelet':
                return '手镯';
            case 'ring':
                return '戒指';
            case 'shoes':
                return '鞋子';
            case 'magic_weapon':
                return '法宝';
            case 'attack':
                return '攻击力';
            case 'magic':
                return '魔力';
            case 'crit':
                return '暴击';
            case 'dodge':
                return '闪避';
            case 'defense':
                return '防御力';
        }
    }

    // QQ
    // 使用药品
    public static function useDrugToQQ($item, int $user_role_id, int $num = 1)
    {
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

        $row->hp += isset($item->hp) ? $item->hp * $num : 0;
        $row->mp += isset($item->mp) ? $item->mp * $num : 0;

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
                'item_num' => DB::raw('`item_num` - ' . $num),
            ])
        ;

        DB::commit();

        return '使用成功 \r\n当前血量：' . $row->hp . '\r\n' . '当前蓝量：' . $row->mp;
    }
}
