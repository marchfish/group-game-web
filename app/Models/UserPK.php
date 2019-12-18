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

class UserPK
{
    public static function pk($userPK, $roles,int $key, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        $my = $userPK->a_user_role_id == $user_role_id ? 0 : 1;
        $enemy = $userPK->a_user_role_id == $user_role_id ? 1 : 0;

        $res = '';

        if ($key == 0) {
            $user_hurt = $roles[$my]->attack - $roles[$enemy]->defense;

            $user_hurt = self::damageCalculation($roles[$my], $roles[$enemy], $user_hurt);

            $res = $roles[$enemy]->name . ' -' . $user_hurt . ' 对方剩余血量：' . $roles[$enemy]->hp;

            if ($user_hurt == 0) {
                $res = '对方闪避了！';
            }

            if ($user_hurt == -1) {
                $res = '您无法破防！';
            }

        }else {
            $userSkill = DB::query()
                ->select([
                    's.*'
                ])
                ->from('skill AS s')
                ->join('user_skill AS us', function ($join) {
                    $join
                        ->on('us.skill_id', '=', 's.id')
                    ;
                })
                ->where('us.user_role_id', '=', $user_role_id)
                ->where('us.quick_key', '=', $key)
                ->get()
                ->first()
            ;

            if (!$userSkill) {
                throw new InvalidArgumentException('没有找到该技能！请先绑定快捷键后操作', 400);
            }

            $res = self::skill($roles[$my], $roles[$enemy], $userSkill, $user_role_id);
        }

        if ($roles[$enemy]->hp <= 0) {

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->delete()
            ;

            throw new InvalidArgumentException('对方被您击败了，恭喜 ['. $roles[$my]->name .'] 获得胜利！', 200);
        }

        DB::table('user_pk')
            ->where('id', '=', $userPK->id)
            ->update([
                'content' => json_encode($roles),
                'handle_user_role_id' => $roles[$enemy]->id,
                'wait_user_role_id'   => $roles[$my]->id,
                'expired_at'          => date('Y-m-d H:i:s', strtotime('+2 minutes'))
            ])
        ;

        $res .= '\r\n血量：' . $roles[$my]->hp . '\r\n蓝量：' . $roles[$my]->mp;
        return $res . '\r\n您回合结束，等待对方操作！';
    }

    public static function skill($roleA, $roleB, $userSkill, int $user_role_id = 0)
    {
        $res = '对不起，使用技能失败！';

        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        $skill_content = json_decode($userSkill->content)[0];

        if ($roleA->mp < $skill_content->use_mp) {
            throw new InvalidArgumentException('无法使用技能，您的蓝量不足' . $skill_content->use_mp, 400);
        }

        if (isset($roleA->skills)) {
            $roleA->skills = obj2arr($roleA->skills);
        }

        if (isset($skill_content->pk_cd) && isset($roleA->skills[$userSkill->id])) {
            $pk_cd = $roleA->num - $roleA->skills[$userSkill->id];

            if ($pk_cd < $skill_content->pk_cd) {
                throw new InvalidArgumentException('CD冷却中：' . ($skill_content->pk_cd - $pk_cd) . '回合', 400);
            }
        }

        $roleA->mp -= $skill_content->use_mp;

        $roleA->skills[$userSkill->id] = $roleA->num + 1;

        if ($userSkill->type == 'recovery-hp') {
            $skill_hp = (int)round($roleA->max_hp * ($skill_content->max_hp / 100));

            $hp = $skill_hp + $roleA->hp;

            $roleA->hp = $hp > $roleA->max_hp ? $roleA->max_hp : $hp;

            $res = '[' . $roleA->name . '] 使用：' . $userSkill->name . '，生命 +' . $skill_hp;

        }elseif ($userSkill->type == 'attack') {
            $user_skill = 0;

            if (isset($skill_content->attack)) {
                $user_skill += (int)round($roleA->attack * ($skill_content->attack / 100));
            };

            if (isset($skill_content->magic)) {
                $user_skill += (int)round($roleA->magic * ($skill_content->magic / 100));
            };

            $user_hurt = $user_skill - $roleB->defense;

            $user_hurt = self::damageCalculation($roleA, $roleB, $user_hurt);

            $res = '[' . $roleA->name . '] 使用：' . $userSkill->name . '，' . $roleB->name . '-' . $user_hurt . ' 对方剩余血量：' . $roleB->hp;

            if ($user_hurt == 0) {
                $res = '对方闪避了！';
            }

            if ($user_hurt == -1) {
                $res = '您无法破防！';
            }
        }

        return $res;
    }

    public static function damageCalculation($roleA, $roleB, int $user_hurt) {

        if (is_success($roleB->dodge)) {
            return 0;
        }

        if ($user_hurt <= 0) {
            return -1;
        }

        $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

        $rand_num = mt_rand(0, 100);

        if ($rand_num >= 50 || is_success($roleA->crit)) {
            $user_hurt += $hurt_wave;
        } else {
            $user_hurt -= $hurt_wave;
        }

        $roleB->hp -= $user_hurt;
        $roleA->num++;

        return $user_hurt;
    }
}
