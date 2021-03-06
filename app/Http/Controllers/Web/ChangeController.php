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

class ChangeController extends Controller
{
    public function showAll()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $rows = DB::query()
                ->select([
                    'c.*',
                    'i.name AS item_name',
                    'i.level AS item_level',
                ])
                ->from('change AS c')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'c.item_id')
                    ;
                })
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.npc_id', '=', 'c.npc_id')
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
                $res .= $row->item_name . '（' . $row->item_level . '级）-- 成功率：' . $row->success_rate . '<br>';

                $res .= '<input type="button" class="action" data-url="' . URL::to('change/show') . "?change_id=" . $row->id . '" value="查看材料" /> ';

                $res .= ' <input type="button" class="action" data-url="' . URL::to('item/check') . "?item_id=" . $row->item_id . '" value="查看属性" /> ';

                $res .= ' <input type="button" class="action" data-url="' . URL::to('change/create') . "?change_id=" . $row->id . '" value="转换" />' . '<br>';
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
                'change_id' => ['required'],
            ], [
                'change_id.required' => 'change_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    'c.*',
                    'i.name AS item_name',
                ])
                ->from('change AS c')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'c.item_id')
                    ;
                })
                ->where('c.id', '=', $query['change_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该转换', 400);
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

                $res = rtrim($res, "，") . '<br>';

            }else {
                $res .= '失败保留物品=无<br>';
            }

            $res .= '成功机率=' . $row->success_rate . '% <br>';

            $res .= '<input type="button" class="action" data-url="' . URL::to('change/create') . "?change_id=" . $row->id . '" value="转换" />' . '<br>';

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
                'change_id' => ['required'],
            ], [
                'change_id.required' => 'change_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            // 判断合成是否存在
            $row = DB::query()
                ->select([
                    'c.*',
                    'i.name AS item_name',
                ])
                ->from('change AS c')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'c.item_id')
                    ;
                })
                ->where('c.id', '=', $query['change_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该转换', 400);
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

                UserKnapsack::useItems($requirements);
                throw new InvalidArgumentException('转换失败！', 400);
            }

            DB::beginTransaction();

            UserKnapsack::useItems($requirements);

            $user_vip->id = $row->item_id;
            $user_vip->num = 1;

            $data = [
                $user_vip
            ];

            UserKnapsack::addItems($data);

            DB::commit();

            $res = '恭喜您！成功转换：' . $row->item_name . '<br>';

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
