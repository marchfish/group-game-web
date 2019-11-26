<?php

namespace App\Http\Controllers\Api;

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
                'item_name' => ['required'],
                'num' => ['nullable', 'integer'],
            ], [
                'item_name.required' => '物品名称不能为空',
                'num.required' => '数量不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            if (!isset($query['num'])) {
                $query['num'] = 1;
            }

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
                throw new InvalidArgumentException('物品的数量不足', 400);
            }

            if ($row->content == "") {
                throw new InvalidArgumentException('该物品不可使用', 400);
            }

            $res = '';

            $item = json_decode($row->content)[0];
            $item->id = $row->id;

            if ($row->type == 1 || $row->type == 2) {
                $res .= Item::useDrugToQQ($item, $user_role_id, $query['num']);
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
                'item_name' => ['required'],
                'num' => ['nullable', 'integer'],
            ], [
                'item_name.required' => '物品名称不能为空',
                'num.required' => '数量不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $model = DB::query()
                ->select([
                    'i.*',
                    'uk.item_num as item_num',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('i.name', '=', $query['item_name'])
            ;

            if (isset($query['num'])) {
                $model->where('uk.item_num', '>=', $query['num']);
            }else {
                $model->where('uk.item_num', '>=', 1);
            }

            $row = $model->get()->first();

            if (!$row) {
                throw new InvalidArgumentException('没有足够的物品数量', 400);
            }

            if ($row->recycle_coin <= 0) {
                throw new InvalidArgumentException('该物品不能回收', 400);
            }

            if (!isset($query['num'])) {
                $query['num'] = $row->item_num;
            }

            $recycle_coin = $row->recycle_coin * $query['num'];

            $res = '回收：' . $row->name . '*' . $query['num'] . '\r\n获得金币：' . $recycle_coin;

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

    // 查看物品
    public function check()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
            ], [
                'item_name.required' => '物品名称不能为空',
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
                ->where('i.name', '=', $query['item_name'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该物品', 400);
            }

            $info = json_decode($row->content)[0];

            $res = '[' . $row->name . ']\r\n' . $row->description . '\r\n';

            if ($info) {
                foreach ($info as $k => $v) {
                    if ($k == 'type'){
                        $res .= '所需等级：' . $row->level . '\r\n';
                        $res .= '装备方式：' . Item::englishToChinese($v) . '\r\n';
                    }else {
                        $res .= Item::englishToChinese($k) . '：' . $v . '\r\n';
                    }
                }
            }

            $res = rtrim($res, '\r\n');

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
