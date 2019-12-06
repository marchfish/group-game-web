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

            $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，生命 +' . $skill_hp . $line . '当前血量：' . $user_hp . $line;
            $res .= '当前蓝量：' . ($user_role->mp - $skill_content->use_mp);
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

        if (isset($skill_content->cd)) {
            $now_time = date('Y-m-d H:i:s', time());

            $cd_time = time_difference($now_time, $skill->expired_at, 'second');

            if ($cd_time > 0) {
                throw new InvalidArgumentException('CD冷却中：' . $cd_time . '秒', 400);
            }

            DB::table('user_skill')
                ->where('user_role_id', '=', $user_role->id)
                ->where('skill_id', '=', $skill->id)
                ->update([
                    'expired_at' => date('Y-m-d H:i:s', strtotime('+' . $skill_content->cd . ' seconds')),
                ])
            ;
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

        $restrict_num = 0;
        if ($fight && $fight->content != '') {
            $fight_contents = json_decode($fight->content);

            foreach ($fight_contents as $fight_content) {
                if (isset($fight_content->skill_type) && $fight_content->skill_type == 'restrict-attack') {
                    $is_restrict = true;
                    $restrict_num = $fight_content->num;
                }
            }
        }

        if ($fight)
        {
            $enemy->hp = $fight->enemy_hp;
            $enemy->mp = $fight->enemy_mp;
        }

        if ($is_restrict) {
            $restrict_num -= 1;
            if ($skill->type == 'relieve-attack') {
                if (is_success($skill_content->probability)){
                    foreach ($fight_contents as $k => $v) {
                        if (isset($v->skill_type) && $v->skill_type == 'restrict-attack') {
                            unset($fight_contents[$k]);
                        }
                    }
                    $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，成功解除限制！' . $line;
                }else {
                    foreach ($fight_contents as $k => $v) {
                        if (isset($v->skill_type) && $v->skill_type == 'restrict-attack') {
                            $v->num = $restrict_num;
                        }
                    }
                    $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，解除限制失败！' . $line;
                }
            }else {
                foreach ($fight_contents as $k => $v) {
                    if (isset($v->skill_type) && $v->skill_type == 'restrict-attack') {
                        if ($restrict_num <= 0) {
                            unset($fight_contents[$k]);
                            $res .= '[' . $user_role->name . '] 禁止攻击已解除！' . $line;
                        }else {
                            $v->num = $restrict_num;
                            $res .= '[' . $user_role->name . '] 被禁止攻击，' . $restrict_num . '回合后解除！' . $line;
                        }
                    }
                }
            }
            DB::table('fight')
                ->where('user_role_id', '=', $user_role->id)
                ->where('enemy_id', '=', $enemy->id)
                ->update([
                    'content' => json_encode($fight_contents),
                ])
            ;
        } else {
            if ($skill->type == 'relieve-attack') {
                $res .= '[' . $user_role->name . '] 使用：' . $skill->name . '，您当前并未被禁！' . $line;
            }

            // 使用攻击技能
            if ($skill->type == 'attack') {
                $res .= self::attackSkill($user_role, $skill, $skill_content, $enemy, $user_role_id);
            }
        }

        DB::table('user_role')
            ->where('id', '=', $user_role->id)
            ->update([
                'mp' => DB::raw('`mp` - ' . $skill_content->use_mp),
            ])
        ;

        if ($user_role_id != 0) {
            if ($enemy->skill_probability != 0 && is_success($enemy->skill_probability)) {
                $res .= Enemy::skillToUserRole($user_role, $enemy, $user_role_id);
            }else {
                $res .= Enemy::attackToUserRoleToQQ($user_role, $enemy);
            }
        }else {
            if ($enemy->skill_probability != 0 && is_success($enemy->skill_probability)) {
                $res .= Enemy::skillToUserRole($user_role, $enemy);
            }else {
                $res .= Enemy::attackToUserRole($user_role, $enemy);
            }
        }

        return $res;
    }

    // 使用攻击技能
    public static function attackSkill($user_role, $skill, $skill_content, $enemy, int $user_role_id = 0){
        $line = '<br>';

        $res = '';
        $success_bool = true;

        if ($user_role_id != 0) {
            $line = '\r\n';
        }

        if (isset($skill_content->probability)) {
            if (!is_success($skill_content->probability)){
                $success_bool = false;
            }
        }

        if (!$success_bool) {
            $res = '[' . $user_role->name . '] 使用：' . $skill->name . '，使用失败！' . $line;
        }

        $user_skill = 0;

        if (isset($skill_content->attack)) {
            $user_skill += (int)round($user_role->attack * ($skill_content->attack / 100));
        };

        if (isset($skill_content->magic)) {
            $user_skill += (int)round($user_role->magic * ($skill_content->magic / 100));
        };

        $user_hurt = $user_skill - $enemy->defense;

        if ($user_hurt <= 0) {
            $res .= '您无法破防' . $line;
        }else {
            $hurt_wave = mt_rand(0, round($user_hurt * 0.5));

            $rand_num = mt_rand(0, 100);

            if ($rand_num >= 50 || is_success($user_role->crit)) {
                $user_hurt += $hurt_wave;
            } else {
                $user_hurt -= $hurt_wave;
            }

            $enemy->hp -= $user_hurt;

            // 存入数据
            DB::table('fight')->updateOrInsert([
                'user_role_id' => $user_role->id,
            ], [
                'enemy_id' => $enemy->id,
                'enemy_hp' => $enemy->hp < 0 ? 0 : $enemy->hp,
                'enemy_mp' => $enemy->mp,
            ]);

            if ($enemy->hp <= 0) {
                $res .= $enemy->name . '被您击败了' . $line;

                UserRole::setExpAndCoin($enemy, $user_role->id);

                $res .= '获得经验=' . $enemy->exp . $line;
                $res .= '获得金币=' . $enemy->coin . $line;

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
                    $res .= '获得物品:' . UserKnapsack::addItems($certain_items, $user_role_id) . $line;
                }

                $is_up = UserRole::is_upgrade($user_role->id);

                if ($is_up != 0) {
                    $res .= '恭喜您！等级提升至：' . $is_up . $line;
                }

                if ($enemy->move_map_id != 0) {
                    DB::table('user_role')
                        ->where('id', '=', $user_role->id)
                        ->update([
                            'map_id' => $enemy->move_map_id,
                        ]);

                    $res .= '您已被传送出该位置' . $line;
                }

                // 怪物刷新时间
                Enemy::refresh($enemy);

                throw new InvalidArgumentException($res, 400);
            }

            $res = '[' . $user_role->name . '] 使用：' . $skill->name . '，' . $enemy->name . '-' . $user_hurt . ' 血量：' . $enemy->hp . $line . $res;
        }

        return $res;
    }
}
