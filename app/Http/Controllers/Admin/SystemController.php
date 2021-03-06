<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Models\Item;
use App\Models\Lottery;
use App\Models\Enemy;
use App\Support\Facades\Captcha;
use GatewayClient\Gateway;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function __construct()
    {
        Gateway::$registerAddress = '127.0.0.1:1238';
    }

    // 测试
    public function test()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'client_id' => ['required'],
            ], [
                'client_id.required' => 'client_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            Gateway::bindUid($query['client_id'], 1);

            Gateway::sendToAll(json_encode
                ([
                    'type'=>'test',
                    'message'=>'测试呀！'
                ])
            );
//            $query['client_id'];

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function test1()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'synthesis_id' => ['required'],
            ], [
                'synthesis_id.required' => 'synthesis_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            Gateway::sendToAll(json_encode
                ([
                    'type'=>'',
                    'message'=>$query['synthesis_id']
                ])
            );

            $client_id  = Gateway::getClientIdByUid('1');

            Gateway::closeClient($client_id[0], json_encode
                ([
                    'type'=>'',
                    'message'=>"对不起，您以下线!"
                ])
            );

            return Response::json([
                'code'    => 200,
                'message' => $client_id,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        $user_role_id = Session::get('userRole.name');

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

        $drugs = DB::query()
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
            ->where('uk.item_num', '>', 0)
            ->whereIn('i.type', [1, 2, 3])
            ->get()
        ;

//        return Response::view('admin/test/web/index', [
//            'rows' => $rows,
//            'drugs' => $drugs,
//        ]);

        return Response::view('admin/test/test', [
            'rows' => $rows,
            'drugs' => $drugs,
        ]);
    }

    // 法宝设置
    public function magicWeapon()
    {
        $date =  [
            'name' => '凝气披风',
            'content' => [
                [
                    'type'    => 'magic_weapon',
                    'max_hp'  => 600,
                    'max_mp'  => 600,
                    'attack'  => 600,
                    'magic'   => 600,
                    'crit'    => 17,
                    'dodge'   => 16,
                    'defense' => 600,
                ]
            ]
        ];

        DB::table('item')->insert([
            'name'         => $date['name'],
            'description'  => '强力法宝',
            'type'         => 10,
            'level'        => 100,
            'content'      => json_encode($date['content']),
            'recycle_coin' => 1000,
        ]);

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 设置怪物
    public function enemy()
    {
        $date =  [
            'items' => [
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
//                [
//                    'id'   => 301,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 302,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 303,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 304,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 305,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 306,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 307,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 308,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 309,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 310,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 311,
//                    'num'  => 1,
//                ]
            ],
            'certain_items' => [
                [
                    'id'   => 312,
                    'num'  => 1,
                ]
            ]
        ];

        DB::table('enemy')->insert([
            'name'          => '迷雾血魔',
            'hp'            => 6160,
            'attack'        => 3680,
            'defense'       => 2860,
            'level'         => 94,
            'exp'           => 30,
            'coin'          => 30,
            'items'         => json_encode($date['items']),
//            'certain_items' => json_encode($date['certain_items']),
            'probability'   => 1,
            'description'   => '',
            'type'          => 0,
            'move_map_id'   => 0
        ]);

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 设置任务
    public function mission()
    {
        $date =  [
            'name' => '',
            'items' => [
                [
                    'id'   => 29,
                    'num'  => 1,
                ],
                [
                    'id'   => 23,
                    'num'  => 1,
                ],
                [
                    'id'   => 30,
                    'num'  => 1,
                ],
//                [
//                    'id'   => 21,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 22,
//                    'num'  => 1,
//                ]
            ],
            'certain_items' => [
                [
                    'id'    => 122,
                    'num'  => 10,
                ]
            ]
        ];

        DB::table('mission')->insert([
            'name'          => $date['name'],
            'hp'            => 120,
            'attack'        => 75,
            'defense'       => 45,
            'level'         => 15,
            'exp'           => 5,
            'coin'          => 25,
            'items'         => json_encode($date['items']),
//            'certain_items' => json_encode($date['certain_items']),
            'probability'   => 20,
            'description'   => '',
            'type'          => 0,
            'move_map_id'   => 0
        ]);

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 设置提炼
    public function refine()
    {
        $npc_id = 46;
        $count = 0;
        $item = 485;
        $item2 = 471;
        $success_rate = 30;

        while ($count < 14){
            $date =  [
                'requirements' => [
                    [
                        'id'   => 128,
                        'num'  => 20,
                    ],
                    [
                        'id'   => 133,
                        'num'  => 20,
                    ],
                    [
                        'id'   => 156,
                        'num'  => 8,
                    ],
                    [
                        'id'   => $item2,
                        'num'  => 1,
                    ],
                ],
                'retains' => [
                    [
                        'id'   => $item2,
                        'num'  => 1,
                    ]
                ]
            ];

            DB::table('refine')->insert([
                'npc_id'        => $npc_id,
                'item_id'       => $item,
                'requirements'  => json_encode($date['requirements']),
                'retains'       => json_encode($date['retains']),
                'success_rate'  => $success_rate
            ]);
            $count++;
            $item++;
            $item2++;
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    public function test2()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'synthesis_id' => ['required'],
            ], [
                'synthesis_id.required' => 'synthesis_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            // 判断合成是否存在
            $row = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('synthesis AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->where('s.id', '=', $query['synthesis_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该合成', 400);
            }

            $user_vip = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($user_vip) {
                $row->success_rate += $user_vip->success_rate;
            }

            // 判断是否有足够的物品数量
            $requirements = json_decode($row->requirements);
            $retains = json_decode($row->retains);

            $res_bool = UserKnapsack::isHaveItems($requirements);

            if (!$res_bool) {
                throw new InvalidArgumentException('背包中没有足够的材料', 400);
            }

            foreach ($requirements as $k => $v) {
                if ($v->id == $retains[0]->id) {
                    unset($requirements[$k]);
                }
            }

            if (!is_success($row->success_rate)) {
                UserKnapsack::useItems($requirements);
                throw new InvalidArgumentException('合成失败！', 400);
            }

            DB::beginTransaction();

            UserKnapsack::useItems($requirements);

            $retains[0]->id = $row->item_id;
            $retains[0]->num = 1;

            UserKnapsack::addItems($retains);

            DB::commit();

            $res = '恭喜您！成功合成：' . $row->item_name . '<br>';

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

    public function test3()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'shop_business_id' => ['required'],
                'var_data' => ['required', 'integer'],
            ], [
                'shop_business_id.required' => 'shop_business_id不能为空',
                'var_data.required' => 'var_data不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'sb.*',
                    'i.name AS item_name',
                ])
                ->from('shop_business AS sb')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'sb.item_id')
                    ;
                })
                ->where('sb.id', '=', $query['shop_business_id'])
                ->where('sb.num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('商品数量不足：' . $query['var_data'], 400);
            }

            if ($row->user_role_id == $user_role_id) {
                throw new InvalidArgumentException('不能购买自己上架的商品!', 400);
            }

            $userRole = UserRole::getUserRole();

            $use_coin = $row->coin * $query['var_data'];

            if ($userRole->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }

            $row->id = $row->item_id;
            $row->num = $query['var_data'];

            $data = [
                $row
            ];

            DB::beginTransaction();

            UserKnapsack::addItems($data);

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . $use_coin),
                ])
            ;

            DB::table('user_role')
                ->where('id', '=', $row->user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` + ' . (int)round($use_coin * 0.9)),
                ])
            ;

            DB::table('shop_business')
                ->where('id', '=', $query['shop_business_id'])
                ->update([
                    'num' => DB::raw('`num` - ' . $query['var_data']),
                ])
            ;

            DB::table('shop_business')
                ->where('num', '<=', 0)
                ->delete()
            ;

            DB::commit();

            $res = '购买成功：' . $row->item_name . '*' . $query['var_data'] . ' - ' . $use_coin . '金币';

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

    // 设置角色测试
    public function userRole()
    {
        try {

            DB::table('user_role')
                ->where('id', '=', 1)
                ->update([
                    'hp'  => 1000000,
                    'mp'  => 1000000,
                    'max_hp'  => 2095,
                    'max_mp'  => 2095,
                    'attack'  => 3198,
                    'magic'   => 2800,
//                    'crit'    => 14,
//                    'dodge'   => 14,
                    'defense' => 2728,
                    'map_id'  => 186,
//                      'level'   => 200
                ])
            ;

            dd('完成：' . date('Y-m-d H:i:s', time()));

        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function userKnapsack()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'name' => ['required'],
            ], [
                'name.required' => '物品名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $res = DB::query()
                ->select([
                    'i.*',
                ])
                ->from('item AS i')
                ->where('name', '=', $query['name'])
                ->get()
                ->first()
            ;

            if (!$res) {
                throw new InvalidArgumentException('没有找到该物品', 400);
            }

            $data[0] = $res;

            $data[0]->id = $res->id;
            $data[0]->num = $query['num'] ?? 10;

            UserKnapsack::addItems($data, 1);

            dd('完成：' . date('Y-m-d H:i:s', time()));

        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
