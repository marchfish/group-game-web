<?php

namespace App\Http\Controllers\Api;

use App\Models\UserPK;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserPKController extends Controller
{
    // 邀请pk
    public function invite()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'role_qq' => ['required'],
            ], [
                'role_qq.required' => '请邀请pk',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'ur.*',
                ])
                ->from('user_role AS ur')
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 'ur.user_id')
                    ;
                })
                ->where('u.qq', '=', $query['role_qq'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到该角色!', 400);
            }

            $userPK = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pk AS up')
                ->whereIn('up.a_user_role_id', [$user_role_id, $row->id])
                ->get()
                ->first()
            ;

            $userPK1 = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pk AS up')
                ->whereIn('up.b_user_role_id', [$user_role_id, $row->id])
                ->get()
                ->first()
            ;

            if ($userPK && $userPK->a_user_role_id == $user_role_id || $userPK1 && $userPK1->b_user_role_id == $user_role_id) {
                throw new InvalidArgumentException('您还有未结束的pk，请结束后再试!', 400);
            }

            if ($userPK && $userPK->a_user_role_id == $row->id || $userPK1 && $userPK1->b_user_role_id == $row->id) {
                throw new InvalidArgumentException('对方还有未结束的pk，请结束后再试!', 400);
            }


            DB::table('user_pk')->insert([
                'a_user_role_id' => $user_role_id,
                'b_user_role_id' => $row->id,
                'content'        => '',
            ]);

            return Response::json([
                'code'    => 200,
                'message' => 'pk邀请成功发出，等待对方接受pk!',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 接受邀请
    public function accept()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPK = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pk AS up')
                ->where('up.b_user_role_id', '=' , $user_role_id)
                ->get()
                ->first()
            ;

            if (!$userPK) {
                throw new InvalidArgumentException('并没有人邀请您pk！', 400);
            }

            if ($userPK->status == 150) {
                throw new InvalidArgumentException('您已接受了邀请！', 400);
            }

            $roleA = DB::query()
                ->select([
                    'ur.*',
                ])
                ->from('user_role AS ur')
                ->where('ur.id', '=' , $userPK->a_user_role_id)
                ->get()
                ->first()
            ;

            $roleA->hp  = $roleA->max_hp;
            $roleA->mp  = $roleA->max_mp;
            $roleA->num = 0;

            $roleB = DB::query()
                ->select([
                    'ur.*',
                ])
                ->from('user_role AS ur')
                ->where('ur.id', '=' , $user_role_id)
                ->get()
                ->first()
            ;

            $roleB->hp = $roleB->max_hp;
            $roleB->mp = $roleB->max_mp;
            $roleB->num = 0;

            $bool = is_success(50);

            $data = [
                $roleA, $roleB,
            ];

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->update([
                    'content' => json_encode($data),
                    'handle_user_role_id' => $bool ? $user_role_id : $userPK->a_user_role_id,
                    'wait_user_role_id'   => $bool ? $userPK->a_user_role_id : $user_role_id,
                    'expired_at'          => date('Y-m-d H:i:s', strtotime('+2 minutes')),
                    'status'  => 150,
                ])
            ;

            $name = $bool ? $roleB->name : $roleA->name;

            return Response::json([
                'code'    => 200,
                'message' => '您已接受邀请，请 [' . $name . ']' . ' 先手！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 拒绝邀请
    public function refuse()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPK = DB::query()
                ->select([
                    'up.*',
                    'ur.name AS role_name',
                ])
                ->from('user_pk AS up')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'up.a_user_role_id')
                    ;
                })
                ->where('up.b_user_role_id', '=' , $user_role_id)
                ->get()
                ->first()
            ;

            if (!$userPK) {
                throw new InvalidArgumentException('并没有人邀请您pk！', 400);
            }

            if ($userPK->status != 0) {
                throw new InvalidArgumentException('您已接受了邀请！可认输', 400);
            }

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->delete()
            ;

            return Response::json([
                'code'    => 200,
                'message' => '您拒绝了 [' . $userPK->role_name . ']' . ' 的pk邀请！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 认输
    public function surrender()
    {
        try {
            $query = Request::all();

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPK = DB::query()
                ->select([
                    'up.*',
                    'ur.name AS roleA_name',
                    'ur1.name AS roleB_name',
                ])
                ->from('user_pk AS up')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'up.a_user_role_id')
                    ;
                })
                ->join('user_role AS ur1', function ($join) {
                    $join
                        ->on('ur1.id', '=', 'up.b_user_role_id')
                    ;
                })
                ->where('up.handle_user_role_id', '=' , $user_role_id)
                ->orWhere('up.wait_user_role_id', '=' , $user_role_id)
                ->get()
                ->first()
            ;

            if (!$userPK) {
                throw new InvalidArgumentException('并没有人邀请您pk！', 400);
            }

            if ($userPK->status == 0) {
                throw new InvalidArgumentException('您还没有接受邀请，可拒绝pk', 400);
            }

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->delete()
            ;

            return Response::json([
                'code'    => 200,
                'message' => '您认输了， [' . ($userPK->a_user_role_id == $user_role_id ? $userPK->roleB_name : $userPK->roleA_name) . ']' . ' 获得胜利！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function pk()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'key' => ['required', 'integer', 'min:0', 'max:8'],
            ], [
                'key.required' => '请输入您的操作',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $userPK = DB::query()
                ->select([
                    'up.*',
                ])
                ->from('user_pk AS up')
                ->where('up.handle_user_role_id', '=' , $user_role_id)
                ->orWhere('up.wait_user_role_id', '=' , $user_role_id)
                ->get()
                ->first()
            ;

            if (!$userPK) {
                throw new InvalidArgumentException('您当前并未有pk！请先邀请pk或接受pk', 400);
            }

            $now_at = date('Y-m-d H:i:s',time());

            $roles = json_decode($userPK->content);

            if ($userPK->handle_user_role_id == $user_role_id) {
                $res = UserPK::pk($userPK, $roles, $query['key'], $user_role_id);
            }else{
                $expired_time = time_difference($now_at, $userPK->expired_at, 'second');

                if ($expired_time > 0) {
                    throw new InvalidArgumentException('请等待对方操作，剩余：' . $expired_time . 's', 400);
                }

                $res = UserPK::pk($userPK, $roles, $query['key'], $user_role_id);
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
}
