<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\UserRole;
use App\Models\Rank;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class RankController extends Controller
{
    public function show()
    {
        try {
            $rows = DB::query()
                ->select([
                    'r.*',
                    DB::raw('IFNULL(`ur`.`name`, "虚位以待") AS user_role_name'),
                ])
                ->from('rank AS r')
                ->leftJoin('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'r.user_role_id')
                    ;
                })
                ->orderBy('r.num', 'asc')
                ->get()
            ;

            $sys_date = DB::query()
                ->select([
                    'sd.*',
                ])
                ->from('sys_date AS sd')
                ->get()
                ->first()
            ;

            $res = '[排位] 本赛季结束日期：'. $sys_date->rank_expired_at . '\r\n';

            foreach ($rows as $row) {
                $res .=  $row->user_role_name . ' ：第' . $row->num . '名 ' . '\r\n';
            }

            $res .= '输入：挑战';

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

    public function reward()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'rank_id' => ['required'],
            ], [
                'rank_id.required' => '查看的名次不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'r.*',
                ])
                ->from('rank AS r')
                ->where('r.id', '=', $query['rank_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该奖励', 400);
            }

            $res = '[第' . $row->num . '名奖励]\r\n';

            $res .= '奖励金币=' . $row->coin . '\r\n';

            $res .= '奖励物品=';

            // 所需物品
            $reward = json_decode($row->reward);

            $ids = array_column($reward,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');

            foreach ($reward as $rew) {

                $rew->name = $items[$rew->id] ?? '？？？';

                $res .= $rew->name . '*' . $rew->num . '，';
            }

            $res = rtrim($res, "，");

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

    public function challenge()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $user_date = UserRole::getUserDateToQQ($user_role_id);

            $now_at = date('Y-m-d H:i:s',time());

            if ($user_date && $user_date->challenge_at) {

                $difference = time_difference($user_date->challenge_at, $now_at);

                if($difference < 30) {
                    throw new InvalidArgumentException('半个小时冷却时间内，无法挑战！', 400);
                };
            }

            DB::table('user_date')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'challenge_at' => $now_at,
                ])
            ;

            $user_Role1 = DB::query()
                ->select([
                    'ur.*',
                    DB::raw('IFNULL(`r`.`num`, 0) AS num'),
                ])
                ->from('user_role AS ur')
                ->leftJoin('rank AS r', function ($join) {
                    $join
                        ->on('r.user_role_id', '=', 'ur.id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($user_Role1->coin < 500) {
                throw new InvalidArgumentException('金币不足500，不能挑战！' , 400);
            }

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . 500),
                ])
            ;

            $res = '对不起，挑战失败！';

            if ($user_Role1->num == 0) {

                $ranks = DB::query()
                    ->select([
                        'r.*',
                    ])
                    ->from('rank AS r')
                    ->get()
                ;
                foreach ($ranks as $rank) {
                    if ($rank->user_role_id == 0) {
                        DB::table('rank')
                            ->where('num', '=', $rank->num)
                            ->update([
                                'user_role_id' => $user_role_id,
                            ])
                        ;
                        $res = '恭喜您！挑战成功，当前排名：第'. $rank->num . '名';
                        break;
                    }elseif ($rank->num == 10) {
                        // 被挑战者
                        $user_Role2 = DB::query()
                            ->select([
                                'ur.*',
                                'r.num as num'
                            ])
                            ->from('user_role AS ur')
                            ->join('rank AS r', function ($join) {
                                $join
                                    ->on('r.user_role_id', '=', 'ur.id')
                                ;
                            })
                            ->where('r.num', '=', 10)
                            ->get()
                            ->first()
                        ;

                        $pk_res = Rank::pk($user_Role1, $user_Role2);

                        if ($pk_res) {
                            $res = '恭喜您！挑战成功，当前排名：第'. $user_Role2->num . '名';
                        }

                        break;
                    }
                }
            } else {
                if ($user_Role1->num == 1) {
                    throw new InvalidArgumentException('您已经到达了巅峰!', 200);
                }

                // 被挑战者
                $user_Role2 = DB::query()
                    ->select([
                        'ur.*',
                        'r.num as num'
                    ])
                    ->from('user_role AS ur')
                    ->join('rank AS r', function ($join) {
                        $join
                            ->on('r.user_role_id', '=', 'ur.id')
                        ;
                    })
                    ->where('r.num', '=', $user_Role1->num - 1)
                    ->get()
                    ->first()
                ;

                $pk_res = Rank::pk($user_Role1, $user_Role2);

                if ($pk_res) {
                    $res = '恭喜您！挑战成功，当前排名：第'. $user_Role2->num . '名';
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
}
