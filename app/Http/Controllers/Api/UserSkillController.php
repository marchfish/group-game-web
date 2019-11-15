<?php

namespace App\Http\Controllers\Api;

use App\Models\Skill;
use App\Models\UserRole;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserSkillController extends Controller
{
    public function show()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $res = '';

            // 判断是否存在物品
            $rows = DB::query()
                ->select([
                    's.*',
                    'us.quick_key as quick_key',
                ])
                ->from('skill AS s')
                ->join('user_skill AS us', function ($join) {
                    $join
                        ->on('us.skill_id', '=', 's.id')
                    ;
                })
                ->where('us.user_role_id', '=', $user_role_id)
                ->get()
            ;

            foreach ($rows as $row) {
                $res .= $row->name . '：' . $row->level . '阶' . ' -- ' . $row->quick_key . '\r\n';
            }

            $res .= '技能后面的数字为快捷键，可自行绑定(默认无绑定)\r\n';
            $res .= '输入：快捷键 技能名称 1-8';

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 学习
    public function study()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
            ], [
                'item_name.required' => '名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'i.*',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('i.name', '=', $query['item_name'])
                ->where('uk.item_num', '>=', 1)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('您没有该技能书', 400);
            }

            if ($row->content == "" || $row->type != 40) {
                throw new InvalidArgumentException('该物品不可学习', 400);
            }

            if ($row->level > $user_role->level) {
                throw new InvalidArgumentException('无法学习，等级不足：' . $row->level, 400);
            }

            $res = Skill::study($row, $user_role_id);

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 使用技能
    public function usrSkill()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'quick_key' => ['required', 'integer', 'min:1', 'max:8'],
            ], [
                'quick_key.required' => '技能快捷键不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $user_date = UserRole::getUserDateToQQ($user_role_id);

            if ($user_date){
                if ($user_date->attack_at) {
                    $now_at = strtotime('now');
                    $attack_at = strtotime($user_date->attack_at);

                    if($now_at - $attack_at < 1) {
                        throw new InvalidArgumentException('', 400);
                    };
                }

                DB::table('user_date')
                    ->where('user_role_id', '=', $user_role_id)
                    ->update([
                        'attack_at' => date('Y-m-d H:i:s', time()),
                    ])
                ;
            }

            // 获取属性信息
            $userSkill = DB::query()
                ->select([
                    's.*',
                ])
                ->from('skill AS s')
                ->join('user_skill AS us', function ($join) {
                    $join
                        ->on('us.skill_id', '=', 's.id')
                    ;
                })
                ->where('us.user_role_id', '=', $user_role_id)
                ->where('us.quick_key', '=', $query['quick_key'])
                ->get()
                ->first()
            ;

            if (!$userSkill) {
                throw new InvalidArgumentException('没有找到该技能，请先绑定快捷键后操作！', 400);
            }

            if ($userSkill->sort == 1) {
                $res = Skill::securitySkill($user_role, $userSkill, $user_role_id);
            }else {
                // 获取怪物信息
                $enemy = DB::query()
                    ->select([
                        'e.*',
                        DB::raw('IFNULL(`ed`.`hour`, "") AS `enemy_hour`'),
                        DB::raw('IFNULL(`ed`.`refresh_at`, "") AS `enemy_refresh_at`'),
                    ])
                    ->from('enemy AS e')
                    ->join('map AS m', function ($join) {
                        $join
                            ->on('m.enemy_id', '=', 'e.id')
                        ;
                    })
                    ->leftJoin('enemy_date AS ed', function ($join) {
                        $join
                            ->on('ed.enemy_id', '=', 'e.id')
                        ;
                    })
                    ->where('m.id', '=', $user_role->map_id)
                    ->get()
                    ->first()
                ;

                if (!$enemy) {
                    throw new InvalidArgumentException('当前位置没有怪物，无法使用此技能！', 400);
                }

                if ($enemy->enemy_hour != '' && strpos($enemy->enemy_hour, date('H', time())) === false) {
                    throw new InvalidArgumentException('当前位置没有怪物，无法使用此技能！', 400);
                }

                if ($enemy->enemy_refresh_at != '' &&  time() < strtotime($enemy->enemy_refresh_at)) {
                    throw new InvalidArgumentException('怪物还未出现，无法使用此技能！刷新时间：' . date('H:i:s', strtotime($enemy->enemy_refresh_at)), 400);
                }

                $res =  Skill::skill($user_role, $userSkill, $enemy, $user_role_id);
            }

            if ($user_date){
                if ($user_date->attack_at) {
                    $now_at = strtotime('now');
                    $attack_at = strtotime($user_date->attack_at);

                    if($now_at - $attack_at < 20) {
                        $res = '';
                        $user_vip = DB::query()
                            ->select([
                                'uv.*',
                            ])
                            ->from('user_vip AS uv')
                            ->where('uv.user_role_id', '=', $user_role_id)
                            ->get()
                            ->first()
                        ;

                        if (!$user_vip || time() > strtotime($user_vip->expired_at)) {
                            $res = '';
                        }else {
                            $user_Role1 = DB::query()
                                ->select([
                                    'ur.*',
                                ])
                                ->from('user_role AS ur')
                                ->where('ur.id', '=', $user_role_id)
                                ->get()
                                ->first()
                            ;

                            if ($user_Role1->hp > 0 && $user_Role1->hp < $user_vip->protect_hp) {
                                // 判断是否存在物品
                                $row = DB::query()
                                    ->select([
                                        'i.*',
                                    ])
                                    ->from('user_knapsack AS uk')
                                    ->join('item AS i', function ($join) {
                                        $join
                                            ->on('i.id', '=', 'uk.item_id')
                                        ;
                                    })
                                    ->where('uk.user_role_id', '=', $user_role_id)
                                    ->where('i.type', '=', 1)
                                    ->where('uk.item_num', '>', 0)
                                    ->get()
                                    ->first()
                                ;

                                if (!$row) {
                                    $res = '您的血瓶没有了!';
                                }else {
                                    $item = json_decode($row->content)[0];
                                    $item->id = $row->id;

                                    Item::useDrugToQQ($item, $user_role_id, 1);
                                }
                            }
                        };
                    };
                }
            }

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 绑定快捷键
    public function setQuick()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'skill_name' => ['required'],
                'quick_key'  => ['required', 'integer', 'min:1', 'max:8'],
            ], [
                'skill_name.required' => '技能名称不能为空',
                'quick_key.required'  => '技能快捷键不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    's.*',
                    'us.quick_key as quick_key',
                ])
                ->from('skill AS s')
                ->join('user_skill AS us', function ($join) {
                    $join
                        ->on('us.skill_id', '=', 's.id')
                    ;
                })
                ->where('us.user_role_id', '=', $user_role_id)
                ->where('s.name', '=', $query['skill_name'])
                ->get()
                ->first()
            ;

            if(!$row) {
                throw new InvalidArgumentException('您没有学习该技能！', 400);
            };

            DB::beginTransaction();

            DB::table('user_skill')
                ->where('user_role_id', '=', $user_role_id)
                ->where('quick_key', '=', $query['quick_key'])
                ->update([
                    'quick_key' => 0,
                ])
            ;

            DB::table('user_skill')
                ->where('user_role_id', '=', $user_role_id)
                ->where('skill_id', '=', $row->id)
                ->update([
                    'quick_key' => $query['quick_key'],
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '设置成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

}
