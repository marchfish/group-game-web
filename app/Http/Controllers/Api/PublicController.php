<?php

namespace App\Http\Controllers\Api;

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
    /**
     *注册用户
     */
    public function registerCreate()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'nickname' => ['required'],
                'qq'       => ['required'],
            ], [
                'nickname.required' => '请用正确的昵称',
                'qq.required'       => 'qq不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $app_token = env('APP_TOKEN');

            if (!isset($query['token']) || $query['token'] != $app_token) {
                throw new InvalidArgumentException('验证失败', 400);
            }

            if (mb_strlen($query['nickname'],'utf8') > 10) {
                throw new InvalidArgumentException('游戏名字不能大于10个字', 400);
            }

            $row = DB::query()
                ->select(['id'])
                ->from('user')
                ->where('qq', '=', $query['qq'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row) {
                throw new InvalidArgumentException('该qq已注册', 400);
            }

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
                ->where('username', '=', $query['qq'])
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row2) {
                throw new InvalidArgumentException('该账号已存在', 400);
            }

            $row3 = DB::query()
                ->select(['id'])
                ->from('user')
                ->where('email', '=', $query['qq'] . '@qq.com')
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($row3) {
                throw new InvalidArgumentException('该qq邮箱已注册', 400);
            }

            $data = [
                'username'  => $query['qq'] ,
                'password'  => Hash::make(123456),
                'nickname'  => $query['nickname'],
                'qq'        => $query['qq'],
                'email'     => $query['qq'] . '@qq.com'
            ];

            DB::beginTransaction();

            $id = DB::table('user')->insertGetId($data);

            $user_role_id = DB::table('user_role')->insertGetId([
                'user_id' => $id,
                'name' => $query['nickname'] ?? ''
            ]);

            DB::table('user_equip')->insert([
                'user_role_id' => $user_role_id,
            ]);

            DB::table('user_vip')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'level'        => 1,
                    'protect_hp'   => 60,
                    'success_rate' => 20,
                    'buy_count'    => 1,
                    'on_hook_type' => 1,
                    'on_hook_at'   => '1991-01-01 00:00:00',
                    'expired_at'   => date('Y-m-d H:i:s', strtotime('+31 day')),
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '注册成功，开始冒险吧！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
