<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class ItemController extends Controller
{
    // 使用物品
    public function useItem()
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
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
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

            $res = '';

            $item = json_decode($row->content)[0];
            $item->id = $row->id;

            if ($row->type == 1) {
                $res .= Item::useDrug($item);
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

    // 显示可回收物品
    public function recycleItemShow()
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
                        ->where('i.recycle_coin', '>', 0)
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_num', '>', 0)
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                $res .=' ------- 回收数量↓'
                    .'<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input class="js-num" name="goodnum" type="tel" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    . '<input type="button" class="action" data-url="' . URL::to('item/recycle') . '?item_id=' . $row->item_id . '" value="回收" />'
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

    // 回收物品
    public function recycle()
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

            $recycle_coin = $row->recycle_coin * $query['var_data'];

            $res = '回收：' . $row->name . '' . $query['var_data'] . '个' . '<br>获得金币：' . $recycle_coin;

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` + ' . $recycle_coin),
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $row->id)
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

    // 查看物品
    public function check()
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

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'i.*',
                ])
                ->from('item AS i')
                ->where('i.id', '=', $query['item_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该物品', 400);
            }

            $info = json_decode($row->content)[0];

            $res = '[' . $row->name . ']' . '<br>' . $row->description . '<br>';

            if ($info) {
                foreach ($info as $k => $v) {
                    if ($k == 'type'){
                        $res .= '装备方式：' . Item::englishToChinese($v) . '<br>';
                    }else {
                        $res .= Item::englishToChinese($k) . '：' . $v . '<br>';
                    }
                }
            }

            return Response::json([
                'code'    => 200,
                'message' => $res ?? '',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
