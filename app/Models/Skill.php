<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class Skill
{
    // 使用学习
    public static function study($item, int $user_role_id = 0)
    {
        if ($user_role_id == 0) {
            $user_role_id = Session::get('user.account.user_role_id');
        }

        // 获取属性信息
        $userSkill = DB::query()
            ->select([
                DB::raw('COUNT(`us`.`id`) AS `count`'),
            ])
            ->from('user_skill AS us')
            ->where('us.user_role_id', '=', $user_role_id)
            ->get()
            ->first()
        ;

        if ($userSkill->count >= 8) {
            return '您已经学了8个技能！';
        }

        $skill = DB::query()
            ->select([
                's.*',
            ])
            ->from('skill AS s')
            ->where('s.id', '=', $item->content)
            ->get()
            ->first()
        ;

        if (!$skill) {
            return '该技能暂时无法学习！';
        }

        if ($skill->parent_skill_id == 0) {
            $userSkill1 = DB::query()
                ->select([
                    'us.id'
                ])
                ->from('user_skill AS us')
                ->where('us.user_role_id', '=', $user_role_id)
                ->where('us.skill_id', '=', $skill->id)
                ->get()
                ->first()
            ;

            if ($userSkill1) {
                return '您已经学习了该技能！';
            }

            DB::beginTransaction();

            DB::table('user_skill')->insert([
                'user_role_id' => $user_role_id,
                'skill_id'     => $skill->id,
            ]);

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $item->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . 1),
                ])
            ;

            DB::commit();
        }else {
            $userSkill1 = DB::query()
                ->select([
                    'us.id'
                ])
                ->from('user_skill AS us')
                ->where('us.user_role_id', '=', $user_role_id)
                ->where('us.skill_id', '=', $skill->parent_skill_id)
                ->get()
                ->first()
            ;

            if (!$userSkill1)
            {
                return '学习失败，原因：您已学习 或 您还未习得' . $skill->name . '第' . ($skill->level - 1) . '阶';
            }

            DB::beginTransaction();

            DB::table('user_skill')
                ->where('user_role_id', '=', $user_role_id)
                ->where('skill_id', '=', $skill->parent_skill_id)
                ->update([
                    'skill_id' => $skill->id,
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $item->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . 1),
                ])
            ;

            DB::commit();
        }

        return '成功学习：' . $item->name;
    }

    // 安全技
    public static function securitySkill($user_role, $skill, int $user_role_id = 0)
    {
        $line = '<br>';

        if ($user_role_id != 0) {
            $line = '\r\n';
        }

        $res = '';
        $skill_content = json_decode($skill->content)[0];

        if ($skill->type == 'recovery-hp') {

            if ($user_role->mp < $skill_content->use_mp) {
                throw new InvalidArgumentException('无法使用技能，您的蓝量不足' . $skill_content->use_mp, 400);
            }

            $skill_hp = (int)round($user_role->max_hp * ($skill_content->max_hp / 100));

            $hp = $skill_hp + $user_role->hp;

            $user_hp = $hp > $user_role->max_hp ? $user_role->max_hp : $hp;

            DB::table('user_role')
                ->where('id', '=', $user_role->id)
                ->update([
                    'hp' => $user_hp,
                    'mp' => DB::raw('`mp` - ' . $skill_content->use_mp),
                ])
            ;

            $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，生命 +' . $skill_hp . $line . '当前血量：' . $user_hp;
        }

        return $res;
    }

    // 普通技
    public static function skill($user_role, $skill, $enemy, int $user_role_id = 0)
    {
        $line = '<br>';

        if ($user_role_id != 0) {
            $line = '\r\n';
        }

        $res = '';
        $is_restrict = false;

        $skill_content = json_decode($skill->content)[0];

        if ($user_role->mp < $skill_content->use_mp) {
            throw new InvalidArgumentException('无法使用技能，您的蓝量不足' . $skill_content->use_mp, 400);
        }

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

        if ($fight->content != '') {
            $fight_contents = json_decode($fight->content);

            foreach ($fight_contents as $fight_content) {
                if (isset($fight_content->skill_type) && $fight_content->skill_type == 'restrict-attack') {
                    $is_restrict = true;
                }
            }
        }

        if ($is_restrict) {
            if ($skill->type == 'relieve-attack') {

            }else {
                $res .= '[' . $user_role->name . '] 被禁止攻击，' . $fight_content->num . '回合后解除！' . $line;
            }
        } else {
            if ($skill->type == 'relieve-attack') {
                DB::table('user_role')
                    ->where('id', '=', $user_role->id)
                    ->update([
                        'mp' => DB::raw('`mp` - ' . $skill_content->use_mp),
                    ])
                ;
                $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，您当前并未被禁！' . $line;
            }
        }

        $res .= Enemy::attackToUserRoleToQQ($user_role, $enemy);

        return $res;
    }
}
