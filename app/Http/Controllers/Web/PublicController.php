<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use App\Support\Facades\Captcha;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use App\Support\Facades\Email;


class PublicController extends Controller
{
    //        dd(Session::get('user.account'));
    /**
     *发送验证码
     */
    public function sendVerifyCode()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'email' => ['required'],
            ], [
                'email.required' => '邮箱不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $code=rand(100001,999999);

            $data=[
                'address' => $query['email'],
                'content' => '您的验证码为：'.$code.'请尽快使用！',
                'title'   => '验证码',
                'name'    => '开荒之路',
                'user'    => '用户',
            ];

            DB::table('sys_email_code')
                ->updateOrInsert([
                    'email' => $query['email'],
                ],
                    [
                        'email'      => $query['email'],
                        'code'       => $code,
                        'expired_at' => date('Y-m-d H:i:s', strtotime('+1 hours')),
                    ]
                );

            Email::sendEmail($data);

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

    /**
     *显示登录
     */
    public function loginShow()
    {

        if (Session::has('user.account')) {
            return Response::redirectTo('index');
        } else {
            return Response::view('web/public/login');
        }
    }

    /**
     *验证码设置
     */
    public function captcha()
    {
        Captcha::build(100, 34);

        Session::put('user.captcha', Captcha::getPhrase());

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

            if (strtolower($query['captcha']) != Session::get('user.captcha')) {
                throw new InvalidArgumentException('验证码错误', 400);
            }

            $account = User::getByName($query['username']);

            if (!$account || !Hash::check($query['password'], $account->password)) {
                throw new InvalidArgumentException('用户名或密码错误', 400);
            }

            Session::put('user', [
                'account' => obj2arr($account),
            ]);

            $user_role = UserRole::getUserRole();

            Session::put('userRole', obj2arr($user_role));

            return Response::json([
                'redirect_url' => URL::to('index'),
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

    /**
     *显示注册
     */
    public function registerShow()
    {
        return Response::view('web/public/register');
    }

    /**
     *注册用户
     */
    public function registerCreate()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'username'    => ['required'],
                'password'    => ['required'],
                'password1'   => ['required'],
                'nickname'    => ['required'],
                'email'       => ['required'],
                'verify_code' => ['required'],
                'gender'      => ['required'],
            ], [
                'username.required'    => '账号不能为空',
                'password.required'    => '密码不能为空',
                'password1.required'   => '确认密码不能为空',
                'nickname.required'    => '昵称不能为空',
                'email.required'       => '邮箱不能为空',
                'verify_code.required' => '验证码不能为空',
                'gender.required'      => '性别不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select(['id'])
                ->from('user')
                ->where('email', '=', $query['email'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row) {
                throw new InvalidArgumentException('该邮箱已注册', 400);
            }

            if ($query['password'] != $query['password1']) {
                throw new InvalidArgumentException('两次密码不一致', 400);
            }

            $query['password'] = Hash::make($query['password']);

            Email::checkVerifyCode($query['email'], $query['verify_code']);

            $row1 = DB::query()
                ->select(['id'])
                ->from('user_role')
                ->where('name', '=', $query['nickname'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row1) {
                throw new InvalidArgumentException('该昵称已存在', 400);
            }

            $row2 = DB::query()
                ->select(['id'])
                ->from('user')
                ->where('username', '=', $query['username'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row2) {
                throw new InvalidArgumentException('该账号已存在', 400);
            }

            $userRow = User::createByQuery($query);

            Session::put('user', [
                'account' => obj2arr($userRow),
            ]);
            
            return Response::json([
                'redirect_url' => URL::to('index'),
                'code'    => 200,
                'message' => '注册成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *退出登录
     */
    public function logout()
    {
        Session::remove('user');

        Session::remove('userRole');

        return Response::redirectTo('');
    }
}
