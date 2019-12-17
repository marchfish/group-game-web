<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equip;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use App\Models\UserKnapsack;

class EquipController extends Controller
{
    // 插入装备数据
    public function equip()
    {
        $equips = [
            [
                'name' => '凝气魔爪',
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 390,
                        'defense' => 350,
                        'max_hp' => 200,
                        'max_mp' => 150,
                        'magic' => 400,
                        'dodge' => 1,
                    ]
                ],
            ],
            [
                'name' => '凝气魔盔',
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 380,
                        'defense' => 350,
                        'max_hp' => 200,
                        'max_mp' => 150,
                        'magic' => 400,
                        'crit'  => 1
                    ]
                ],
            ],
            [
                'name' => '凝气魔甲',
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 380,
                        'defense' => 450,
                        'max_hp' => 200,
                        'max_mp' => 200,
                        'magic' => 400
                    ]
                ],
            ],
//            [
//                'name' => '凝气耳环',
//                'content' => [
//                    [
//                        'type' => 'earring',
//                        'attack' => 310,
//                        'defense' => 210,
//                        'max_hp' => 160,
//                        'max_mp' => 160,
//                        'magic' => 280,
//                        'crit'  => 1
//                    ]
//                ],
//            ],
//            [
//                'name' => '凝气项链',
//                'content' => [
//                    [
//                        'type' => 'necklace',
//                        'attack' => 310,
//                        'defense' => 210,
//                        'max_hp' => 160,
//                        'max_mp' => 160,
//                        'magic' => 280,
//                        'crit'  => 1
//                    ]
//                ],
//            ],
//            [
//                'name' => '凝气手镯',
//                'content' => [
//                    [
//                        'type' => 'bracelet',
//                        'attack' => 310,
//                        'defense' => 210,
//                        'max_hp' => 160,
//                        'max_mp' => 160,
//                        'magic' => 280,
//                        'crit'  => 1
//                    ]
//                ],
//            ],
//            [
//                'name' => '凝气戒指',
//                'content' => [
//                    [
//                        'type' => 'ring',
//                        'attack' => 310,
//                        'defense' => 210,
//                        'max_hp' => 160,
//                        'max_mp' => 160,
//                        'magic' => 280,
//                        'crit'  => 1
//                    ]
//                ],
//            ],
//            [
//                'name' => '凝气战靴',
//                'content' => [
//                    [
//                        'type' => 'shoes',
//                        'attack' => 310,
//                        'defense' => 210,
//                        'max_hp' => 160,
//                        'max_mp' => 160,
//                        'magic' => 280,
//                        'crit'  => 1
//                    ]
//                ],
//            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '强力装备【迷雾征服者】',
                'type'         => 10,
                'level'        => 110,
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
                'name' => '凝气战剑' . $color,
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 370,
                        'defense' => 250,
                        'max_hp' => 370,
                        'max_mp' => 370,
                        'magic' => 420,
                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '凝气头盔' . $color,
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 300,
                        'defense' => 240,
                        'max_hp' => 270,
                        'max_mp' => 270,
                        'magic' => 270
                    ]
                ],
            ],
            [
                'name' => '凝气战甲' . $color,
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 300,
                        'defense' => 370,
                        'max_hp' => 370,
                        'max_mp' => 370,
                        'magic' => 370,
                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '凝气耳环' . $color,
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 300,
                        'defense' => 240,
                        'magic' => 270,
                        'crit'  => 4,
                    ]
                ],
            ],
            [
                'name' => '凝气项链' . $color,
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 300,
                        'defense' => 240,
                        'max_hp' => 270,
                        'max_mp' => 270,
                        'magic' => 270,
                        'crit'  => 4,
                    ]
                ],
            ],
            [
                'name' => '凝气手镯' . $color,
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 300,
                        'defense' => 240,
                        'magic' => 270,
                        'crit'  => 4,
                    ]
                ],
            ],
            [
                'name' => '凝气戒指' . $color,
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 300,
                        'defense' => 240,
                        'magic' => 270,
                        'crit'  => 4,
                    ]
                ],
            ],
            [
                'name' => '凝气战靴' . $color,
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 300,
                        'defense' => 240,
                        'magic' => 270,
                        'crit'  => 4,
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '强力装备【魔已无所畏惧】，品质：' . $color,
                'type'         => 10,
                'level'        => 90,
                'content'      => json_encode($equip['content']),
                'recycle_coin' => 1000,
            ]);
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }
}
