<?php

namespace App\Http\Controllers\Web;

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
            $user_role_id = Session::get('user.account.user_role_id');

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
                $res = '您还不是会员' . '<br>';
            }else {
                $res  = '会员到期时间为：' . $row->expired_at . '<br>';
                $res .= '会员等级为：' . $row->level . '<br>';
                $res .= '血量保护为：' . $row->protect_hp;
                $res .='<div>'
                    .'<input class="minus" name="" type="button" value="-" />'
                    .'<input style="width: 50px" onkeyup="value=value.replace(/[^\d]/g,\'\')" class="js-num" name="goodnum" type="tel" value="60"/>'
                    .'<input class="add" name="" type="button" value="+" />'
                    . '<input type="button" class="action" data-url="' . URL::to('vip/protect') . '?id=' . 1 . '" value="设置" />'
                    .'</div>'
                ;
                $res .= '成功率提升：' . $row->success_rate . '<br>';
            }

            $res .= '会员价格：50000金币一个月' . '<br>';
            $res .= '<input type="button" class="action" data-url="' . URL::to('vip-buy') . '" value="购买会员" />';

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

            $user_role_id = Session::get('user.account.user_role_id');

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
                    'on_hook_type' => $query['action'] == '挂机经验' ? 1 : 2,
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
            $user_role_id = Session::get('user.account.user_role_id');

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

            $res = '您共挂机：' . $num . '分钟' . '<br>';

            $exp = 0;
            $coin = 0;

            switch ($row->on_hook_type) {
                case 1 :
                    $exp = $num * 10;
                    $res .= '获得经验：' . $exp . '<br>';
                    break;
                case 2 :
                    $coin = $num * 5;
                    $res .= '获得金币：' . $coin . '<br>';
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

            $level = UserRole::is_upgrade();

            if ($level != 0) {
                $res .= '恭喜您！等级提升为：' . $level;
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
            $user_role_id = Session::get('user.account.user_role_id');

            $userRole = UserRole::getUserRole();

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
                'var_data' => ['required', 'integer'],
            ], [
                'var_data.required' => '保护不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $user_role = UserRole::getUserRole();

            if ($query['var_data'] > ($user_role->max_hp * 0.6)) {
                throw new InvalidArgumentException('保护不能大于血量上限的60%', 400);
            }

            DB::table('user_vip')
                ->where('user_role_id', '=', $user_role_id)
                ->update([
                    'protect_hp' => intval($query['var_data']),
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '设置成功，当前保护为：' . $query['var_data'],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
