<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equip;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use App\Models\UserKnapsack;

class EquipController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 获取装备信息
            $row = DB::query()
                ->select([
                    'e.*',
                    DB::raw('IFNULL(`weapon`.`name`, "无") AS `weapon_name`'),
                    DB::raw('IFNULL(`helmet`.`name`, "无") AS `helmet_name`'),
                    DB::raw('IFNULL(`clothes`.`name`, "无") AS `clothes_name`'),
                    DB::raw('IFNULL(`earring`.`name`, "无") AS `earring_name`'),
                    DB::raw('IFNULL(`necklace`.`name`, "无") AS `necklace_name`'),
                    DB::raw('IFNULL(`bracelet`.`name`, "无") AS `bracelet_name`'),
                    DB::raw('IFNULL(`ring`.`name`, "无") AS `ring_name`'),
                    DB::raw('IFNULL(`shoes`.`name`, "无") AS `shoes_name`'),
                    DB::raw('IFNULL(`magic`.`name`, "无") AS `magic_weapon_name`'),
                ])
                ->from('user_equip AS e')
                ->leftJoin('item AS weapon', function ($join) {
                    $join
                        ->on('weapon.id', '=', 'e.weapon')
                    ;
                })
                ->leftJoin('item AS helmet', function ($join) {
                    $join
                        ->on('helmet.id', '=', 'e.helmet')
                    ;
                })
                ->leftJoin('item AS clothes', function ($join) {
                    $join
                        ->on('clothes.id', '=', 'e.clothes')
                    ;
                })
                ->leftJoin('item AS earring', function ($join) {
                    $join
                        ->on('earring.id', '=', 'e.earring')
                    ;
                })
                ->leftJoin('item AS necklace', function ($join) {
                    $join
                        ->on('necklace.id', '=', 'e.necklace')
                    ;
                })
                ->leftJoin('item AS bracelet', function ($join) {
                    $join
                        ->on('bracelet.id', '=', 'e.bracelet')
                    ;
                })
                ->leftJoin('item AS ring', function ($join) {
                    $join
                        ->on('ring.id', '=', 'e.ring')
                    ;
                })
                ->leftJoin('item AS shoes', function ($join) {
                    $join
                        ->on('shoes.id', '=', 'e.shoes')
                    ;
                })
                ->leftJoin('item AS magic', function ($join) {
                    $join
                        ->on('magic.id', '=', 'e.magic_weapon')
                    ;
                })
                ->where('e.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到', 400);
            }
            $res  = '[' . $user_role->name . ']\r\n';
            $res .= '武器：' . $row->weapon_name . '\r\n';
            $res .= '头盔：' . $row->helmet_name . '\r\n';
            $res .= '衣服：' . $row->clothes_name . '\r\n';
            $res .= '耳环：' . $row->earring_name . '\r\n';
            $res .= '项链：' . $row->necklace_name . '\r\n';
            $res .= '手镯：' . $row->bracelet_name . '\r\n';
            $res .= '戒指：' . $row->ring_name . '\r\n';
            $res .= '鞋子：' . $row->shoes_name . '\r\n';
            $res .= '法宝：' . $row->magic_weapon_name;

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 装备
    public function equip()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_id' => ['required'],
            ], [
                'item_id.required' => '物品id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'i.*',
                    'ur.level AS user_role_level',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'uk.user_role_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_id', '=', $query['item_id'])
                ->where('uk.item_num', '>', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('您没有该物品', 400);
            }

            if ($row->type != 10) {
                throw new InvalidArgumentException('该物品不属于装备', 400);
            }

            if ($row->user_role_level < $row->level) {
                throw new InvalidArgumentException('装备失败：等级不足' . $row->level, 400);
            }

            // 获取装备信息
            $row1 = DB::query()
                ->select([
                    'e.*',
                ])
                ->from('user_equip AS e')
                ->where('e.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row1) {
                throw new InvalidArgumentException('数据出错，请联系管理员', 400);
            }

            $res = '装备成功：' . $row->name;

            $item = json_decode($row->content)[0];
            $item->id = $query['item_id'];

            $arrRow1 = obj2arr($row1);

            $equip = $arrRow1[$item->type];

            if ($equip == $query['item_id']) {
                throw new InvalidArgumentException('已装备了相同的装备', 400);
            }

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'max_hp' => DB::raw('`max_hp` + ' . ($item->max_hp ?? 0)),
                    'max_mp' => DB::raw('`max_mp` + ' . ($item->max_mp ?? 0)),
                    'attack' => DB::raw('`attack` + ' . ($item->attack ?? 0)),
                    'magic' => DB::raw('`magic` + ' . ($item->magic ?? 0)),
                    'crit' => DB::raw('`crit` + ' . ($item->crit ?? 0)),
                    'dodge' => DB::raw('`dodge` + ' . ($item->dodge ?? 0)),
                    'defense' => DB::raw('`defense` + ' . ($item->defense ?? 0)),
                ])
            ;

            $data = $row;
            $data->num = 1;

            UserKnapsack::useItems([
                0 => $data
            ]);

            if ($equip != 0) {
                Equip::unEquip($equip);
            }

            DB::table('user_equip')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'weapon' => $item->type == 'weapon' ?  $query['item_id'] : $row1->weapon,
                    'helmet' => $item->type == 'helmet' ?  $query['item_id'] : $row1->helmet,
                    'clothes' => $item->type == 'clothes' ?  $query['item_id'] : $row1->clothes,
                    'earring' => $item->type == 'earring' ?  $query['item_id'] : $row1->earring,
                    'necklace' => $item->type == 'necklace' ?  $query['item_id'] : $row1->necklace,
                    'bracelet' => $item->type == 'bracelet' ?  $query['item_id'] : $row1->bracelet,
                    'ring' => $item->type == 'ring' ?  $query['item_id'] : $row1->ring,
                    'shoes' => $item->type == 'shoes' ?  $query['item_id'] : $row1->shoes,
                    'magic_weapon' => $item->type == 'magic_weapon' ?  $query['item_id'] : $row1->magic_weapon,
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 卸下装备
    public function unEquip()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_id' => ['required'],
            ], [
                'item_id.required' => '物品id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $res = Equip::unEquip($query['item_id']);

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
