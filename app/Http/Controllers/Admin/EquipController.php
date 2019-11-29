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
                'name' => '炎魔战剑',
                'content' => [
                    [
                        'type'   => 'weapon',
                        'attack' => 300,
                        'defense' => 180,
                        'max_hp' => 300,
                        'max_mp' => 300,
                        'magic' => 350,
                        'dodge' => 1,
                    ]
                ],
            ],
            [
                'name' => '炎魔头盔',
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 230,
                        'defense' => 170,
                        'max_hp' => 200,
                        'max_mp' => 200,
                        'magic' => 200
                    ]
                ],
            ],
            [
                'name' => '炎魔战甲',
                'content' => [
                    [
                        'type' => 'clothes',
                        'attack' => 230,
                        'defense' => 300,
                        'max_hp' => 300,
                        'max_mp' => 300,
                        'magic' => 300,
                        'dodge' => 1,
                    ]
                ],
            ],
            [
                'name' => '炎魔耳环',
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 230,
                        'defense' => 170,
                        'magic' => 200,
                        'crit'  => 2,
                    ]
                ],
            ],
            [
                'name' => '炎魔项链',
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 230,
                        'defense' => 170,
                        'max_hp' => 200,
                        'max_mp' => 200,
                        'magic' => 200,
                        'crit'  => 2,
                    ]
                ],
            ],
            [
                'name' => '炎魔手镯',
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 230,
                        'defense' => 170,
                        'magic' => 200,
                        'crit'  => 2,
                    ]
                ],
            ],
            [
                'name' => '炎魔戒指',
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 230,
                        'defense' => 170,
                        'magic' => 200,
                        'crit'  => 2,
                    ]
                ],
            ],
            [
                'name' => '炎魔战靴',
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 230,
                        'defense' => 170,
                        'magic' => 200,
                        'crit'  => 2,
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '高级装备',
                'type'         => 10,
                'level'        => 90,
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
                'name' => '炎魔战剑' . $color,
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
                'name' => '炎魔头盔' . $color,
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
                'name' => '炎魔战甲' . $color,
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
                'name' => '炎魔耳环' . $color,
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
                'name' => '炎魔项链' . $color,
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
                'name' => '炎魔手镯' . $color,
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
                'name' => '炎魔戒指' . $color,
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
                'name' => '炎魔战靴' . $color,
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
