<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserVipController extends Controller
{
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

    //
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

            if ($row->on_hook_at == '1991-01-01 00:00:00') {
                throw new InvalidArgumentException('您并未处于挂机中！', 400);
            }

//            $num = strtotime('2019-02-01 10:00:00') - strtotime('2019-02-01 9:50:00');
//
//            dd($num / 60);

//            DB::table('user_vip')
//                ->where('user_role_id', '=', $user_role_id)
//                ->update([
//                    'on_hook_at'   => date('Y-m-d H:i:s', time()),
//                    'on_hook_type' => $query['action'] == '挂机经验' ? 1 : 2,
//                ])
//            ;

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
}
