<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserController extends Controller
{
    // 列表
    public function index()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id'   => ['nullable', 'integer'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select([
                    'u.*',
                ])
                ->from('user AS u')
            ;

            if (isset($query['nickname'])) {
                $model->where('u.nickname', 'like', '%' . $query['nickname'] . '%');
            }

            if (isset($query['tel'])) {
                $model->where('u.tel', '=', $query['tel']);
            }

            $paginate = $model->paginate($query['size']);

            foreach ($paginate->items() as $item) {
                $item->avatar  = upload_url($item->avatar);
                $item->status1 = User::statusToStr($item->status);
            }

            return Response::view('admin/user/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 禁用
    public function disabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的用户
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('user')
                ->where('id', '=', $query['user_id'])
                ->where('status', '=', 200)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于认证通过的用户', 400);
            }

            // 更改用户状态
            DB::table('user')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 0,
                ])
            ;

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

    // 解禁
    public function removeDisabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有存在的用户
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('user')
                ->where('id', '=', $query['user_id'])
                ->where('status', '=', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被禁用的用户', 400);
            }

            // 更改用户状态
            DB::table('user')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

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
}
