<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use App\Models\Enemy;

class UserRole
{
    public static function attackToEnemy($user_Role, $enemy)
    {
        $level_difference = $enemy->level - $user_Role->level;
        if ($level_difference > 0 && is_success($level_difference)) {
            return $enemy->name . '闪避了';
        }

        $user_hurt = $user_Role->attack - $enemy->defense;
        if ($user_hurt <= 0) {
            return '无法破防';
        }

        $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($user_Role->crit)) {
            $user_hurt += $hurt_wave;
        }else {
            $user_hurt -= $hurt_wave;
        }

        $enemy->hp -= $user_hurt;
        if ($enemy->hp <= 0) {
            return $enemy->name . '被您击败了';
        }

        $res = Enemy::attackToUserRole($user_Role, $enemy);

        return $enemy->name . '-' . $user_hurt . '血量：' . $enemy->hp . '<br>' . $res;
    }
}
