<?php

namespace App\Http\Controllers\Api;

use App\Models\Map;
use App\Models\UserRole;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GameController extends Controller
{
    // 获取位置信息
    public function location()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'm.name AS name',
                    'm.description AS description',
                    DB::raw('IFNULL(`n`.`name`, "") AS `npc_name`'),
                    DB::raw('IFNULL(`e`.`name`, "") AS `enemy_name`'),
                    DB::raw('IFNULL(`e`.`img`, "") AS `enemy_img`'),
                    DB::raw('IFNULL(`e`.`level`, 0) AS `enemy_level`'),
                    DB::raw('IFNULL(`e`.`type`, 0) AS `enemy_type`'),
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

            $res = Map::getLocationToQQ($row);

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

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

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
                    DB::raw('IFNULL(`e`.`type`, 0) AS `enemy_type`'),
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

            $res = Map::getLocationToQQ($row1);

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
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $user_date = UserRole::getUserDateToQQ($user_role_id);

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
                    ])
                ;
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

            // 设置攻击
            $user_Role->attack = $user_Role->attack > $user_Role->magic ? $user_Role->attack : $user_Role->magic;

            // 获取宠物信息
            $user_pets = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pets AS up')
                ->where('up.user_role_id', '=', $user_role_id)
                ->where('up.is_fight', '=', 1)
                ->get()
                ->first()
            ;

            $res =  UserRole::attackToEnemyToQQ($user_Role, $enemy, $user_pets);

            if ($user_date){
                if ($user_date->attack_at) {
                    $now_at = strtotime('now');
                    $attack_at = strtotime($user_date->attack_at);

                    if($now_at - $attack_at < 20) {
                        $res = '';
                        $user_vip = DB::query()
                            ->select([
                                'uv.*',
                            ])
                            ->from('user_vip AS uv')
                            ->where('uv.user_role_id', '=', $user_role_id)
                            ->get()
                            ->first()
                        ;

                        if (!$user_vip || time() > strtotime($user_vip->expired_at)) {
                            $res = '';
                        }else {
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
                                    $res = '您的血瓶没有了!';
                                }else {
                                    $item = json_decode($row->content)[0];
                                    $item->id = $row->id;

                                    Item::useDrugToQQ($item, $user_role_id, 1);
                                }
                            }
                        };
                    };
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
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

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
            $query = Request::all();

            $validator = Validator::make($query, [
                'action' => ['required'],
            ], [
                'action.required' => 'action不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $res = '';

            switch ($query['action']) {
                case 2:

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

                    $res .= '攻击排行前10';

                    foreach ($rows1 as $row1) {
                        $res .=  '\r\n' . $row1->name . ' ：' . $row1->attack;
                    };

                    break;
                case 3:
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

                    $res .= '防御排行前10';

                    foreach ($rows2 as $row2) {
                        $res .= '\r\n' . $row2->name . ' ：' . $row2->defense;
                    };

                    break;
                case 4:
                    $rows3 = DB::query()
                        ->select([
                            'ur.*'
                        ])
                        ->from('user_role AS ur')
                        ->where('id', '<>', 1)
                        ->limit(10)
                        ->orderBy('magic', 'desc')
                        ->get()
                    ;

                    $res .= '魔力排行前10';

                    foreach ($rows3 as $row3) {
                        $res .= '\r\n' . $row3->name . ' ：' . $row3->magic;
                    };

                    break;
                default:

                    $res = '等级排行前10';

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
                        $res .= '\r\n' . $row->name . ' ：' . $row->level;
                    };

                    break;
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
                $res .= '上次中奖：[' . $row->user_name . '] 获得' . $row->coin . '金币--('. $row->number .')\r\n';
            }else {
                $res .= '上次中奖：无人中奖\r\n';
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
                $res .= '上期开奖号码：'. $last->number .'\r\n';
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

            $res .= '第' . $row1->stage . '期\r\n';

            $row2 = DB::query()
                ->select([
                    '*'
                ])
                ->from('sys_coin AS sc')
                ->limit(1)
                ->get()
                ->first()
            ;

            $res .= '奖池：' . $row2->lottery_coin . '\r\n';
            $res .= '注：每人每天仅限购买一次，花费：1000/次\r\n';
            $res .= '输入：购买号码 三位数字';

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
                'number' => ['required'],
            ], [
                'number.required' => 'number不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

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

            if ($user_role->coin < 1000) {
                throw new InvalidArgumentException('您金币不足1000', 400);
            }

            DB::beginTransaction();

            DB::table('user_lottery')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'stage'        => $lottery->stage,
                    'number'       => $query['number'],
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

    // 开奖历史
    public function lotteryHistory()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $rows = DB::query()
                ->select([
                    'l.*',
                    DB::raw('IFNULL(`ur`.`name`, "无人中奖") AS `user_role_name`'),
                ])
                ->from('lottery AS l')
                ->leftJoin('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'l.user_role_id')
                    ;
                })
                ->where('l.number', '<>', '')
                ->orderBy('l.created_at', 'desc')
                ->paginate($query['size'])
            ;

            $res = '[开奖历史] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .='第(' . $row->stage . ')期 号码：' . $row->number . '---' . $row->user_role_name . '\r\n';
            }

            $res .= '翻页：开奖历史 页数';

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

    // 改名
    public function rename()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'nickname' => ['required'],
            ], [
                'nickname.required' => '请输入正确的昵称',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            if (mb_strlen($query['nickname'],'utf8') > 10) {
                throw new InvalidArgumentException('游戏名字不能大于10个字', 400);
            }

            // 查看是否有改名卡
            $row = DB::query()
                ->select([
                    'uk.*',
                ])
                ->from('user_knapsack AS uk')
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('uk.item_id', '=', 147)
                ->where('uk.item_num', '>=', 1)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('改名失败，您没有改名卡！', 400);
            }

            $row1 = DB::query()
                ->select(['id'])
                ->from('user_role')
                ->where('name', '=', $query['nickname'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row1) {
                throw new InvalidArgumentException('该昵称已存在', 400);
            }

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'name' => $query['nickname'],
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', 147)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . 1),
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '恭喜您成功改名为：[' . $query['nickname'] . ']',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

}
