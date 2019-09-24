<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class Equip
{
    // 卸下装备
    public static function unEquip(int $item_id)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        // 获取装备信息
        $equip = DB::query()
            ->select([
                'e.*',
            ])
            ->from('user_equip AS e')
            ->where('e.user_role_id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        if (!$equip) {
            throw new InvalidArgumentException('数据出错，请联系管理员', 400);
        }

        $row = DB::query()
            ->select([
                'i.*',
            ])
            ->from('item AS i')
            ->where('i.id', '=', $item_id)
            ->get()
            ->first()
        ;

        if (!$row) {
            throw new InvalidArgumentException('没有找到该装备', 400);
        }

        $item = json_decode($row->content)[0];

        DB::beginTransaction();

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'max_hp' => DB::raw('`max_hp` - ' . ($item->max_hp ?? 0)),
                'max_mp' => DB::raw('`max_mp` - ' . ($item->max_mp ?? 0)),
                'attack' => DB::raw('`attack` - ' . ($item->attack ?? 0)),
                'magic' => DB::raw('`magic` - ' . ($item->magic ?? 0)),
                'crit' => DB::raw('`crit` - ' . ($item->crit ?? 0)),
                'dodge' => DB::raw('`dodge` - ' . ($item->dodge ?? 0)),
                'defense' => DB::raw('`defense` - ' . ($item->defense ?? 0)),
            ])
        ;

        DB::table('user_equip')
            ->where('user_role_id', '=', $user_role_id)
            ->update([
                'weapon' => $item->type == 'weapon' ?  0 : $equip->weapon,
                'helmet' => $item->type == 'helmet' ?  0 : $equip->helmet,
                'clothes' => $item->type == 'clothes' ?  0 : $equip->clothes,
                'earring' => $item->type == 'earring' ?  0 : $equip->earring,
                'necklace' => $item->type == 'necklace' ?  0 : $equip->necklace,
                'bracelet' => $item->type == 'bracelet' ?  0 : $equip->bracelet,
                'ring' => $item->type == 'ring' ?  0 : $equip->ring,
                'shoes' => $item->type == 'shoes' ?  0 : $equip->shoes,
                'magic_weapon' => $item->type == 'magic_weapon' ?  0 : $equip->magic_weapon,
            ])
        ;

        $data = $equip;
        $data->num = 1;
        $data->id = $item_id;

        UserKnapsack::addItems([
            0 => $data
        ]);

        DB::commit();
    }
}
