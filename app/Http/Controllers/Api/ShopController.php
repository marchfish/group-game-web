<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class ShopController extends Controller
{
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $rows = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('shop AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.npc_id', '=', 's.npc_id')
                    ;
                })
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'm.id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->paginate($query['size'])
            ;

            if (count($rows->items()) <= 0) {
                throw new InvalidArgumentException('当前位置并没有商店或没有该页!', 400);
            }

            $res = '[商店] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：￥' . $row->coin . ' 金币 \r\n';
            }

            $res .= '翻页：商店 页数';

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

    // 购买物品
    public function buy()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
                'num'       => ['required', 'integer'],
            ], [
                'item_name.required' => '物品名称不能为空',
                'num.required'       => '数量不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('shop AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.npc_id', '=', 's.npc_id')
                    ;
                })
                ->where('i.name', '=', $query['item_name'])
                ->where('m.id', '=', $user_role->map_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('该位置没有商店或没有找到该物品！', 400);
            }

            $userRole = $user_role;

            $use_coin = $row->coin * $query['num'];

            if ($userRole->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }

            $row->id = $row->item_id;
            $row->num = $query['num'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data, $user_role_id);

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . $use_coin),
                ])
            ;

            DB::commit();

            $res = '购买：' . $row->item_name . '*' . $query['num'] . ' - ' . $use_coin . '金币';

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
