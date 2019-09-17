<?php

namespace App\Models;

use App\Events\UserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class Enemy
{
    public static function attackToUserRole($user_Role, $enemy)
    {
        if (is_success($user_Role->dodge)) {
            return $user_Role->name . '闪避了';
        }

        $enemy_hurt = $enemy->attack - $user_Role->defense;
        if ($enemy_hurt <= 0) {
            return '无法破防';
        }

        $hurt_wave = mt_rand(0, round($enemy_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($enemy->crit)) {
            $enemy_hurt += $hurt_wave;
        }else {
            $enemy_hurt -= $hurt_wave;
        }

        $user_Role->hp -= $enemy_hurt;
        if ($user_Role->hp <= 0) {
            return '您被击败了，请复活后继续...';
        }

        return $user_Role->name . '-' . $enemy_hurt . '血量：' . $user_Role->hp;
    }
}
