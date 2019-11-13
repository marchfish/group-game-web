<?php

namespace App\Http\Controllers\Api;

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
use phpDocumentor\Reflection\Types\Integer;

class UserWarehouseController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

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
                ->paginate($query['size'])
            ;

            $res = '['. $user_role->name .'] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .= $row->item_name . '：' . $row->item_num . '\r\n';
            }

            $res .= '翻页：仓库 页数';

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

            $warehoues_item = DB::query()
                ->select([
                    'uw.*',
                ])
                ->from('user_warehouse AS uw')
                ->where('uw.user_role_id', '=', $user_role_id)
                ->where('uw.item_id', '=', $row->id)
                ->get()
                ->first()
            ;

            $res = '成功存入：' . $row->name . '*' . $query['num'];

            DB::beginTransaction();

            if ($warehoues_item) {
                DB::table('user_warehouse')
                    ->where('user_role_id', '=', $user_role_id)
                    ->where('item_id', '=', $row->id)
                    ->update([
                        'item_num' => DB::raw('`item_num` + ' . $query['num']),
                    ])
                ;
            }else {
                DB::table('user_warehouse')
                    ->insert([
                        'user_role_id' => $user_role_id,
                        'item_id'      => $row->id,
                        'item_num'     => $query['num'],
                    ])
                ;
            }

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

    // 取出物品
    public function delete()
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
                ->where('i.name', '=', $query['item_name'])
                ->where('uw.item_num', '>=', $query['num'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('仓库中没有足够的物品数量', 400);
            }

            $res = '成功取出：' . $row->item_name . '*' . $query['num'];

            $query['item_id'] = $row->item_id;

            $row->id = $query['item_id'];
            $row->num = $query['num'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data, $user_role_id);

            DB::table('user_warehouse')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $query['item_id'])
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $query['num']),
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

