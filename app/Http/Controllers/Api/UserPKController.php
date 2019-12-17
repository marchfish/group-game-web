<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserPKController extends Controller
{
    // 邀请pk
    public function invitePK()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'role_name' => ['required'],
            ], [
                'role_name.required' => '要pk的昵称不能为空',
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
                ->where('ur.name', '=', $query['role_name'])
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
    public function acceptPK()
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

            $roleA->hp = $roleA->max_hp;
            $roleA->mp = $roleA->max_mp;

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

            $bool = is_success(50);

            $data = [
                'roleA' => $roleA,
                'roleB' => $roleB,
            ];

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->update([
                    'content' => json_encode($data),
                    'handle_user_role_id' => $bool ? $user_role_id : $userPK->a_user_role_id,
                    'wait_user_role_id'   => $bool ? $userPK->a_user_role_id : $user_role_id,
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

            $roleA->hp = $roleA->max_hp;
            $roleA->mp = $roleA->max_mp;

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

            $bool = is_success(50);

            $data = [
                'roleA' => $roleA,
                'roleB' => $roleB,
            ];

            DB::table('user_pk')
                ->where('id', '=', $userPK->id)
                ->update([
                    'content' => json_encode($data),
                    'handle_user_role_id' => $bool ? $user_role_id : $userPK->a_user_role_id,
                    'wait_user_role_id'   => $bool ? $userPK->a_user_role_id : $user_role_id,
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
}
