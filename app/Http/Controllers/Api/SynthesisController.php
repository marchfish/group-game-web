<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserKnapsack;
use App\Models\Item;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SynthesisController extends Controller
{
    public function showAll()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $rows = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                    'i.level AS item_level',
                ])
                ->from('synthesis AS s')
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
                throw new InvalidArgumentException('当前位置并没有合成店或没有该页!', 400);
            }

            $res = '[合成] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .= $row->item_name . '（' . $row->item_level . '级）-- 成功率：' . $row->success_rate . '\r\n';
            }

            $res .= '输入：查看合成 物品名称\r\n';

            $res .= '输入：合成 物品名称';

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

    public function show()
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

            $row = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('synthesis AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->where('i.name', '=', $query['item_name'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该合成', 400);
            }

            $res = '[' . $row->item_name . ']\r\n';

            $res .= '所需物品=';

            // 所需物品
            $requirements = json_decode($row->requirements);

            $ids = array_column($requirements,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');

            foreach ($requirements as $requirement) {
                $requirement->name = $items[$requirement->id] ?? '？？？';

                $res .= $requirement->name . '*' . $requirement->num . '，';
            }

            $res = rtrim($res, "，") . '\r\n';

            // 失败保留
            if ($row->retains != '') {
                $retains = json_decode($row->retains);

                $ids = array_column($retains,'id');

                $items = Item::getItems($ids);

                $items = array_column(obj2arr($items), 'name','id');
                $res .= '失败保留物品=';

                foreach ($retains as $retain) {
                    $retain->name = $items[$retain->id] ?? '？？？';
                    $res .= $retain->name . '*' . $retain->num . '，';
                }

                $res = rtrim($res, "，") . '\r\n';

            }else {
                $res .= '失败保留物品=无\r\n';
            }

            $res .= '成功机率=' . $row->success_rate . '%\r\n';

            $res .= '输入：合成 物品名称';

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

    public function create()
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

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('synthesis AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->where('i.name', '=', $query['item_name'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该合成', 400);
            }

            $user_vip = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($user_vip) {
                $now_at = date('Y-m-d H:i:s',time());

                $difference = time_difference($user_vip->expired_at, $now_at, 'second');

                if ($difference < 0) {
                    $row->success_rate += $user_vip->success_rate;
                }
            }

            // 判断是否有足够的物品数量
            $requirements = json_decode($row->requirements);

            $res_bool = UserKnapsack::isHaveItems($requirements, $user_role_id);

            if (!$res_bool) {
                throw new InvalidArgumentException('背包中没有足够的材料', 400);
            }

            if (!is_success($row->success_rate)) {

                if ($row->retains != '') {
                    $retains = json_decode($row->retains);
                    foreach ($requirements as $k => $v) {
                        if ($v->id == $retains[0]->id) {
                            unset($requirements[$k]);
                        }
                    }
                }

                UserKnapsack::useItems($requirements, $user_role_id);
                throw new InvalidArgumentException('合成失败！', 400);
            }

            DB::beginTransaction();

            UserKnapsack::useItems($requirements, $user_role_id);

            $user_vip->id = $row->item_id;
            $user_vip->num = 1;

            $data = [
                $user_vip
            ];

            UserKnapsack::addItems($data, $user_role_id);

            DB::commit();

            $res = '恭喜您！成功合成：' . $row->item_name;

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
