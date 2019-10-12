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

class PublicController extends Controller
{
    /**
     *显示登录.
     */
    public function loginShow()
    {
        if (Session::has('admin.account')) {
            return Response::redirectTo('admin');
        } else {
            return Response::view('admin/public/login');
        }
    }

    /**
     *验证码设置.
     */
    public function captcha()
    {
        Captcha::build(100, 34);

        Session::put('admin.captcha', Captcha::getPhrase());

        return Response::stream(function () {
            Captcha::output();
        }, 200, ['Content-type' => 'image/jpeg']);
    }

    /**
     *登录验证
     */
    public function login()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'username' => ['required'],
                'password' => ['required'],
                'captcha'  => ['required'],
            ], [
                'username.required' => '用户名不能为空',
                'password.required' => '密码不能为空',
                'captcha.required'  => '验证码不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if (strtolower($query['captcha']) != Session::get('admin.captcha')) {
                throw new InvalidArgumentException('验证码错误', 400);
            }

            $account = AdminAccount::getByName($query['username']);

            if (!$account || !Hash::check($query['password'], $account->password)) {
                throw new InvalidArgumentException('用户名或密码错误', 400);
            }

            if ($account->id == 1) {
                // 超级管理员获取所有权限
                $permissions = AdminPermission::all();
            } else {
                // 获取用户权限
                $permissions = AdminPermission::getByAccountId($account->id);
            }

            // 未在 admin_permission 表中的 controller 白名单
            $white_list = [
                'IndexController',
            ];

            $s = '';

            foreach ($permissions as $permission) {
                $s .= ',' . $permission->controller;
            }

            $account->controllers = array_flip(array_merge($white_list, explode(',', trim($s, ','))));

            Session::put('admin', [
                'account' => obj2arr($account),
            ]);

            return Response::json([
                'redirect_url' => URL::to('admin'),
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function logout()
    {
        Session::remove('admin');

        return Response::redirectTo('admin/login');
    }

    // 设置level
    public function level()
    {
        DB::beginTransaction();

        $count = 1700000;

        for ($i = 31; $i <= 60; $i++) {
            DB::table('sys_level')->insert([
                'level'   => $i,
                'exp'     => $count,
                'attack'  => 2,
                'defense' => 2,
                'fame_id' => 3,
            ]);
            $count += 100000;
        }

        DB::commit();

        echo '完成';
    }

    // 插入装备数据
    public function equip()
    {
        $equips = [
            [
                'name' => '青铜剑',
                'content' => [
                    [
                       'type' => 'weapon',
                       'attack' => 15
                    ]
                ],
            ],
            [
                'name' => '青铜头盔',
                'content' => [
                    [
                        'type' => 'helmet',
                        'attack' => 10,
                        'defense' => 10
                    ]
                ],
            ],
            [
                'name' => '青铜甲',
                'content' => [
                    [
                        'type' => 'clothes',
                        'defense' => 15
                    ]
                ],
            ],
            [
                'name' => '青铜耳环',
                'content' => [
                    [
                        'type' => 'earring',
                        'attack' => 10
                    ]
                ],
            ],
            [
                'name' => '青铜项链',
                'content' => [
                    [
                        'type' => 'necklace',
                        'attack' => 10,
                        'defense' => 10
                    ]
                ],
            ],
            [
                'name' => '青铜手镯',
                'content' => [
                    [
                        'type' => 'bracelet',
                        'attack' => 10
                    ]
                ],
            ],
            [
                'name' => '青铜戒指',
                'content' => [
                    [
                        'type' => 'ring',
                        'attack' => 10
                    ]
                ],
            ],
            [
                'name' => '青铜鞋',
                'content' => [
                    [
                        'type' => 'shoes',
                        'attack' => 10,
                        'defense' => 10
                    ]
                ],
            ],
        ];

        foreach ($equips as $equip) {
            DB::table('item')->insert([
                'name'         => $equip['name'],
                'description'  => '新手装备',
                'type'         => 10,
                'level'        => 5,
                'content'      => json_encode($equip['content']),
                'recycle_coin' => 5,
            ]);
        }

        dd('完成：' . date('Y-m-d H:i:s', time()));
    }
}
