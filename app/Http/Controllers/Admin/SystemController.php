<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\UserKnapsack;
use App\Models\Item;
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

    // 测试
    public function test()
    {
        try {
            $query = Request::all();

            $user_role_id = Session::get('user.account.user_role_id');

            $rows = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                    'i.level AS item_level',
                ])
                ->from('synthesis AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.npc_id', '=', 's.npc_id')
                    ;
                })
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'm.id')
                    ;
                })
                ->where('ur.id', '=', $user_role_id)
                ->get()
            ;

            $res = '';

            foreach ($rows as $row) {
                $res .= $row->item_name . '（' . $row->item_level . '级装备）-- 成功率：' . $row->success_rate . '<br>';

                $res .= '<input type="button" class="action" data-url="' . URL::to('admin/test1') . "?synthesis_id=" . $row->id . '" value="查看材料" />' . '<br>';
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

//            // 查询是否接受过该任务
//            $row1 = DB::query()
//                ->select([
//                    'mh.*',
//                ])
//                ->from('mission_history AS mh')
//                ->where('mh.mission_id', '=', $query['mission_id'])
//                ->where('mh.user_role_id', '=', $user_role_id)
//                ->get()
//                ->first()
//            ;
//
//            if (!$row1) {
//                $res .= '<input type="button" class="action" data-url="' . URL::to('mission/accept') . '" value="接受任务" />';
//            }elseif($row1->status == 150) {
//                $res .= '<input type="button" class="action" data-url="' . URL::to('mission/submit') . "?mission_id=" . $query['mission_id'] . '" value="提交任务" />';
//            }else {
//                $res .= '（已完成）';
//            }

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
                'item_id' => ['required'],
                'var_data' => ['required', 'integer'],
                'var_data1' => ['required', 'integer'],
            ], [
                'item_id.required' => '物品id不能为空',
                'var_data.required' => 'var_data不能为空',
                'var_data1.required' => 'var_data1不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if ($query['var_data1'] <= 9) {
                throw new InvalidArgumentException('物品价格小于10金币', 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('uk.item_id', '=', $query['item_id'])
                ->where('uk.item_num', '>=', $query['var_data'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有足够的物品数量', 400);
            }

            $res = '出售成功：' . $row->name . '*' . $query['var_data'];

            DB::beginTransaction();

            DB::table('shop_business')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'item_id'      => $query['item_id'],
                    'num'          => $query['var_data'],
                    'coin'         => $query['var_data1'],
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $query['item_id'])
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . $query['var_data']),
                ])
            ;

            DB::commit();

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
