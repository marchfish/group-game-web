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
}
