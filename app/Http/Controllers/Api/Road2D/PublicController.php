<?php

namespace App\Http\Controllers\Api\Road2D;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Support\Facades\Captcha;
use GatewayClient\Gateway;
use InvalidArgumentException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function index()
    {
//        return redirect('/khzl');
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
                'client_id' => ['required'],
            ], [
                'client_id.required' => 'client_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = Session::get('userRole');

            $uid = $user_role['id'];

            $old_client_id = Gateway::getClientIdByUid($uid);

            if($old_client_id && $old_client_id[0] != $query['client_id']){
                $data = [
                    'type'=>'logout',
                    'message'=>'您的账号在别处登录!'
                ];

                Gateway::closeClient($old_client_id[0], json_encode($data));
            }

            Gateway::bindUid($query['client_id'], $uid);

            $data = [
                'type'=>'login',
                'message'=>$user_role['name'] . '：上线了！'
            ];

            Gateway::sendToAll(json_encode($data), null, [$query['client_id']]);

            return Response::json([
                'code'    => 200,
                'message' => '成功',
                'client_id' => $query['client_id'],
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
//        $uid = Session::get('userRole.user_id');
        $uid = 1;

        $client_id = Gateway::getClientIdByUid($uid);

        if ($client_id) {
            Gateway::closeClient($client_id[0], '退出成功');
        }

        Session::remove('user');

        Session::remove('userRole');

        return Response::json([
            'code'    => 200,
            'message' => '成功',
        ]);
    }

    /**
     *发送信息
     */
    public function sendMessage()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'message' => ['required'],
                'client_id' => ['required'],
            ], [
                'message.required' => 'message不能为空',
                'client_id.required' => 'client_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if(!Gateway::isOnline($query['client_id'])) {
                throw new InvalidArgumentException('您已离线！', 400);
            }

            $role_id   = Session::get('userRole.id');
            $role_name = Session::get('userRole.name');

            $data = [
                'type' => 'message',
                'message' => $role_name . '：' . $query['message']
            ];

            DB::table('message')->insert([
                'role_id' => $role_id,
                'content' => $query['message']
            ]);

            Gateway::sendToAll(json_encode($data));

            return Response::json([
                'code' => 200,
                'message' => '成功',
            ]);

        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
