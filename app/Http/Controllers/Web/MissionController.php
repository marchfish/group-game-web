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

class MissionController extends Controller
{
    // 显示
    public function show()
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

            $res .= '<input type="button" class="action" data-url="' . URL::to('mission/accept') . '" value="接受任务" />';

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
}
