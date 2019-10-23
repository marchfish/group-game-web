<?php

namespace App\Http\Controllers\Web;

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
                $res .= $row->item_name . '：￥' . $row->coin . ' 金币';
                $res .='<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input style="width: 100px" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="js-num" name="goodnum" type="tel" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    . '<input type="button" class="action" data-url="' . URL::to('shop-mall/buy') . '?item_id=' . $row->item_id . '" value="购买" />'
                    .'</div>'
                ;
                $res .= '<br>';
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

    // 购买物品
    public function buy()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_id' => ['required'],
                'var_data' => ['required'],
            ], [
                'item_id.required' => '物品id不能为空',
                'var_data.required' => 'var_data不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('sm.item_id', '=', $query['item_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商城中没有找到该物品', 400);
            }

            $userRole = UserRole::getUserRole();

            $use_coin = $row->coin * $query['var_data'];

            if ($userRole->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }

            $row->id = $query['item_id'];
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

            DB::commit();

            $res = '购买：' . $row->item_name . '*' . $query['var_data'] . ' - ' . $use_coin . '金币';

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
