<?php

namespace App\Http\Controllers\Web;

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
            $user_role_id = Session::get('user.account.user_role_id');

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
                ->from('shop_business AS sb')
                ->where('sb.num', '>', 0)
                ->orderBy('sb.created_at', 'desc')
                ->get()
            ;

            $res = '<div class="wr-color-E53E27">[拍卖行]</div>';

            foreach ($rows as $row) {
                if ($user_role_id != $row->user_role_id) {
                    $res .= $row->item_name . '：￥' . $row->coin . ' 金币 （剩余:' . $row->num . '）--- ['. $row->user_name .']';
                    $res .='<div>'
                        .'<input class="minus" name="" type="button" value="-" />'
                        .'<input style="width: 50px" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="js-num" name="goodnum" type="tel" value="1"/>'
                        .'<input class="add" name="" type="button" value="+" />'
                        . '<input type="button" class="action" data-url="' . URL::to('shop-business/buy') . '?shop_business_id=' . $row->id . '" value="购买" />'
                        .'</div>'
                    ;
                    $res .= '<br>';
                }else {
                    $res .= $row->item_name . '：￥' . $row->coin . ' 金币 （剩余:' . $row->num . '）--- ['. $row->user_name .']';
                    $res .='<div>'
                        .'<input class="minus" name="" type="button" value="-" />'
                        .'<input style="width: 50px" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="js-num" name="goodnum" type="tel" value="1"/>'
                        .'<input class="add" name="" type="button" value="+" />'
                        . '<input type="button" class="action" data-url="' . URL::to('shop-business/unsell') . '?shop_business_id=' . $row->id . '" value="下架" />'
                        .'</div>'
                    ;
                    $res .= '<br>';
                }
            }

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

    public function sellShow()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $rows = DB::query()
                ->select([
                    'uk.*',
                    'i.name AS item_name',
                    'i.type AS item_type',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_num', '>', 0)
                ->where('i.recycle_coin', '>', 0)
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                $res .='<div>'
                    .'出售单价：<input class="sell-item" onkeyup="value=value.replace(/[^\d]/g,\'\')" name="" type="tel" style="width: 50px" value="1"/><br>'
                    .'出售数量：<input class="minus" name="" type="button" value="-" />'
                    .'<input class="js-num" onkeyup="value=value.replace(/[^\d]/g,\'\')" name="" type="tel" style="width: 50px" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    .'<input type="button" class="action" data-url="' . URL::to('shop-business/sell') . '?item_id=' . $row->item_id . '" value="出售" />'
                    .'</div>'
                ;

                $res .= '<br>';
            }

            if ($res == '') {
                $res = '您没有可出售的物品!';
            }

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
                'item_id' => ['required'],
                'var_data' => ['required', 'integer'],
                'var_data1' => ['required', 'integer'],
            ], [
                'item_id.required' => '物品id不能为空',
                'var_data.required' => 'var_data不能为空',
                'var_data1.required' => 'var_data1不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if ($query['var_data1'] <= 9) {
                throw new InvalidArgumentException('物品价格小于10金币', 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('uk.item_id', '=', $query['item_id'])
                ->where('uk.item_num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有足够的物品数量', 400);
            }

            if ($row->recycle_coin <= 0) {
                throw new InvalidArgumentException('该物品不能出售', 400);
            }

            $res = '出售成功：' . $row->name . '*' . $query['var_data'];

            DB::beginTransaction();

            DB::table('shop_business')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'item_id'      => $query['item_id'],
                    'num'          => $query['var_data'],
                    'coin'         => $query['var_data1'],
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $query['item_id'])
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $query['var_data']),
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
                'shop_business_id' => ['required'],
                'var_data' => ['required', 'integer'],
            ], [
                'shop_business_id.required' => 'shop_business_id不能为空',
                'var_data.required' => 'var_data不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('sb.id', '=', $query['shop_business_id'])
                ->where('sb.num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商品数量不足：' . $query['var_data'], 400);
            }

            if ($row->user_role_id == $user_role_id) {
                throw new InvalidArgumentException('不能购买自己上架的商品!', 400);
            }

            $userRole = UserRole::getUserRole();

            $use_coin = $row->coin * $query['var_data'];

            if ($userRole->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }

            $row->id = $row->item_id;
            $row->num = $query['var_data'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data);

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
                    'num' => DB::raw('`num` - ' . $query['var_data']),
                ])
            ;

            DB::table('shop_business')
                ->where('num', '<=', 0)
                ->delete()
            ;

            DB::commit();

            $res = '购买成功：' . $row->item_name . '*' . $query['var_data'] . ' - ' . $use_coin . '金币';

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
                'shop_business_id' => ['required'],
                'var_data' => ['required', 'integer'],
            ], [
                'shop_business_id.required' => 'shop_business_id不能为空',
                'var_data.required' => 'var_data不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('sb.id', '=', $query['shop_business_id'])
                ->where('sb.num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商品数量不足：' . $query['var_data'], 400);
            }

            if ($row->user_role_id != $user_role_id) {
                throw new InvalidArgumentException('不能下架别人的商品!', 400);
            }

            $row->id = $row->item_id;
            $row->num = $query['var_data'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data);

            DB::table('shop_business')
                ->where('id', '=', $query['shop_business_id'])
                ->update([
                    'num' => DB::raw('`num` - ' . $query['var_data']),
                ])
            ;

            DB::table('shop_business')
                ->where('num', '<=', 0)
                ->delete()
            ;

            DB::commit();

            $res = '下架成功：' . $row->item_name . '*' . $query['var_data'];

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
