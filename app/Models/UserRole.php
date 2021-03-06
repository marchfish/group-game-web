<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use App\Models\Enemy;
use App\Models\Item;
use App\Models\UserKnapsack;
use phpDocumentor\Reflection\Types\Null_;

class UserRole
{
    public static function getUserRole()
    {
        $user_role_id = Session::get('user.account.user_role_id');

        $row = DB::query()
            ->select([
                'ur.*',
                DB::raw('IFNULL(`m`.`name`, "") AS `map_name`'),
                DB::raw('IFNULL(`sf`.`name`, "") AS `fame_name`'),
            ])
            ->from('user_role AS ur')
            ->leftJoin('map AS m', function ($join) {
                $join
                    ->on('m.id', '=', 'ur.map_id')
                ;
            })
            ->leftJoin('sys_fame AS sf', function ($join) {
                $join
                    ->on('sf.id', '=', 'ur.fame_id')
                ;
            })
            ->where('ur.id', '=', $user_role_id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row;
    }

    public static function getUserRoleByUid($uid)
    {
        $user_role = DB::query()
            ->select([
                '*',
            ])
            ->from('user_role AS ur')
            ->where('ur.id', '=', $uid)
            ->limit(1)
            ->get()
            ->first()
        ;
        return $user_role;
    }

    public static function attackToEnemy($user_Role, $enemy, $user_pets = null)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        $res = '';

        $fight = DB::query()
            ->select([
                '*',
            ])
            ->from('fight AS f')
            ->where('user_role_id', '=', $user_role_id)
            ->where('enemy_id', '=', $enemy->id)
            ->where('enemy_hp', '>', 0)
            ->limit(1)
            ->get()
            ->first()
        ;

        $is_restrict = false;
        $restrict_num = 0;

        if ($fight)
        {
            $enemy->hp = $fight->enemy_hp;
            $enemy->mp = $fight->enemy_mp;
        }

        if ($fight && $fight->content != '') {
            $fight_contents = json_decode($fight->content);

            foreach ($fight_contents as $fight_content) {
                if (isset($fight_content->skill_type) && $fight_content->skill_type == 'restrict-attack') {
                    $is_restrict = true;
                    $restrict_num = $fight_content->num;
                }
            }
        }

        // 宠物信息
        $pets_res = '';

        if ($is_restrict) {
            $restrict_num -= 1;
            foreach ($fight_contents as $k => $v) {
                if (isset($v->skill_type) && $v->skill_type == 'restrict-attack') {
                    if ($restrict_num <= 0) {
                        unset($fight_contents[$k]);
                        $res .= '[' . $user_Role->name . '] 禁止攻击已解除！<br>';
                    }else {
                        $v->num = $restrict_num;
                        $res .= '[' . $user_Role->name . '] 被禁止攻击，' . $restrict_num . '回合后解除！<br>';
                    }
                }
            }
            DB::table('fight')
                ->where('user_role_id', '=', $user_Role->id)
                ->where('enemy_id', '=', $enemy->id)
                ->update([
                    'content' => json_encode($fight_contents),
                ])
            ;
        }else {
            $level_difference = $enemy->level - $user_Role->level;

            $user_hurt = $user_Role->attack - $enemy->defense;

            if ($level_difference > 0 && is_success($level_difference)) {
                $res .= $enemy->name . '闪避了<br>';
            }elseif ($user_hurt <= 0) {
                $res .= '您无法破防<br>';
            }else {

                $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

                $rand_num = mt_rand(0, 100);

                if ($rand_num >= 50 || is_success($user_Role->crit)) {
                    $user_hurt += $hurt_wave;
                } else {
                    $user_hurt -= $hurt_wave;
                }

                $enemy->hp -= $user_hurt;

                // 判断是否有宠物
                if ($user_pets) {
                    $pets_hurt_wave = mt_rand(0, (int)round($user_pets->attack * 0.5));

                    if (is_success(50)) {
                        $user_pets->attack += $pets_hurt_wave;
                    } else {
                        $user_pets->attack -= $pets_hurt_wave;
                        if ($user_pets->attack <= 0) {
                            $user_pets->attack = 1;
                        }
                    }

                    $enemy->hp -= $user_pets->attack;

                    $pets_res = ' ' . $user_pets->name . '攻击-' . $user_pets->attack;
                }

                // 存入数据
                DB::table('fight')->updateOrInsert([
                    'user_role_id' => $user_role_id,
                ], [
                    'enemy_id' => $enemy->id,
                    'enemy_hp' => $enemy->hp < 0 ? 0 : $enemy->hp,
                    'enemy_mp' => $enemy->mp,
                ]);

                if ($enemy->hp <= 0) {
                    $res .= $enemy->name . '被您击败了<br>';

                    self::setExpAndCoin($enemy);

                    $res .= '获得经验=' . $enemy->exp . '<br>';
                    $res .= '获得金币=' . $enemy->coin . '<br>';

                    $certain_items = json_decode($enemy->certain_items);
                    $items = json_decode($enemy->items);

                    if (is_success($enemy->probability)) {
                        $count = mt_rand(0, count($items) - 1);
                        $win_item[0] = $items[$count];
                        if (is_array($certain_items)) {
                            $certain_items = array_merge($certain_items, $win_item);
                        } else {
                            $certain_items = $win_item;
                        }
                        $result = [];
                        foreach ($certain_items as $val) {
                            $key = $val->id;
                            if (!isset($result[$key])) {
                                $result[$key] = $val;
                            } else {
                                $result[$key]->num += $val->num;
                            }
                        }

                        $certain_items = $result;
                    }

                    if (is_array($certain_items)) {
                        $res .= '获得物品:' . UserKnapsack::addItems($certain_items) . '<br>';
                    }

                    $is_up = self::is_upgrade();

                    if ($is_up != 0) {
                        $res .= '恭喜您！等级提升至：' . $is_up . '<br>';
                    }

                    if ($enemy->move_map_id != 0) {
                        DB::table('user_role')
                            ->where('id', '=', $user_role_id)
                            ->update([
                                'map_id' => $enemy->move_map_id,
                            ]);

                        $res .= '您已被传送出该位置<br>';
                    }

                    // 怪物刷新时间
                    Enemy::refresh($enemy);

                    return $res;
                }

                $res = '[' . $user_Role->name . ']' . '攻击' . $enemy->name . '-' . $user_hurt . $pets_res . ' 怪物血量：' . $enemy->hp . '<br>' . $res;
            }

        }

        if ($enemy->skill_probability != 0 && is_success($enemy->skill_probability)) {
            $res .= Enemy::skillToUserRole($user_Role, $enemy);
        }else {
            $res .= Enemy::attackToUserRole($user_Role, $enemy);
        }

        return $res;
    }

    public static function setExpAndCoin($row, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'exp' => DB::raw('`exp` + ' . $row->exp),
                'coin' => DB::raw('`coin` + ' . $row->coin),
            ])
        ;
    }

    public static function is_upgrade(int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        $row = DB::query()
            ->select([
                'ur.*',
            ])
            ->from('user_role AS ur')
            ->where('id', '=', $user_role_id)
            ->limit(1)
            ->get()
            ->first()
        ;

        $role_level = $row->level;

        DB::beginTransaction();

        do {
            $role_level += 1;

            $row1 = DB::query()
                ->select([
                    'sl.*',
                ])
                ->from('sys_level AS sl')
                ->where('level', '=', $role_level)
                ->limit(1)
                ->get()
                ->first()
            ;

            if(!$row1) {
               break ;
            };

            if ($row->exp >= $row1->exp) {
                DB::table('user_role')
                    ->where('id', '=', $user_role_id)
                    ->update([
                        'max_hp' => DB::raw('`max_hp` + ' . $row1->max_hp),
                        'max_mp' => DB::raw('`max_mp` + ' . $row1->max_mp),
                        'attack' => DB::raw('`attack` + ' . $row1->attack),
                        'magic' => DB::raw('`magic` + ' . $row1->magic),
                        'crit' => DB::raw('`crit` + ' . $row1->crit),
                        'dodge' => DB::raw('`dodge` + ' . $row1->dodge),
                        'defense' => DB::raw('`defense` + ' . $row1->defense),
                        'level' => $row1->level,
                        'fame_id' => $row1->fame_id,
                    ])
                ;

                $row->new_level = $row1->level;


            }

        } while ($row->exp > $row1->exp);

        DB::commit();

        if (isset($row->new_level) && $row->new_level > $row->level) {
            return $row->new_level;
        }else {
            return 0;
        }

    }

    public static function getUserDate()
    {
        $user_role_id = Session::get('user.account.user_role_id');

        $user_date =  DB::query()
            ->select([
                'ud.*',
            ])
            ->from('user_date AS ud')
            ->where('ud.user_role_id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        if (!$user_date) {
            DB::table('user_date')->insert([
                'user_role_id' => $user_role_id
            ]);

            return null;
        }

        return $user_date;
    }

    // QQ
    public static function getUserRoleToQQ(int $user_role_id)
    {
        $row = DB::query()
            ->select([
                'ur.*',
                DB::raw('IFNULL(`m`.`name`, "") AS `map_name`'),
                DB::raw('IFNULL(`sf`.`name`, "") AS `fame_name`'),
            ])
            ->from('user_role AS ur')
            ->leftJoin('map AS m', function ($join) {
                $join
                    ->on('m.id', '=', 'ur.map_id')
                ;
            })
            ->leftJoin('sys_fame AS sf', function ($join) {
                $join
                    ->on('sf.id', '=', 'ur.fame_id')
                ;
            })
            ->where('ur.id', '=', $user_role_id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row;
    }

    public static function getUserDateToQQ(int $user_role_id)
    {
        $user_date =  DB::query()
            ->select([
                'ud.*',
            ])
            ->from('user_date AS ud')
            ->where('ud.user_role_id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        if (!$user_date) {
            DB::table('user_date')->insert([
                'user_role_id' => $user_role_id
            ]);

            return null;
        }

        return $user_date;
    }

    public static function isUpgradeToQQ(int $user_role_id)
    {
        $row = DB::query()
            ->select([
                'ur.*',
            ])
            ->from('user_role AS ur')
            ->where('id', '=', $user_role_id)
            ->limit(1)
            ->get()
            ->first()
        ;

        $role_level = $row->level;

        DB::beginTransaction();

        do {
            $role_level += 1;

            $row1 = DB::query()
                ->select([
                    'sl.*',
                ])
                ->from('sys_level AS sl')
                ->where('level', '=', $role_level)
                ->limit(1)
                ->get()
                ->first()
            ;

            if(!$row1) {
                break ;
            };

            if ($row->exp >= $row1->exp) {
                DB::table('user_role')
                    ->where('id', '=', $user_role_id)
                    ->update([
                        'max_hp' => DB::raw('`max_hp` + ' . $row1->max_hp),
                        'max_mp' => DB::raw('`max_mp` + ' . $row1->max_mp),
                        'attack' => DB::raw('`attack` + ' . $row1->attack),
                        'magic' => DB::raw('`magic` + ' . $row1->magic),
                        'crit' => DB::raw('`crit` + ' . $row1->crit),
                        'dodge' => DB::raw('`dodge` + ' . $row1->dodge),
                        'defense' => DB::raw('`defense` + ' . $row1->defense),
                        'level' => $row1->level,
                        'fame_id' => $row1->fame_id,
                    ])
                ;

                $row->new_level = $row1->level;
            }

        } while ($row->exp > $row1->exp);

        DB::commit();

        if (isset($row->new_level) && $row->new_level > $row->level) {
            return $row->new_level;
        }else {
            return 0;
        }

    }

    public static function attackToEnemyToQQ($user_Role, $enemy, $user_pets = null)
    {
        $user_role_id = $user_Role->id;

        $res = '';

        $fight = DB::query()
            ->select([
                '*',
            ])
            ->from('fight AS f')
            ->where('user_role_id', '=', $user_role_id)
            ->where('enemy_id', '=', $enemy->id)
            ->where('enemy_hp', '>', 0)
            ->limit(1)
            ->get()
            ->first()
        ;

        $is_restrict = false;
        $restrict_num = 0;

        if ($fight)
        {
            $enemy->hp = $fight->enemy_hp;
            $enemy->mp = $fight->enemy_mp;
        }

        if ($fight && $fight->content != '') {
            $fight_contents = json_decode($fight->content);

            foreach ($fight_contents as $fight_content) {
                if (isset($fight_content->skill_type) && $fight_content->skill_type == 'restrict-attack') {
                    $is_restrict = true;
                    $restrict_num = $fight_content->num;
                }
            }
        }

        // 宠物信息
        $pets_res = '';

        if ($is_restrict) {
            $restrict_num -= 1;
            foreach ($fight_contents as $k => $v) {
                if (isset($v->skill_type) && $v->skill_type == 'restrict-attack') {
                    if ($restrict_num <= 0) {
                        unset($fight_contents[$k]);
                        $res .= '[' . $user_Role->name . '] 禁止攻击已解除！\r\n';
                    }else {
                        $v->num = $restrict_num;
                        $res .= '[' . $user_Role->name . '] 被禁止攻击，' . $restrict_num . '回合后解除！\r\n';
                    }
                }
            }
            DB::table('fight')
                ->where('user_role_id', '=', $user_Role->id)
                ->where('enemy_id', '=', $enemy->id)
                ->update([
                    'content' => json_encode($fight_contents),
                ])
            ;
        }else {
            $level_difference = $enemy->level - $user_Role->level;

            $user_hurt = $user_Role->attack - $enemy->defense;

            if ($level_difference > 0 && is_success($level_difference)) {
                $res .= $enemy->name . '闪避了\r\n';
            } elseif ($user_hurt <= 0) {
                $res .= '您无法破防\r\n';
            } else {
                $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

                $rand_num = mt_rand(0, 100);

                if ($rand_num >= 50 || is_success($user_Role->crit)) {
                    $user_hurt += $hurt_wave;
                } else {
                    $user_hurt -= $hurt_wave;
                }

                $enemy->hp -= $user_hurt;

                // 判断是否有宠物
                if ($user_pets) {
                    $pets_hurt_wave = mt_rand(0, (int)round($user_pets->attack * 0.5));

                    if (is_success(50)) {
                        $user_pets->attack += $pets_hurt_wave;
                    } else {
                        $user_pets->attack -= $pets_hurt_wave;
                        if ($user_pets->attack <= 0) {
                            $user_pets->attack = 1;
                        }
                    }

                    $enemy->hp -= $user_pets->attack;

                    $pets_res = ' ' . $user_pets->name . '攻击-' . $user_pets->attack;
                }

                // 存入数据
                DB::table('fight')->updateOrInsert([
                    'user_role_id' => $user_role_id,
                ], [
                    'enemy_id' => $enemy->id,
                    'enemy_hp' => $enemy->hp < 0 ? 0 : $enemy->hp,
                    'enemy_mp' => $enemy->mp,
                ]);

                if ($enemy->hp <= 0) {
                    $res .= $enemy->name . '被您击败了\r\n';

                    self::setExpAndCoinToQQ($enemy, $user_role_id);

                    $res .= '获得经验=' . $enemy->exp . '\r\n';
                    $res .= '获得金币=' . $enemy->coin . '\r\n';

                    $certain_items = json_decode($enemy->certain_items);
                    $items = json_decode($enemy->items);

                    if (is_success($enemy->probability)) {
                        $count = mt_rand(0, count($items) - 1);
                        $win_item[0] = $items[$count];
                        if (is_array($certain_items)) {
                            $certain_items = array_merge($certain_items, $win_item);
                        } else {
                            $certain_items = $win_item;
                        }
                        $result = [];
                        foreach ($certain_items as $val) {
                            $key = $val->id;
                            if (!isset($result[$key])) {
                                $result[$key] = $val;
                            } else {
                                $result[$key]->num += $val->num;
                            }
                        }

                        $certain_items = $result;
                    }

                    if (is_array($certain_items)) {
                        $res .= '获得物品:' . UserKnapsack::addItems($certain_items, $user_role_id) . '\r\n';
                    }

                    $is_up = self::isUpgradeToQQ($user_role_id);

                    if ($is_up != 0) {
                        $res .= '恭喜您！等级提升至：' . $is_up . '\r\n';
                    }

                    if ($enemy->move_map_id != 0) {
                        DB::table('user_role')
                            ->where('id', '=', $user_role_id)
                            ->update([
                                'map_id' => $enemy->move_map_id,
                            ]);

                        $res .= '您已被传送出该位置';
                    }

                    // 怪物刷新时间
                    Enemy::refresh($enemy);

                    return $res;
                }

                $res = '[' . $user_Role->name . ']' . '攻击' . $enemy->name . '-' . $user_hurt . $pets_res . ' 怪物血量：' . $enemy->hp . '\r\n' . $res;
            }
        }

        if ($enemy->skill_probability != 0 && is_success($enemy->skill_probability)) {
            $res .= Enemy::skillToUserRole($user_Role, $enemy, $user_role_id);
        }else {
            $res .= Enemy::attackToUserRoleToQQ($user_Role, $enemy);
        }

        return $res;
    }

    public static function setExpAndCoinToQQ($row, int $user_role_id)
    {
        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'exp' => DB::raw('`exp` + ' . $row->exp),
                'coin' => DB::raw('`coin` + ' . $row->coin),
            ])
        ;
    }
}
