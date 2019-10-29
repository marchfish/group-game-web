<?php

namespace App\Http\Controllers\Web;

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
            // 获取物品信息
            $user_role_id = Session::get('user.account.user_role_id');

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
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：￥' . $row->coin . ' 金币 ';
                $res .='<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input style="width: 50px" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="js-num" name="goodnum" type="tel" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    .'<input type="button" class="action" data-url="' . URL::to('shop/buy') . '?item_id=' . $row->item_id . '" value="购买" />'
                    .' <input type="button" class="action" data-url="' . URL::to('item/check') . '?item_id=' . $row->item_id . '" value="查看" />'
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
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('shop AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->where('s.item_id', '=', $query['item_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商店中没有找到该物品', 400);
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
