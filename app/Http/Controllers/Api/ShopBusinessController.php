<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Models\ShopBusiness;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ShopBusinessController extends Controller
{
    public function show()
    {
        try {
            $query = Request::all();

            $sys_date = DB::query()
                ->select([
                    'sd.*',
                ])
                ->from('sys_date AS sd')
                ->get()
                ->first()
            ;

            $now_at = date('Y-m-d H:i:s',time());

            $difference = time_difference($sys_date->shop_business_expired_at, $now_at, 'second');

            if ($difference >= 0) {
                ShopBusiness::update();
                return Response::json([
                    'code'    => 400,
                    'message' => '拍卖行更新，暂无商品出售！',
                ]);
            }

            $rows = DB::query()
                ->select([
                    'sb.*',
                    'ur.name AS user_name',
                    'i.name AS item_name',
                ])
                ->from('shop_business AS sb')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'sb.user_role_id')
                    ;
                })
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sb.item_id')
                    ;
                })
                ->where('sb.num', '>', 0)
                ->orderBy('sb.created_at', 'desc')
                ->paginate($query['size'])
            ;

            $res = '[拍卖行] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->coin . ' (' . $row->num . ')\r\n';
            }

            $res .= '翻页：拍卖行 数字';

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

    public function sell()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
                'price'     => ['required', 'integer'],
                'num'       => ['required', 'integer'],
            ], [
                'item_name.required' => '物品名称不能为空',
                'price.required'     => '单价不能为空',
                'num.required'       => '数量不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if ($query['price'] <= 9) {
                throw new InvalidArgumentException('物品价格小于10金币', 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'i.*',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('i.name', '=', $query['item_name'])
                ->where('uk.item_num', '>=', $query['num'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有足够的物品数量', 400);
            }

            if ($row->recycle_coin <= 0) {
                throw new InvalidArgumentException('该物品不能出售', 400);
            }

            $res = '出售成功：' . $row->name . '*' . $query['num'];

            DB::beginTransaction();

            DB::table('shop_business')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'item_id'      => $row->id,
                    'num'          => $query['num'],
                    'coin'         => $query['price'],
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $row->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $query['num']),
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

    public function buy()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
                'price'     => ['required', 'integer'],
                'num'       => ['required', 'integer'],
            ], [
                'item_name.required' => '物品名称不能为空',
                'price.required'     => '单价不能为空',
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
                    'sb.*',
                    'i.name AS item_name',
                ])
                ->from('shop_business AS sb')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sb.item_id')
                    ;
                })
                ->where('i.name', '=', $query['item_name'])
                ->where('sb.num', '>=', $query['num'])
                ->where('sb.coin', '=', $query['price'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到对应商品，或数量不足：' . $query['num'], 400);
            }

            if ($row->user_role_id == $user_role_id) {
                throw new InvalidArgumentException('不能购买自己上架的商品!', 400);
            }

            $userRole = $user_role;

            $use_coin = $row->coin * $query['num'];

            if ($userRole->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }
            $query['shop_business_id'] = $row->id;

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

            DB::table('user_role')
                ->where('id', '=', $row->user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` + ' . (int)round($use_coin * 0.9)),
                ])
            ;

            DB::table('shop_business')
                ->where('id', '=', $query['shop_business_id'])
                ->update([
                    'num' => DB::raw('`num` - ' . $query['num']),
                ])
            ;

            DB::table('shop_business')
                ->where('num', '<=', 0)
                ->delete()
            ;

            DB::commit();

            $res = '购买成功：' . $row->item_name . '*' . $query['num'] . ' - ' . $use_coin . '金币';

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

    public function unSell()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
                'num'       => ['nullable', 'integer'],
            ], [
                'item_name.required' => '商品名称不能为空',
                'num.required'       => '数量不能为空'
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'sb.*',
                    'i.name AS item_name',
                ])
                ->from('shop_business AS sb')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sb.item_id')
                    ;
                })
                ->where('i.name', '=', $query['item_name'])
                ->where('sb.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('拍卖行中并没有找到您上架的该物品：' . $query['item_name'], 400);
            }

            if (!isset($query['num'])) {
                $query['num'] = $row->num;
            }

            if ($row->num < $query['num']) {
                throw new InvalidArgumentException('商品数量不足：' . $query['num'], 400);
            }

            $shop_business_id = $row->id;

            $row->id = $row->item_id;
            $row->num = $query['num'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data, $user_role_id);

            DB::table('shop_business')
                ->where('id', '=', $shop_business_id)
                ->update([
                    'num' => DB::raw('`num` - ' . $query['num']),
                ])
            ;

            DB::table('shop_business')
                ->where('num', '<=', 0)
                ->delete()
            ;

            DB::commit();

            $res = '下架成功：' . $row->item_name . '*' . $query['num'];

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
