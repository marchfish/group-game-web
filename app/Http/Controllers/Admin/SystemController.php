<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Models\Item;
use App\Models\Lottery;
use App\Models\Enemy;
use App\Support\Facades\Captcha;
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
    public function index()
    {
        return Response::view('admin/test/index');
    }

    // 插入装备数据
    public function equip()
    {
        $equips = [
            [
                'name' => '冰封战剑',
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 120,
                        'max_hp' => 10,
                        'max_mp' => 10
                    ]
                ],
            ],
            [
                'name' => '冰封头盔',
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 110,
                        'defense' => 110
                    ]
                ],
            ],
            [
                'name' => '冰封战甲',
                'content' => [
                    [
                        'type' => 'clothes',
                        'defense' => 120,
                        'max_hp' => 10,
                        'max_mp' => 10
                    ]
                ],
            ],
            [
                'name' => '冰封耳环',
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 110
                    ]
                ],
            ],
            [
                'name' => '冰封项链',
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 110,
                        'defense' => 110
                    ]
                ],
            ],
            [
                'name' => '冰封手镯',
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 110
                    ]
                ],
            ],
            [
                'name' => '冰封戒指',
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 110
                    ]
                ],
            ],
            [
                'name' => '冰封战靴',
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 110,
                        'defense' => 110
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '中级装备',
                'type'         => 10,
                'level'        => 35,
                'content'      => json_encode($equip['content']),
                'recycle_coin' => 500,
            ]);
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    public function colorEquip()
    {
        $color = '【橙】';
//        $quality = 'blue';

        $equips = [
            [
                'name' => '冰封战剑' . $color,
                'content' => [
                    [
                        'type'    => 'weapon',
                        'attack'  => 180,
                        'max_hp' => 40,
                        'max_mp' => 40,
                        'magic'  => 10,
                        'crit'    => 1,
                        'dodge'   => 1
                    ]
                ],
            ],
            [
                'name' => '冰封头盔' . $color,
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 170,
                        'defense' => 170
                    ]
                ],
            ],
            [
                'name' => '冰封战甲' . $color,
                'content' => [
                    [
                        'type' => 'clothes',
                        'defense' => 180,
                        'max_hp' => 40,
                        'max_mp' => 40,
                        'magic'  => 10,
                        'crit'    => 1,
                        'dodge'   => 1
                    ]
                ],
            ],
            [
                'name' => '冰封耳环' . $color,
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 170
                    ]
                ],
            ],
            [
                'name' => '冰封项链' . $color,
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 170,
                        'defense' => 170
                    ]
                ],
            ],
            [
                'name' => '冰封手镯' . $color,
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 170
                    ]
                ],
            ],
            [
                'name' => '冰封戒指' . $color,
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 170
                    ]
                ],
            ],
            [
                'name' => '冰封战靴' . $color,
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 170,
                        'defense' => 170
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '特级装备，品质：' . $color,
                'type'         => 10,
                'level'        => 35,
                'content'      => json_encode($equip['content']),
                'recycle_coin' => 1000,
            ]);
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 法宝设置
    public function magicWeapon()
    {
        $date =  [
            'name' => '冰封寒盾',
            'content' => [
                [
                    'type'    => 'magic_weapon',
                    'max_hp'  => 10,
                    'max_mp'  => 10,
                    'attack'  => 30,
                    'magic'   => 30,
                    'crit'    => 14,
                    'dodge'   => 14,
                    'defense' => 30,
                ]
            ]
        ];

        DB::table('item')->insert([
            'name'         => $date['name'],
            'description'  => '中级法宝',
            'type'         => 10,
            'level'        => 20,
            'content'      => json_encode($date['content']),
            'recycle_coin' => 1000,
        ]);

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 设置怪物
    public function enemy()
    {
        $date =  [
//            'name' => '藤蔓怪人',
            'items' => [
                [
                    'id'   => 111,
                    'num'  => 1,
                ],
                [
                    'id'   => 112,
                    'num'  => 1,
                ],
                [
                    'id'   => 113,
                    'num'  => 1,
                ],
                [
                    'id'   => 114,
                    'num'  => 1,
                ],
                [
                    'id'   => 115,
                    'num'  => 1,
                ],
                [
                    'id'   => 116,
                    'num'  => 1,
                ],
                [
                    'id'   => 117,
                    'num'  => 1,
                ],
                [
                    'id'   => 118,
                    'num'  => 1,
                ],
//                [
//                    'id'   => 118,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 126,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 129,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 104,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 128,
//                    'num'  => 1,
//                ]
            ],
            'certain_items' => [
                [
                    'id'    => 2,
                    'num'  => 1,
                ]
            ]
        ];

        DB::table('enemy')->insert([
            'name'          => '冰封魔王【BOSS】',
            'hp'            => 400,
            'attack'        => 800,
            'defense'       => 800,
            'level'         => 30,
            'exp'           => 30,
            'coin'          => 30,
            'items'         => json_encode($date['items']),
//            'certain_items' => json_encode($date['certain_items']),
            'probability'   => 6,
            'description'   => '',
            'type'          => 10,
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
            'level'         => 5,
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
        $npc_id = 17;
        $count = 0;
        $item = 237;
        $item2 = 229;
        $success_rate = 10;

        while ($count < 8){
            $date =  [
                'requirements' => [
                    [
                        'id'   => 130,
                        'num'  => 10,
                    ],
                    [
                        'id'   => 135,
                        'num'  => 10,
                    ],
                    [
                        'id'   => 156,
                        'num'  => 10,
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
//                'retains'       => json_encode($date['retains']),
                'success_rate'  => $success_rate
            ]);
            $count++;
            $item++;
            $item2++;
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }

    // 测试
    public function test()
    {
        try {
            $user_role_id = Session::get('user.account.user_role_id');

            $res = '';

            return Response::json([
                'code'    => 200,
                'message' => '成功卸下：',
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

            $user_role_id = Session::get('user.account.user_role_id');

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
                throw new InvalidArgumentException('找不到该合成', 400);
            }

            $res = '[' . $row->item_name . ']' . '<br>';

            $res .= '所需物品=';

            // 所需物品
            $requirements = json_decode($row->requirements);

            $ids = array_column($requirements,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');

            foreach ($requirements as $requirement) {
                $requirement->name = $items[$requirement->id] ?? '？？？';

                $res .= $requirement->name . '*' . $requirement->num . '，';
            }

            $res = rtrim($res, "，") . '<br>';

            // 失败保留
            $retains = json_decode($row->retains);

            $ids = array_column($retains,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');
            $res .= '失败保留物品=';

            foreach ($retains as $retain) {
                $retain->name = $items[$retain->id] ?? '？？？';
                $res .= $retain->name . '*' . $retain->num . '，';
            }

            $res = rtrim($res, "，") . '<br>';

            $res .= '成功机率=' . $row->success_rate . '% <br>';

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
}
