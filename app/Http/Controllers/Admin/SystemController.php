<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAccount;
use App\Models\AdminPermission;
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
            'name' => '石头怪',
            'items' => [
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 127,
                    'num'  => 1,
                ],
                [
                    'id'   => 48,
                    'num'  => 1,
                ],
                [
                    'id'   => 49,
                    'num'  => 1,
                ],
//                [
//                    'id'   => 35,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 36,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 37,
//                    'num'  => 1,
//                ],
//                [
//                    'id'   => 38,
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
            'name'          => $date['name'],
            'hp'            => 170,
            'attack'        => 185,
            'defense'       => 150,
            'level'         => 13,
            'exp'           => 13,
            'coin'          => 50,
            'items'         => json_encode($date['items']),
//            'certain_items' => json_encode($date['certain_items']),
            'probability'   => 10,
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
            'name' => '火之鸡',
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
}
