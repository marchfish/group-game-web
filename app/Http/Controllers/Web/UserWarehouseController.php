<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserWarehouseController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $rows = DB::query()
                ->select([
                    'uw.*',
                    'i.name AS item_name',
                    'i.type AS item_type',
                ])
                ->from('user_warehouse AS uw')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uw.item_id')
                    ;
                })
                ->where('uw.user_role_id', '=', $user_role_id)
                ->where('uw.item_num', '>', 0)
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                $res .='<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input class="js-num" name="" onkeyup="value=value.replace(/[^\d]/g,\'\')" type="tel" style="width: 50px" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    . '<input type="button" class="action" data-url="' . URL::to('warehouse/take-out') . '?item_id=' . $row->item_id . '" value="取出" />'
                    .'</div>'
                ;

                $res .= '<br>';
            }

            if ($res == '') {
                $res = '您的仓库什么都没有!';
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

    // 显示可保存物品
    public function userKnapsackItemShow()
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
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num;

                $res .='<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input class="js-num" name="" onkeyup="value=value.replace(/[^\d]/g,\'\')" type="tel" style="width: 50px" value="1"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    . '<input type="button" class="action" data-url="' . URL::to('vip/warehouse-save') . '?item_id=' . $row->item_id . '" value="存入" />'
                    .'</div>'
                ;

                $res .= '<br>';
            }

            if ($res == '') {
                $res = '您没有可存入的物品!';
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

    // 存入物品
    public function create()
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

            $warehoues_item = DB::query()
                ->select([
                    'uw.*',
                ])
                ->from('user_warehouse AS uw')
                ->where('uw.user_role_id', '=', $user_role_id)
                ->where('uw.item_id', '=', $query['item_id'])
                ->get()
                ->first()
            ;

            $res = '成功存入：' . $row->name . '*' . $query['var_data'];

            DB::beginTransaction();

            if ($warehoues_item) {
                DB::table('user_warehouse')
                    ->where('user_role_id', '=', $user_role_id)
                    ->where('item_id', '=', $query['item_id'])
                    ->update([
                        'item_num' => DB::raw('`item_num` + ' . $query['var_data']),
                    ])
                ;
            }else {
                DB::table('user_warehouse')
                    ->insert([
                        'user_role_id' => $user_role_id,
                        'item_id'      => $query['item_id'],
                        'item_num'     => intval($query['var_data']),
                    ])
                ;
            }

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

    // 取出物品
    public function delete()
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
                    'uw.*',
                    'i.name AS item_name',
                ])
                ->from('user_warehouse AS uw')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uw.item_id')
                    ;
                })
                ->where('uw.user_role_id', '=', $user_role_id)
                ->where('uw.item_id', '=', $query['item_id'])
                ->where('uw.item_num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('仓库中没有足够的物品数量', 400);
            }

            $res = '成功取出：' . $row->item_name . '*' . $query['var_data'];

            $row->id = $query['item_id'];
            $row->num = intval($query['var_data']);

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data);

            DB::table('user_warehouse')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $query['item_id'])
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $query['var_data']),
                ])
            ;

            DB::table('user_warehouse')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_num', '<=', 0)
                ->delete()
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
}

