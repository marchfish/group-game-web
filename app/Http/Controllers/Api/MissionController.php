<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\UserKnapsack;
use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class MissionController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            if (isset($query['mission_id'])){
                $row = DB::query()
                    ->select([
                        'm.*',
                    ])
                    ->from('mission AS m')
                    ->where('m.id', '=', $query['mission_id'])
                    ->get()
                    ->first()
                ;
            }else {
                $row = DB::query()
                    ->select([
                        'm.*',
                    ])
                    ->from('mission AS m')
                    ->join('npc AS n', function ($join) {
                        $join
                            ->on('n.mission_id', '=', 'm.id')
                        ;
                    })
                    ->join('map AS mp', function ($join) {
                        $join
                            ->on('mp.npc_id', '=', 'n.id')
                        ;
                    })
                    ->where('mp.id', '=', $user_role->map_id)
                    ->get()
                    ->first()
                ;
            }

            if (!$row) {
                throw new InvalidArgumentException('该位置没有任务可接', 400);
            }

            $res = '[' . $row->name . ']\r\n';

            $res .= '描述=' . $row->description . '\r\n';

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

            // 获得金币
            $res .= '奖励金币=' . $row->coin . '\r\n';

            // 获得经验
            $res .= '奖励经验=' . $row->exp . '\r\n';

            // 获得物品奖励
            $reward = json_decode($row->reward);

            $ids = array_column($reward,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');
            $res .= '奖励物品=';

            foreach ($reward as $rew) {
                $rew->name = $items[$rew->id] ?? '？？？';
                $res .= $rew->name . '*' . $rew->num . '，';
            }

            $res = rtrim($res, "，") . '\r\n';

            // 查询是否接受过该任务
            $row1 = DB::query()
                ->select([
                    'mh.*',
                ])
                ->from('mission_history AS mh')
                ->where('mh.mission_id', '=', $row->id)
                ->where('mh.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row1) {
                $res .= '接受任务';
            }elseif($row1->status == 150) {
                $res .= '提交任务';
            }else {
                $res .= '（已完成）';
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

    // 接受任务
    public function accept()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'm.*',
                ])
                ->from('mission AS m')
                ->join('npc AS n', function ($join) {
                    $join
                        ->on('n.mission_id', '=', 'm.id')
                    ;
                })
                ->join('map AS mp', function ($join) {
                    $join
                        ->on('mp.npc_id', '=', 'n.id')
                    ;
                })
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'mp.id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('该位置并没有任务可接', 400);
            }

            $row1 = DB::query()
                ->select([
                    'mh.*',
                ])
                ->from('mission_history AS mh')
                ->where('mh.user_role_id', '=', $user_role_id)
                ->where('mh.mission_id', '=', $row->id)
                ->get()
                ->first()
            ;

            if ($row1) {
                throw new InvalidArgumentException('已接受了该任务 [' . $row->name . ']', 400);
            }

            DB::table('mission_history')->insert([
                'user_role_id' => $user_role_id,
                'mission_id' => $row->id
            ]);

            return Response::json([
                'code'    => 200,
                'message' => '成功接受任务 [' . $row->name . ']',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 提交任务
    public function submit()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            if (isset($query['mission_id'])) {
                // 判断任务是否存在
                $row = DB::query()
                    ->select([
                        'm.*',
                    ])
                    ->from('mission_history AS mh')
                    ->join('mission AS m', function ($join) {
                        $join
                            ->on('m.id', '=', 'mh.mission_id')
                        ;
                    })
                    ->where('mh.user_role_id', '=', $user_role_id)
                    ->where('mh.mission_id', '=', $query['mission_id'])
                    ->where('mh.status', '=', 150)
                    ->get()
                    ->first()
                ;
            }else {
                $row = DB::query()
                    ->select([
                        'm.*',
                    ])
                    ->from('mission_history AS mh')
                    ->join('mission AS m', function ($join) {
                        $join
                            ->on('m.id', '=', 'mh.mission_id')
                        ;
                    })
                    ->join('npc AS n', function ($join) {
                        $join
                            ->on('n.mission_id', '=', 'm.id')
                        ;
                    })
                    ->join('map AS mp', function ($join) {
                        $join
                            ->on('mp.npc_id', '=', 'n.id')
                        ;
                    })
                    ->where('mp.id', '=', $user_role->map_id)
                    ->where('mh.user_role_id', '=', $user_role_id)
                    ->where('mh.status', '=', 150)
                    ->get()
                    ->first()
                ;
            }

            if (!$row) {
                throw new InvalidArgumentException('没有找到您在进行的该任务', 400);
            }

            // 判断是否有足够的物品数量
            $requirements = json_decode($row->requirements);
            $reward = json_decode($row->reward);

            $res_bool = UserKnapsack::isHaveItems($requirements, $user_role_id);

            if (!$res_bool) {
                throw new InvalidArgumentException('没有任务所需的物品数量', 400);
            }

            DB::beginTransaction();

            UserRole::setExpAndCoinToQQ($row, $user_role_id);

            UserKnapsack::useItems($requirements, $user_role_id);

            $reward_items = UserKnapsack::addItems($reward, $user_role_id);

            DB::table('mission_history')
                ->where('user_role_id', '=', $user_role_id)
                ->where('mission_id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            if ($row->move_map_id != 0) {
                DB::table('user_role')
                    ->where('id', '=', $user_role_id)
                    ->update([
                        'map_id' => $row->move_map_id,
                    ])
                ;
            }

            DB::commit();

            $res = '恭喜您！完成任务：' . $row->name . '\r\n';

            // 获得金币
            $res .= '奖励金币=' . $row->coin . '\r\n';

            // 获得经验
            $res .= '奖励经验=' . $row->exp . '\r\n';

            // 获得物品奖励
            $res .='奖励物品:' . $reward_items . '\r\n';

            $is_up = UserRole::isUpgradeToQQ($user_role_id);

            if ($is_up != 0) {
                $res .='恭喜您！等级提升至：' . $is_up . '\r\n';
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

    // 显示已接任务
    public function userMissionShow()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $rows = DB::query()
                ->select([
                    'mh.*',
                    'm.name AS name',
                ])
                ->from('mission_history AS mh')
                ->join('mission AS m', function ($join) {
                    $join
                        ->on('m.id', '=', 'mh.mission_id')
                    ;
                })
                ->where('mh.user_role_id', '=', $user_role_id)
                ->where('mh.status', '=', 150)
                ->orderBy('mh.mission_id', 'asc')
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->mission_id . '、' . $row->name . '-----进行中 \r\n';
            }

            if ($res == '') {
                throw new InvalidArgumentException('您当前没有进行中的任务！', 400);
            }

            $res .= '输入：任务 编号\r\n输入：提交任务 编号';

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
