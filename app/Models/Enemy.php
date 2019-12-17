<?php

namespace App\Models;

use App\Events\UserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;

class Enemy
{
    public static function attackToUserRole($user_Role, $enemy)
    {
        $user_role_id = Session::get('user.account.user_role_id');

        if (is_success($user_Role->dodge)) {
            return $user_Role->name . '闪避了';
        }

        $enemy_hurt = $enemy->attack - $user_Role->defense;
        if ($enemy_hurt <= 0) {
            return '怪物无法破防';
        }

        $hurt_wave = mt_rand(0, round($enemy_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($enemy->crit)) {
            $enemy_hurt += $hurt_wave;
        }else {
            $enemy_hurt -= $hurt_wave;
        }

        $user_Role->hp -= $enemy_hurt;

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'hp' => $user_Role->hp < 0 ? 0 : $user_Role->hp,
            ])
        ;

        if ($user_Role->hp <= 0) {
            DB::table('fight')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'enemy_hp' => 0,
                ])
            ;

            return '您被击败了，请复活后继续...';
        }

        return $user_Role->name . '-' . $enemy_hurt . ' 血量：' . $user_Role->hp;
    }

    public static function attackToUserRoleToQQ($user_Role, $enemy)
    {
        $user_role_id = $user_Role->id;

        if (is_success($user_Role->dodge)) {
            return $user_Role->name . '闪避了';
        }

        $enemy_hurt = $enemy->attack - $user_Role->defense;
        if ($enemy_hurt <= 0) {
            return '怪物无法破防';
        }

        $hurt_wave = mt_rand(0, round($enemy_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($enemy->crit)) {
            $enemy_hurt += $hurt_wave;
        }else {
            $enemy_hurt -= $hurt_wave;
        }

        $user_Role->hp -= $enemy_hurt;

        DB::table('user_role')
            ->where('id', '=', $user_role_id)
            ->update([
                'hp' => $user_Role->hp < 0 ? 0 : $user_Role->hp,
            ])
        ;

        if ($user_Role->hp <= 0) {
            DB::table('fight')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'enemy_hp' => 0,
                ])
            ;

            return '您被击败了，请复活后继续...';
        }

        return $user_Role->name . '-' . $enemy_hurt . ' 血量：' . $user_Role->hp;
    }

    public static function skillToUserRole($user_role, $enemy, int $user_role_id = 0)
    {
        $line = '<br>';

        if ($user_role_id != 0) {
            $line = '\r\n';
        }

        $res = '怪物使用技能失败!';

        if ($enemy->skill != '') {
            $enemy_skill = json_decode($enemy->skill);

            $rand_num = mt_rand(0, count($enemy_skill)-1);

            if (isset($enemy_skill[$rand_num])) {

                $enemySkill = DB::query()
                    ->select([
                        's.*',
                    ])
                    ->from('skill AS s')
                    ->where('s.id', '=', $enemy_skill[$rand_num]->skill_id)
                    ->get()
                    ->first()
                ;

                if ($enemySkill) {
                    $skill_content = json_decode($enemySkill->content)[0];

                    $fight = DB::query()
                        ->select([
                            '*',
                        ])
                        ->from('fight AS f')
                        ->where('user_role_id', '=', $user_role->id)
                        ->where('enemy_id', '=', $enemy->id)
                        ->where('enemy_hp', '>', 0)
                        ->limit(1)
                        ->get()
                        ->first()
                    ;

                   if($fight && $enemySkill->type == 'recovery-hp') {
                       $skill_hp = (int)round($enemy->max_hp * ($skill_content->max_hp / 100));

                       $hp = $skill_hp + $fight->enemy_hp;

                       $enemy_hp = $hp > $enemy->max_hp ? $enemy->max_hp : $hp;

                       DB::table('fight')
                           ->where('user_role_id', '=', $user_role->id)
                           ->where('enemy_id', '=', $enemy->id)
                           ->update([
                               'enemy_hp' => $enemy_hp,
                           ])
                       ;

                       $res = '[' . $enemy->name . '] 使用：' . $enemySkill->name . '，生命 +' . $skill_hp . $line . '怪物当前血量：' . $enemy_hp . $line;
                   }

                   if ($enemySkill->type == 'restrict-attack') {
                       $data = [
                            [
                                'skill_type' => $enemySkill->type,
                                'num'        => $skill_content->num
                            ]
                       ];

                       DB::table('fight')
                           ->where('user_role_id', '=', $user_role->id)
                           ->where('enemy_id', '=', $enemy->id)
                           ->update([
                               'content' => json_encode($data),
                           ])
                       ;

                       $res = '[' . $enemy->name . '] 使用：' . $enemySkill->name . '，您被禁止攻击：' . $skill_content->num . '回合' . $line ;
                   }

                   if ($enemySkill->type == 'attack') {
                       $res = self::attacSkillToUserRole($user_role, $enemy, $enemySkill, $skill_content);
                   }
                }
            }
        }

        return $res;
    }

    public static function refresh($enemy)
    {
        if ($enemy->enemy_refresh_minute && $enemy->enemy_refresh_minute != '' && $enemy->enemy_refresh_minute != 0) {
            $refresh_at = date('Y-m-d H:i:s', strtotime('+' . $enemy->enemy_refresh_minute . ' minute'));

            DB::table('enemy_date')
                ->where('enemy_id', '=', $enemy->id)
                ->update([
                    'refresh_at' => $refresh_at,
                ])
            ;
        }
    }

    public static function attacSkillToUserRole($user_role, $enemy, $enemySkill, $skill_content)
    {
        $enemy_skill = 0;

        if (isset($skill_content->attack)) {
            $enemy_skill += (int)round($enemy->attack * ($skill_content->attack / 100));
        };

        if (isset($skill_content->magic)) {
            $enemy_skill += (int)round($enemy->magic * ($skill_content->magic / 100));
        };

        $enemy_hurt = $enemy_skill - $user_role->defense;

        if ($enemy_hurt <= 0) {
            return '怪物无法破防';
        }

        $hurt_wave = mt_rand(0, round($enemy_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($enemy->crit)) {
            $enemy_hurt += $hurt_wave;
        }else {
            $enemy_hurt -= $hurt_wave;
        }

        $user_role->hp -= $enemy_hurt;

        DB::table('user_role')
            ->where('id', '=', $user_role->id)
            ->update([
                'hp' => $user_role->hp < 0 ? 0 : $user_role->hp,
            ])
        ;

        if ($user_role->hp <= 0) {
            DB::table('fight')
                ->where('user_role_id', '=', $user_role->id)
                ->update([
                    'enemy_hp' => 0,
                ])
            ;

            return '[' . $enemy->name . '] 使用：' . $enemySkill->name . '，' . '您被击败了，请复活后继续...';
        }

        return '[' . $enemy->name . '] 使用：' . $enemySkill->name . '，' . $user_role->name . '-' . $enemy_hurt . ' 血量：' . $user_role->hp;
    }
}
