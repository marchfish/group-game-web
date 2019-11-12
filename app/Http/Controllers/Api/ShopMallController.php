<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class ShopMallController extends Controller
{
    // 显示
    public function show()
    {
        try {
            // 获取装备信息
            $rows = DB::query()
                ->select([
                    'sm.*',
                    'i.name AS item_name'
                ])
                ->from('shop_mall AS sm')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sm.item_id')
                    ;
                })
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->id . '、' . $row->item_name . '：￥' . $row->coin . ' 金币' . '\r\n';
            }

            $res .= '输入：购买 编号 数量';

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
                'shop_mall_id' => ['required', 'integer'],
                'num' => ['required', 'integer'],
            ], [
                'shop_mall_id.required' => 'shop_mall_id不能为空',
                'num.required' => '数量不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'sm.*',
                    'i.name AS item_name',
                ])
                ->from('shop_mall AS sm')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sm.item_id')
                    ;
                })
                ->where('sm.id', '=', $query['shop_mall_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商城中没有找到该物品', 400);
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
