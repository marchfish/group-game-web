<?php

namespace App\Http\Controllers\Web;

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
            $user_role_id = Session::get('user.account.user_role_id');

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
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '（' . $row->item_level . '级装备）-- 成功率：' . $row->success_rate . '<br>';

                $res .= '<input type="button" class="action" data-url="' . URL::to('synthesis/show') . "?synthesis_id=" . $row->id . '" value="查看材料" /> ';

                $res .= ' <input type="button" class="action" data-url="' . URL::to('synthesis/create') . "?synthesis_id=" . $row->id . '" value="合成" />' . '<br>';
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

    public function show()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'synthesis_id' => ['required'],
            ], [
                'synthesis_id.required' => 'synthesis_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('s.id', '=', $query['synthesis_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该合成', 400);
            }

            $res = '[' . $row->item_name . ']' . '<br>';

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

            $res = rtrim($res, "，") . '<br>';

            // 失败保留
            $retains = json_decode($row->retains);

            $ids = array_column($retains,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');
            $res .= '失败保留物品=';

            foreach ($retains as $retain) {
                $retain->name = $items[$retain->id] ?? '？？？';
                $res .= $retain->name . '*' . $retain->num . '，';
            }

            $res = rtrim($res, "，") . '<br>';

            $res .= '成功机率=' . $row->success_rate . '% <br>';

            $res .= '<input type="button" class="action" data-url="' . URL::to('synthesis/create') . "?synthesis_id=" . $row->id . '" value="合成" />' . '<br>';

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
                'synthesis_id' => ['required'],
            ], [
                'synthesis_id.required' => 'synthesis_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            // 判断合成是否存在
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
                ->where('s.id', '=', $query['synthesis_id'])
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
                $row->success_rate += $user_vip->success_rate;
            }

            // 判断是否有足够的物品数量
            $requirements = json_decode($row->requirements);
            $retains = json_decode($row->retains);

            $res_bool = UserKnapsack::isHaveItems($requirements);

            if (!$res_bool) {
                throw new InvalidArgumentException('背包中没有足够的材料', 400);
            }

            foreach ($requirements as $k => $v) {
                if ($v->id == $retains[0]->id) {
                    unset($requirements[$k]);
                }
            }

            if (!is_success($row->success_rate)) {
                UserKnapsack::useItems($requirements);
                throw new InvalidArgumentException('合成失败！', 400);
            }

            DB::beginTransaction();

            UserKnapsack::useItems($requirements);

            $retains[0]->id = $row->item_id;
            $retains[0]->num = 1;

            UserKnapsack::addItems($retains);

            DB::commit();

            $res = '恭喜您！成功合成：' . $row->item_name . '<br>';

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
