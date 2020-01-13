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
                'name' => '炼狱魔爪',
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
                'name' => '炼狱魔盔',
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
                'name' => '炼狱魔甲',
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
//                'name' => '炼狱耳环',
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
//                'name' => '炼狱项链',
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
//                'name' => '炼狱手镯',
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
//                'name' => '炼狱戒指',
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
//                'name' => '炼狱战靴',
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
        $color = '(红)';
//        $quality = 'blue';

        $equips = [
            [
                'name' => '炼狱战剑' . $color,
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 430,
                        'defense' => 330,
                        'max_hp' => 230,
                        'max_mp' => 180,
                        'magic' => 350
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱头盔' . $color,
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 430,
                        'defense' => 330,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 340
                    ]
                ],
            ],
            [
                'name' => '炼狱战甲' . $color,
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 430,
                        'defense' => 430,
                        'max_hp' => 230,
                        'max_mp' => 230,
                        'magic' => 350
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱利刃' . $color,
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 380,
                        'defense' => 390,
                        'max_hp' => 230,
                        'max_mp' => 180,
                        'magic' => 350
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱妖盔' . $color,
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 330,
                        'defense' => 390,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 340
                    ]
                ],
            ],
            [
                'name' => '炼狱妖甲' . $color,
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 380,
                        'defense' => 510,
                        'max_hp' => 230,
                        'max_mp' => 230,
                        'magic' => 350
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱魔杖' . $color,
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 380,
                        'defense' => 330,
                        'max_hp' => 230,
                        'max_mp' => 180,
                        'magic' => 430
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱魔盔' . $color,
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 330,
                        'defense' => 330,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 380
                    ]
                ],
            ],
            [
                'name' => '炼狱魔袍' . $color,
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 380,
                        'defense' => 430,
                        'max_hp' => 230,
                        'max_mp' => 230,
                        'magic' => 430
//                        'dodge' => 3,
                    ]
                ],
            ],
            [
                'name' => '炼狱耳环' . $color,
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 330,
                        'defense' => 230,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 300,
                        'crit'  => 1,
                    ]
                ],
            ],
            [
                'name' => '炼狱项链' . $color,
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 330,
                        'defense' => 230,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 300,
                        'crit'  => 1,
                    ]
                ],
            ],
            [
                'name' => '炼狱手镯' . $color,
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 330,
                        'defense' => 230,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 300,
                        'crit'  => 1,
                    ]
                ],
            ],
            [
                'name' => '炼狱戒指' . $color,
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 330,
                        'defense' => 230,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 300,
                        'crit'  => 1,
                    ]
                ],
            ],
            [
                'name' => '炼狱战靴' . $color,
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 330,
                        'defense' => 230,
                        'max_hp' => 180,
                        'max_mp' => 180,
                        'magic' => 300,
                        'crit'  => 1,
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '强力装备【迷雾挑战者】，品质：' . $color,
                'type'         => 10,
                'level'        => 100,
                'content'      => json_encode($equip['content']),
                'recycle_coin' => 800,
            ]);
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }
}
