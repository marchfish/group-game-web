<?php

namespace App\Http\Controllers\Web;

use App\Models\Map;
use App\Models\UserRole;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class GameController extends Controller
{
    // 界面
    public function index()
    {
        $user_role_id = Session::get('user.account.user_role_id');

        $rows = DB::query()
            ->select([
                's.*',
                'us.quick_key as quick_key',
            ])
            ->from('skill AS s')
            ->join('user_skill AS us', function ($join) {
                $join
                    ->on('us.skill_id', '=', 's.id')
                ;
            })
            ->where('us.user_role_id', '=', $user_role_id)
            ->get()
        ;

        $drugs = DB::query()
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
            ->where('uk.item_num', '>', 0)
            ->whereIn('i.type', [1, 2, 3])
            ->get()
        ;

        return Response::view('web/game/index', [
            'rows' => $rows,
            'drugs' => $drugs,
        ]);
    }

    // 获取位置信息
    public function location()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    'm.name AS name',
                    'm.description AS description',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`e`.`img`, "") AS `enemy_img`'),
                    DB::raw('IFNULL(`e`.`level`, 0) AS `enemy_level`'),
                    DB::raw('IFNULL(`mforward`.`name`, "") AS `forward_name`'),
                    DB::raw('IFNULL(`mbehind`.`name`, "") AS `behind_name`'),
                    DB::raw('IFNULL(`mup`.`name`, "") AS `up_name`'),
                    DB::raw('IFNULL(`mdown`.`name`, "") AS `down_name`'),
                    DB::raw('IFNULL(`mleft`.`name`, "") AS `left_name`'),
                    DB::raw('IFNULL(`mright`.`name`, "") AS `right_name`'),
                    DB::raw('IFNULL(`n`.`mission_id`, 0) AS `mission_id`'),
                    DB::raw('IFNULL(`n`.`type`, 0) AS `npc_type`'),
                    DB::raw('IFNULL(`ed`.`hour`, "") AS `enemy_hour`'),
                    DB::raw('IFNULL(`ed`.`refresh_at`, "") AS `enemy_refresh_at`'),
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
                ->leftJoin('enemy_date AS ed', function ($join) {
                    $join
                        ->on('ed.enemy_id', '=', 'm.enemy_id')
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
                    'm.description AS description',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`e`.`img`, "") AS `enemy_img`'),
                    DB::raw('IFNULL(`e`.`level`, 0) AS `enemy_level`'),
                    DB::raw('IFNULL(`mforward`.`name`, "") AS `forward_name`'),
                    DB::raw('IFNULL(`mbehind`.`name`, "") AS `behind_name`'),
                    DB::raw('IFNULL(`mup`.`name`, "") AS `up_name`'),
                    DB::raw('IFNULL(`mdown`.`name`, "") AS `down_name`'),
                    DB::raw('IFNULL(`mleft`.`name`, "") AS `left_name`'),
                    DB::raw('IFNULL(`mright`.`name`, "") AS `right_name`'),
                    DB::raw('IFNULL(`n`.`mission_id`, 0) AS `mission_id`'),
                    DB::raw('IFNULL(`n`.`type`, 0) AS `npc_type`'),
                    DB::raw('IFNULL(`ed`.`hour`, "") AS `enemy_hour`'),
                    DB::raw('IFNULL(`ed`.`refresh_at`, "") AS `enemy_refresh_at`'),
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
                ->leftJoin('enemy_date AS ed', function ($join) {
                    $join
                        ->on('ed.enemy_id', '=', 'm.enemy_id')
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

            $user_date = UserRole::getUserDate();

            if ($user_date){
                if ($user_date->attack_at) {
                    $now_at = strtotime('now');
                    $attack_at = strtotime($user_date->attack_at);

                    if($now_at - $attack_at < 1) {
                        throw new InvalidArgumentException('', 400);
                    };
                }

                DB::table('user_date')
                    ->where('user_role_id', '=', $user_role_id)
                    ->update([
                        'attack_at' => date('Y-m-d H:i:s', time()),
                    ]);
            }

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
                    DB::raw('IFNULL(`ed`.`hour`, "") AS `enemy_hour`'),
                    DB::raw('IFNULL(`ed`.`refresh_at`, "") AS `enemy_refresh_at`'),
                    DB::raw('IFNULL(`ed`.`refresh_minute`, "") AS `enemy_refresh_minute`'),
                ])
                ->from('enemy AS e')
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.enemy_id', '=', 'e.id')
                    ;
                })
                ->leftJoin('enemy_date AS ed', function ($join) {
                    $join
                        ->on('ed.enemy_id', '=', 'e.id')
                    ;
                })
                ->where('m.id', '=', $user_Role->map_id)
                ->get()
                ->first()
            ;

            if (!$enemy) {
                throw new InvalidArgumentException('当前位置并没有怪物！', 400);
            }

            if ($enemy->enemy_hour != '' && strpos($enemy->enemy_hour, date('H', time())) === false) {
                throw new InvalidArgumentException('当前位置并没有怪物！', 400);
            }

            if ($enemy->enemy_refresh_at != '' &&  time() < strtotime($enemy->enemy_refresh_at)) {
                throw new InvalidArgumentException('怪物还未出现，刷新时间：' . date('H:i:s', strtotime($enemy->enemy_refresh_at)), 400);
            }

            $enemy->max_hp = $enemy->hp;

            $res =  UserRole::attackToEnemy($user_Role, $enemy);

            $user_vip = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($user_vip && time() < strtotime($user_vip->expired_at)) {

                $user_Role1 = DB::query()
                    ->select([
                        'ur.*',
                    ])
                    ->from('user_role AS ur')
                    ->where('ur.id', '=', $user_role_id)
                    ->get()
                    ->first()
                ;

                if ($user_Role1->hp > 0 && $user_Role1->hp < $user_vip->protect_hp) {
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
                        ->where('i.type', '=', 1)
                        ->where('uk.item_num', '>', 0)
                        ->get()
                        ->first()
                    ;

                    if (!$row) {
                        $res .= '<br>您的血瓶没有了!';
                    }else {
                        $item = json_decode($row->content)[0];
                        $item->id = $row->id;

                        Item::useDrug($item);
                    }
                }
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

    // 复活
    public function revive()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            // 获取角色信息
            $user_Role = DB::query()
                ->select([
                    'ur.*',
                    'sf.map_id AS revive_map_id',
                    'sf.coin AS revive_coin',
                ])
                ->from('user_role AS ur')
                ->join('sys_fame AS sf', function ($join) {
                    $join
                        ->on('sf.id', '=', 'ur.fame_id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($user_Role->hp > 0) {
                throw new InvalidArgumentException('您并不需要复活！', 400);
            }

            if ($user_Role->coin < $user_Role->revive_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $user_Role->revive_coin, 400);
            }

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin'   => DB::raw('`coin` - ' . $user_Role->revive_coin),
                    'hp'     => $user_Role->max_hp,
                    'map_id' => $user_Role->revive_map_id != 0 ? $user_Role->revive_map_id : $user_Role->map_id,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '复活成功，-' . $user_Role->revive_coin . '金币',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 排行榜
    public function ranking()
    {
        try {
            $res = '<div class="wr-color-E53E27">等级排行前10</div>';

            $rows = DB::query()
                ->select([
                    'ur.*'
                ])
                ->from('user_role AS ur')
                ->where('id', '<>', 1)
                ->limit(10)
                ->orderBy('level', 'desc')
                ->get()
            ;

            foreach ($rows as $row) {
                $res .= $row->name . ' ：' . $row->level . '<br>';
            }

            $rows1 = DB::query()
                ->select([
                    'ur.*'
                ])
                ->from('user_role AS ur')
                ->where('id', '<>', 1)
                ->limit(10)
                ->orderBy('attack', 'desc')
                ->get()
            ;

            $res .= '<div class="wr-color-E53E27">攻击排行前10</div>';

            foreach ($rows1 as $row1) {
                $res .=  $row1->name . ' ：' . $row1->attack . '<br>';
            }

            $rows2 = DB::query()
                ->select([
                    'ur.*'
                ])
                ->from('user_role AS ur')
                ->where('id', '<>', 1)
                ->limit(10)
                ->orderBy('defense', 'desc')
                ->get()
            ;

            $res .= '<div class="wr-color-E53E27">防御排行前10</div>';

            foreach ($rows2 as $row2) {
                $res .=  $row2->name . ' ：' . $row2->defense . '<br>';
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

    // 查看彩票
    public function lotteryShow()
    {
        try {
            $res = '';

            $row = DB::query()
                ->select([
                    'l.*',
                    'ur.name AS user_name',
                ])
                ->from('lottery AS l')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'l.user_role_id')
                    ;
                })
                ->where('user_role_id', '<>', 0)
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row) {
                $res .= '<div class="wr-color-E53E27">上次中奖：[' . $row->user_name . '] 获得' . $row->coin . '金币--('. $row->number .')</div>';
            }else {
                $res .= '上次中奖：无人中奖<br>';
            }

            $last = DB::query()
                ->select([
                    '*'
                ])
                ->from('lottery AS l')
                ->where('number', '<>', '')
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($last) {
                $res .= '上期开奖号码：'. $last->number .'<br><br>';
            }

            $row1 = DB::query()
                ->select([
                    '*'
                ])
                ->from('lottery AS l')
                ->where('number', '=', '')
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get()
                ->first()
            ;

            $res .= '第' . $row1->stage . '期<br>';

            $row2 = DB::query()
                ->select([
                    '*'
                ])
                ->from('sys_coin AS sc')
                ->limit(1)
                ->get()
                ->first()
            ;

            $res .= '<div class="wr-color-E53E27">奖池：' . $row2->lottery_coin . '</div><br>';
            $res .= '注：每人每天仅限购买一次，花费：1000/次';

            $res .='<div>'
                .'<input class="js-num" onkeyup="value=value.replace(/[^\d]/g,\'\')" name="goodnum" minlength="3" maxlength="3" type="text" style="width: 50px" value="000"/><br>'
                . '<input type="button" class="action" data-url="' . URL::to('lottery/buy') . '?item_id=' . 1 . '" value="购买号码" />'
                .'</div>'
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

    // 购买彩票
    public function lotteryBuy()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'var_data' => ['required'],
            ], [
                'var_data.required' => 'var_data不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $lottery = DB::query()
                ->select([
                    '*'
                ])
                ->from('lottery AS l')
                ->where('number', '=', '')
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get()
                ->first()
            ;

            if (!$lottery) {
                throw new InvalidArgumentException('今天还没有开售', 400);
            }

            $userLottery = DB::query()
                ->select([
                    'ul.*'
                ])
                ->from('user_lottery AS ul')
                ->where('ul.user_role_id', '=', $user_role_id)
                ->where('ul.stage', '=', $lottery->stage)
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($userLottery) {
                throw new InvalidArgumentException('您今天已经购买过了，购买号码：' . $userLottery->number, 400);
            }

            $userRole = UserRole::getUserRole();

            if ($userRole->coin < 1000) {
                throw new InvalidArgumentException('您金币不足1000', 400);
            }

            DB::beginTransaction();

            DB::table('user_lottery')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'stage'        => $lottery->stage,
                    'number'       => $query['var_data'],
                ])
            ;

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . 1000),
                ])
            ;

            DB::table('sys_coin')
                ->where('id', '=', 1)
                ->update([
                    'lottery_coin' => DB::raw('`lottery_coin` + ' . 1000),
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '购买成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
