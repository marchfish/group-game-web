<?php

namespace App\Http\Controllers\Web;

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

            $validator = Validator::make($query, [
                'mission_id' => ['required'],
            ], [
                'mission_id.required' => '任务id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    'm.*',
                ])
                ->from('mission AS m')
                ->where('m.id', '=', $query['mission_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该任务', 400);
            }

            $res = '[' . $row->name . ']' . '<br>';

            $res .= '描述=' . $row->description . '<br>';

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

            // 获得金币
            $res .= '奖励金币=' . $row->coin . '<br>';

            // 获得经验
            $res .= '奖励经验=' . $row->exp . '<br>';

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

            $res = rtrim($res, "，") . '<br>';

            // 查询是否接受过该任务
            $row1 = DB::query()
                ->select([
                    'mh.*',
                ])
                ->from('mission_history AS mh')
                ->where('mh.mission_id', '=', $query['mission_id'])
                ->where('mh.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row1) {
                $res .= '<input type="button" class="action" data-url="' . URL::to('mission/accept') . '" value="接受任务" />';
            }elseif($row1->status == 150) {
                $res .= '<input type="button" class="action" data-url="' . URL::to('mission/submit') . "?mission_id=" . $query['mission_id'] . '" value="提交任务" />';
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
            $user_role_id = Session::get('user.account.user_role_id');

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
                throw new InvalidArgumentException('找不到该任务', 400);
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

            $validator = Validator::make($query, [
                'mission_id' => ['required'],
            ], [
                'mission_id.required' => '任务id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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

            if (!$row) {
                throw new InvalidArgumentException('您没有在进行的该任务', 400);
            }

            // 判断是否有足够的物品数量
            $requirements = json_decode($row->requirements);
            $reward = json_decode($row->reward);

            $res_bool = UserKnapsack::isHaveItems($requirements);

            if (!$res_bool) {
                throw new InvalidArgumentException('没有任务所需的物品数量', 400);
            }

            DB::beginTransaction();

            UserRole::setExpAndCoin($row);

            UserKnapsack::useItems($requirements);

            $reward_items = UserKnapsack::addItems($reward);

            DB::table('mission_history')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            DB::commit();

            $res = '恭喜您！完成任务：' . $row->name . '<br>';

            // 获得金币
            $res .= '奖励金币=' . $row->coin . '<br>';

            // 获得经验
            $res .= '奖励经验=' . $row->exp . '<br>';

            // 获得物品奖励
            $res .='奖励物品:' . $reward_items . '<br>';

            $is_up = UserRole::is_upgrade();

            if ($is_up != 0) {
                $res .='恭喜您！等级提升至：' . $is_up . '<br>';
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
            $user_role_id = Session::get('user.account.user_role_id');

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
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->name . '-----进行中 ' . '<input type="button" class="action" data-url="' . URL::to('mission') . "?mission_id=" . $row->mission_id . '" value="查看任务" />';
                $res .= '<input type="button" class="action" data-url="' . URL::to('mission/submit') . "?mission_id=" . $row->mission_id . '" value="提交任务" />' . '<br>';
            }

            if ($res == '') {
                $res = '您当前没有再进行中的任务！';
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
}
