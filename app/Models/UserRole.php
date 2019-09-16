<?php

namespace App\Models;

use App\Events\UserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class UserRole
{
    public static function attackToEnemy($user_Role, $enemy)
    {
        $level_difference = $enemy->level - $user_Role->level;

        if ($level_difference > 0 && is_success($level_difference)) {
            return $enemy->name . '闪避了';
        }

        if ($user_Role->attack - $enemy->defense) {

            return '无法破防';
        }

        return 0;
    }
}
