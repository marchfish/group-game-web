<?php

namespace App\Http\Controllers\Web;

use App\Models\Map;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class GameController extends Controller
{
    // 界面
    public function index()
    {
        return Response::view('web/game/index');
    }

    // 获取位置信息
    public function location()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    'm.name AS name',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`mforward`.`name`, "") AS `forward_name`'),
                    DB::raw('IFNULL(`mbehind`.`name`, "") AS `behind_name`'),
                    DB::raw('IFNULL(`mup`.`name`, "") AS `up_name`'),
                    DB::raw('IFNULL(`mdown`.`name`, "") AS `down_name`'),
                    DB::raw('IFNULL(`mleft`.`name`, "") AS `left_name`'),
                    DB::raw('IFNULL(`mright`.`name`, "") AS `right_name`'),
                    DB::raw('IFNULL(`n`.`mission_id`, 0) AS `mission_id`'),
                ])
                ->from('map AS m')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'm.id')
                    ;
                })
                ->leftJoin('npc AS n', function ($join) {
                    $join
                        ->on('n.id', '=', 'm.npc_id')
                    ;
                })
                ->leftJoin('enemy AS e', function ($join) {
                    $join
                        ->on('e.id', '=', 'm.enemy_id')
                    ;
                })
                ->leftJoin('map AS mforward', function ($join) {
                    $join
                        ->on('mforward.id', '=', 'm.forward')
                    ;
                })
                ->leftJoin('map AS mbehind', function ($join) {
                    $join
                        ->on('mbehind.id', '=', 'm.behind')
                    ;
                })
                ->leftJoin('map AS mup', function ($join) {
                    $join
                        ->on('mup.id', '=', 'm.up')
                    ;
                })
                ->leftJoin('map AS mdown', function ($join) {
                    $join
                        ->on('mdown.id', '=', 'm.down')
                    ;
                })
                ->leftJoin('map AS mleft', function ($join) {
                    $join
                        ->on('mleft.id', '=', 'm.left')
                    ;
                })
                ->leftJoin('map AS mright', function ($join) {
                    $join
                        ->on('mright.id', '=', 'm.right')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            $res = Map::getLocationToMessage($row);

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

    // 移动
    public function move()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'action' => ['required'],
            ], [
                'action.required' => 'action不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    'm.*',
                ])
                ->from('map AS m')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'm.id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            $move_id = 0;

            switch ($query['action']) {
                case '前':
                    $move_id = $row->forward ;
                    break;
                case '后':
                    $move_id = $row->behind;
                    break;
                case '上':
                    $move_id = $row->up;
                    break;
                case '下':
                    $move_id = $row->down;
                    break;
                case '左':
                    $move_id = $row->left;
                    break;
                case '右':
                    $move_id = $row->right;
                    break;
            }

            if ($move_id <= 0) {
                throw new InvalidArgumentException('对不起前方无路可走！', 400);
            }

            $row1 = DB::query()
                ->select([
                    'm.name AS name',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`mforward`.`name`, "") AS `forward_name`'),
                    DB::raw('IFNULL(`mbehind`.`name`, "") AS `behind_name`'),
                    DB::raw('IFNULL(`mup`.`name`, "") AS `up_name`'),
                    DB::raw('IFNULL(`mdown`.`name`, "") AS `down_name`'),
                    DB::raw('IFNULL(`mleft`.`name`, "") AS `left_name`'),
                    DB::raw('IFNULL(`mright`.`name`, "") AS `right_name`'),
                    DB::raw('IFNULL(`n`.`mission_id`, 0) AS `mission_id`'),
                ])
                ->from('map AS m')
                ->leftJoin('npc AS n', function ($join) {
                    $join
                        ->on('n.id', '=', 'm.npc_id')
                    ;
                })
                ->leftJoin('enemy AS e', function ($join) {
                    $join
                        ->on('e.id', '=', 'm.enemy_id')
                    ;
                })
                ->leftJoin('map AS mforward', function ($join) {
                    $join
                        ->on('mforward.id', '=', 'm.forward')
                    ;
                })
                ->leftJoin('map AS mbehind', function ($join) {
                    $join
                        ->on('mbehind.id', '=', 'm.behind')
                    ;
                })
                ->leftJoin('map AS mup', function ($join) {
                    $join
                        ->on('mup.id', '=', 'm.up')
                    ;
                })
                ->leftJoin('map AS mdown', function ($join) {
                    $join
                        ->on('mdown.id', '=', 'm.down')
                    ;
                })
                ->leftJoin('map AS mleft', function ($join) {
                    $join
                        ->on('mleft.id', '=', 'm.left')
                    ;
                })
                ->leftJoin('map AS mright', function ($join) {
                    $join
                        ->on('mright.id', '=', 'm.right')
                    ;
                })
                ->where('m.id', '=', $move_id)
                ->get()
                ->first()
            ;

            $res = Map::getLocationToMessage($row1);

            // 更新位置
            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'map_id' => $move_id,
                ])
            ;

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

    // 攻击
    public function attack()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            // 获取角色信息
            $user_Role = DB::query()
                ->select([
                    'ur.*',
                ])
                ->from('user_role AS ur')
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            // 获取怪物信息
            $enemy = DB::query()
                ->select([
                    'e.*',
                ])
                ->from('enemy AS e')
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.enemy_id', '=', 'e.id')
                    ;
                })
                ->where('m.id', '=', $user_Role->map_id)
                ->get()
                ->first()
            ;

            if (!$enemy) {
                throw new InvalidArgumentException('当前位置并没有怪物！', 400);
            }

            $res =  UserRole::attackToEnemy($user_Role, $enemy);

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

    // 获取状态信息
    public function userRoleStatus()
    {
        try {
            $res = '[当前状态]' . '<br>';

            $userRole = Session::get('userRole');

            $res .= '角色名：' . $userRole['name'] .'<br>';
            $res .= '血量：' . $userRole['hp'] .'<br>';
            $res .= '蓝量：' . $userRole['mp'] .'<br>';
            $res .= '攻击力：' . $userRole['attack'] .'<br>';
            $res .= '魔力：' . $userRole['magic'] .'<br>';
            $res .= '暴击：' . $userRole['crit'] .'<br>';
            $res .= '闪避：' . $userRole['dodge'] .'<br>';
            $res .= '防御力：' . $userRole['defense'] .'<br>';
            $res .= '等级：' . $userRole['level'] .'<br>';
            $res .= '经验：' . $userRole['exp'] .'<br>';
            $res .= '称号：' . $userRole['fame'] .'<br>';
            $res .= '当前位置：' . $userRole['map_id'] .'<br>';
            $res .= '血量上限：' . $userRole['max_hp'] .'<br>';
            $res .= '蓝量上限：' . $userRole['max_mp'] .'<br>';

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
