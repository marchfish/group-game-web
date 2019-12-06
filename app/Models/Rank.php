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

class Rank
{
    public static function pk($user_Role1, $user_Role2)
    {
        $pk_bool = true;
        $user_Role1->hp = $user_Role1->max_hp;
        $user_Role2->hp = $user_Role2->max_hp;

        while ($pk_bool) {
            // 1
            $user_Role1_hurt = $user_Role1->attack - $user_Role2->defense;

            if (is_success($user_Role2->dodge)) {

            }elseif ($user_Role1_hurt <= 0) {
                return false;
            }else {
                $user_Role1_hurt_wave = mt_rand(0, round($user_Role1_hurt * 0.5));

                $rand_num1 = mt_rand(0, 100);

                if ($rand_num1 >= 50 || is_success($user_Role1->crit)) {
                    $user_Role1_hurt += $user_Role1_hurt_wave;
                } else {
                    $user_Role1_hurt -= $user_Role1_hurt_wave;
                }

                $user_Role2->hp -= $user_Role1_hurt;
            }

            // 2
            $user_Role2_hurt = $user_Role2->attack - $user_Role1->defense;
            if (is_success($user_Role1->dodge)) {

            }elseif ($user_Role2_hurt <= 0) {

            }else {
                $user_Role2_hurt_wave = mt_rand(0, round($user_Role2_hurt * 0.5));

                $rand_num2 = mt_rand(0, 100);

                if ($rand_num2 >= 50 || is_success($user_Role2->crit)) {
                    $user_Role2_hurt += $user_Role2_hurt_wave;
                } else {
                    $user_Role2_hurt -= $user_Role2_hurt_wave;
                }

                $user_Role1->hp -= $user_Role2_hurt;
            }

            if ($user_Role1->hp <= 0 || $user_Role2->hp <= 0) {
                if ($user_Role1->hp >= $user_Role2->hp) {

                    DB::beginTransaction();

                    DB::table('rank')
                        ->where('num', '=', $user_Role2->num)
                        ->update([
                            'user_role_id' => $user_Role1->id,
                        ])
                    ;

                    if ($user_Role1->num != 0) {

                        DB::table('rank')
                            ->where('num', '=', $user_Role1->num)
                            ->update([
                                'user_role_id' => $user_Role2->id,
                            ])
                        ;

                    }

                    DB::commit();

                    return true;
                }else {
                    return false;
                }
            }
        }

        return false;
    }

    public static function start()
    {
        $ranks = DB::query()
            ->select([
                'r.*',
            ])
            ->from('rank AS r')
            ->get()
        ;

        DB::beginTransaction();

        foreach ($ranks as $rank)
        {
            if ($rank->user_role_id != 0) {
                $reward = json_decode($rank->reward);
                UserKnapsack::addItems($reward, $rank->user_role_id);

                DB::table('user_role')
                    ->where('id', '=', $rank->user_role_id)
                    ->update([
                        'coin' => DB::raw('`coin` + ' . $rank->coin),
                    ])
                ;
            }
        }

        DB::table('rank')
            ->update([
                'user_role_id' => 0,
            ])
        ;

        DB::table('sys_date')
            ->where('id', '=', 1)
            ->update([
                'rank_expired_at' => date('Y-m-d H:i:s', strtotime('+1 month')),
            ])
        ;

        DB::commit();
    }
}
