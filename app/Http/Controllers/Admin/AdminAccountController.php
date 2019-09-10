<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAccount;
use App\Models\AdminRole;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminAccountController extends Controller
{
    public function index()
    {
        $accounts = DB::table('admin_account')
            ->select([
                '*',
            ])
            ->where('status', '=', 200)
            ->orderBy('id', 'asc')
            ->paginate(10)
        ;

        foreach ($accounts  as $account) {
            $account->roles = AdminRole::getByAccountIds($account->id);
        }

        return Response::view('admin/admin-account/index', [
            'accounts' => $accounts,
        ]);
    }

    public function news()
    {
        $roles = DB::table('admin_role')
            ->select([
                '*',
            ])
            ->orderBy('id', 'asc')
            ->get();

        return Response::view('admin/admin-account/new', [
            'roles' => $roles,
        ]);
    }

    public function edit()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'id' => ['required', 'integer'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $account = DB::table('admin_account')
                ->select([
                    '*',
                ])
                ->where('id', '=', $query['id'])
                ->where('status', '=', 200)
                ->get()
                ->first();

            if (!$account) {
                throw new InvalidArgumentException('用户不存在', 400);
            }

            $account->roles = array_column(obj2arr(AdminRole::getByAccountIds($account->id)), 'name', 'id');

            $roles = DB::table('admin_role')
                ->select([
                    '*',
                ])
                ->orderBy('id', 'asc')
                ->get();

            return Response::view('admin/admin-account/edit', [
                'account' => $account,
                'roles'   => $roles,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::redirectTo('admin/admin_account');
        }
    }

    public function create()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'username'   => ['required'],
                'password'   => ['required'],
                'repassword' => ['required'],
                'nickname'   => ['required'],
                'roles'      => ['nullable', 'array'],
            ], [
                'username.required'   => '用户名不能为空',
                'password.required'   => '密码不能为空',
                'repassword.required' => '重复密码不能为空',
                'nickname.required'   => '昵称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if ($query['password'] != $query['repassword']) {
                throw new InvalidArgumentException('两次密码输入不同', 400);
            }

            $account = DB::table('admin_account')
                ->select([
                    '*',
                ])
                ->where('username', '=', $query['username'])
                ->get()
                ->first();

            if ($account) {
                throw new InvalidArgumentException('该用户名已存在', 400);
            }

            DB::beginTransaction();

            $account = AdminAccount::createByQuery($query);

            if (isset($query['roles'])) {
                AdminAccount::updateRole($account->id, $query['roles']);
            }

            DB::commit();

            return Response::json([
                'message' => '保存成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (QueryException $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function update()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'id'         => ['required', 'integer'],
                'password'   => ['nullable'],
                'repassword' => ['nullable'],
                'nickname'   => ['nullable'],
                'roles'      => ['nullable', 'array'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            if ($query['password'] != $query['repassword']) {
                throw new InvalidArgumentException('两次密码输入不同', 400);
            }

            DB::beginTransaction();

            AdminAccount::updateByQuery($query);

            AdminAccount::updateRole($query['id'], $query['roles'] ?? []);

            DB::commit();

            return Response::json([
                'message' => '保存成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (QueryException $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function delete()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'id' => ['required', 'integer'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            AdminAccount::deleteById($query['id']);

            return Response::json([
                'message' => '删除成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
