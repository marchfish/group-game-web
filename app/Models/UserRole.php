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

    public static function attackToEnemy($user_Role, $enemy)
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

        if ($fight)
        {
            $now_at = strtotime('now');
            $fight_at = strtotime($fight->updated_at);

            if($now_at - $fight_at < 1) {
                return '';
            };

            $enemy->hp = $fight->enemy_hp;
            $enemy->mp = $fight->enemy_mp;
        }

        $level_difference = $enemy->level - $user_Role->level;
        if ($level_difference > 0 && is_success($level_difference)) {
            return $enemy->name . '闪避了';
        }

        $user_hurt = $user_Role->attack - $enemy->defense;
        if ($user_hurt <= 0) {
            return '您无法破防';
        }

        $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($user_Role->crit)) {
            $user_hurt += $hurt_wave;
        }else {
            $user_hurt -= $hurt_wave;
        }

        $enemy->hp -= $user_hurt;

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

            $res .= '获得经验=' .  $enemy->exp . '<br>';
            $res .= '获得金币=' .  $enemy->coin . '<br>';

            $certain_items = json_decode($enemy->certain_items);
            $items = json_decode($enemy->items);

            if (is_success($enemy->probability)) {
                $count = mt_rand(0, count($items) - 1);
                $win_item[0] = $items[$count];
                if (is_array($certain_items)) {
                    $certain_items = array_merge($certain_items, $win_item);
                }else {
                    $certain_items = $win_item;
                }
                $result = [];
                foreach($certain_items as $val){
                    $key = $val->id;
                    if(!isset($result[$key])){
                        $result[$key] = $val;
                    }else{
                        $result[$key]->num += $val->num;
                    }
                }

                $certain_items = $result;
            }

            $res .='获得物品:' . UserKnapsack::addItems($certain_items) . '<br>';

            $is_up = self::is_upgrade();

            if ($is_up != 0) {
                $res .='恭喜您！等级提升至：' . $is_up . '<br>';
            }

            if ($enemy->move_map_id != 0) {
                DB::table('user_role')
                    ->where('id', '=', $user_role_id)
                    ->update([
                        'map_id' => $enemy->move_map_id,
                    ])
                ;

                $res .='您已被传送出该位置<br>';
            }

            return $res;
        }

        $res .= Enemy::attackToUserRole($user_Role, $enemy);

        return $enemy->name . '-' . $user_hurt . '血量：' . $enemy->hp . '<br>' . $res;
    }

    public static function setExpAndCoin($row)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'exp' => DB::raw('`exp` + ' . $row->exp),
                'coin' => DB::raw('`coin` + ' . $row->coin),
            ])
        ;
    }

    public static function is_upgrade ()
    {
        $user_role_id = Session::get('user.account.user_role_id');

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

}
