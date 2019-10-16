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
//            'name' => '藤蔓怪人',
            'items' => [
                [
                    'id'   => 122,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 2,
                ],
                [
                    'id'   => 127,
                    'num'  => 1,
                ],
                [
                    'id'   => 128,
                    'num'  => 1,
                ],
                [
                    'id'   => 71,
                    'num'  => 1,
                ],
                [
                    'id'   => 122,
                    'num'  => 3,
                ],
                [
                    'id'   => 122,
                    'num'  => 5,
                ],
                [
                    'id'   => 64,
                    'num'  => 1,
                ],
                [
                    'id'   => 137,
                    'num'  => 1,
                ],
                [
                    'id'   => 70,
                    'num'  => 1,
                ],
                [
                    'id'   => 129,
                    'num'  => 1,
                ],
                [
                    'id'   => 86,
                    'num'  => 1,
                ],
                [
                    'id'   => 128,
                    'num'  => 2,
                ]
            ],
            'certain_items' => [
                [
                    'id'    => 2,
                    'num'  => 1,
                ]
            ]
        ];

        DB::table('enemy')->insert([
            'name'          => '厚壳鬼',
            'hp'            => 300,
            'attack'        => 405,
            'defense'       => 395,
            'level'         => 21,
            'exp'           => 21,
            'coin'          => 21,
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
}
