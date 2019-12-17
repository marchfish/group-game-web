<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserVipController extends Controller
{
    // 显示会员信息
    public function vipShow()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row || time() > strtotime($row->expired_at)) {
                $res = '您还不是会员\r\n';
            }else {
                $res  = '会员到期时间为：' . $row->expired_at . '\r\n';
                $res .= '会员等级为：' . $row->level . '\r\n';
                $res .= '血量保护为：' . $row->protect_hp . '\r\n';
                $res .= '成功率提升：' . $row->success_rate . '\r\n';
            }

            $res .= '会员价格：50000金币一个月\r\n';
            $res .= '输入：购买会员';

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 挂机
    public function onHook()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'action' => ['required'],
            ], [
                'action.required' => 'action不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if ($row->on_hook_at != '1991-01-01 00:00:00') {
                throw new InvalidArgumentException('您已处于挂机中！挂机时间：' . $row->on_hook_at, 400);
            }

            DB::table('user_vip')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'on_hook_at'   => date('Y-m-d H:i:s', time()),
                    'on_hook_type' => $query['action'] == '2' ? 2 : 1,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '挂机成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 结束挂机
    public function endHook()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row || $row->on_hook_at == '1991-01-01 00:00:00') {
                throw new InvalidArgumentException('您并未处于挂机中！', 400);
            }

            $time = time();

            if(strtotime($row->expired_at) < $time){
                $time = strtotime($row->expired_at);
            }

            $num = $time - strtotime($row->on_hook_at);

            $num = (int)round($num / 60);

            $res = '您共挂机：' . $num . '分钟' . '\r\n';

            $exp = 0;
            $coin = 0;

            switch ($row->on_hook_type) {
                case 1 :
                    $exp = $num * 10;
                    $res .= '获得经验：' . $exp;
                    break;
                case 2 :
                    $coin = $num * 5;
                    $res .= '获得金币：' . $coin;
                    break;
            }

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'exp' => DB::raw('`exp` + ' . $exp),
                    'coin' => DB::raw('`coin` + ' . $coin),
                ])
            ;

            DB::table('user_vip')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'on_hook_at' => '1991-01-01 00:00:00',
                ])
            ;

            DB::commit();

            $level = UserRole::isUpgradeToQQ($user_role_id);

            if ($level != 0) {
                $res .= '\r\n恭喜您！等级提升为：' . $level;
            }

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 购买会员
    public function vipBuy()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userRole = $user_role;

            if ($userRole->coin < 50000) {
                throw new InvalidArgumentException('您的金币不足：50000', 400);
            }

            $row = DB::query()
                ->select([
                    'uv.*',
                ])
                ->from('user_vip AS uv')
                ->where('uv.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            DB::beginTransaction();

            if (!$row) {
                $expired_at = date('Y-m-d H:i:s', strtotime('+31 day'));

                DB::table('user_vip')
                    ->insert([
                        'user_role_id' => $user_role_id,
                        'level'        => 1,
                        'protect_hp'   => 60,
                        'success_rate' => 20,
                        'buy_count'    => 1,
                        'on_hook_type' => 1,
                        'on_hook_at'   => '1991-01-01 00:00:00',
                        'expired_at'   => $expired_at,
                    ])
                ;
            }else {

                $expired_at = date('Y-m-d H:i:s', strtotime('+31 day', strtotime($row->expired_at)));

                if(time() > strtotime($row->expired_at)){
                    $expired_at = date('Y-m-d H:i:s', strtotime('+31 day'));
                }

                DB::table('user_vip')
                    ->where('user_role_id', '=', $user_role_id)
                    ->update([
                        'buy_count'  => DB::raw('`buy_count` + ' . 1),
                        'expired_at' => $expired_at,
                    ])
                ;
            }

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . 50000),
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '购买成功，您的会员有效期至：' . $expired_at,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 设置保护
    public function setProtectHp()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'hp' => ['required', 'integer'],
            ], [
                'hp.required' => '保护不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            if ($query['hp'] > ($user_role->max_hp * 0.6)) {
                throw new InvalidArgumentException('保护不能大于血量上限的60%', 400);
            }

            DB::table('user_vip')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'protect_hp' => intval($query['hp']),
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '设置成功，当前保护为：' . intval($query['hp']),
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
