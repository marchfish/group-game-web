<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class Pets
{
    // 判断是否升级
    public static function is_upgrade($user_pets_id, int $user_role_id = 0)
    {
        $line = '\r\n';

        if ($user_role_id == 0) {
            $line = '<br>';
            $user_role_id = Session::get('user.account.user_role_id');
        }

        $userPets = DB::query()
            ->select([
                'up.*',
            ])
            ->from('user_pets AS up')
            ->where('up.id', '=', $user_pets_id)
            ->where('up.user_role_id', '=', $user_role_id)
            ->limit(1)
            ->get()
            ->first()
        ;

        if ($userPets->level >= $userPets->max_level) {
            return $line . '您的宠物已达到最高等级限制！';
        }

        $pets_level = $userPets->level;

        DB::beginTransaction();

        do {
            $pets_level += 1;

            $sysPets = DB::query()
                ->select([
                    'sp.*',
                ])
                ->from('sys_pets AS sp')
                ->where('sp.level', '=', $pets_level)
                ->limit(1)
                ->get()
                ->first()
            ;

            if(!$sysPets) {
                break ;
            }

            if ($sysPets->level > $userPets->max_level) {
                break ;
            }

            if ($userPets->exp >= $sysPets->exp) {
                DB::table('user_pets')
                    ->where('id', '=', $user_pets_id)
                    ->update([
                        'attack' => DB::raw('`attack` + ' . $sysPets->attack),
                        'level' => $sysPets->level
                    ])
                ;

                $userPets->new_level = $sysPets->level;
            }

        } while ($userPets->exp > $sysPets->exp);

        DB::commit();

        if (isset($userPets->new_level) && $userPets->new_level > $userPets->level) {
            return $line . $userPets->name . ' 等级提升为：' . $userPets->new_level;
        }else {
            return '';
        }
    }
}
