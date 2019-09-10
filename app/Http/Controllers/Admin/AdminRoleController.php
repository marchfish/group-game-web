<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = DB::table('admin_role')
            ->select([
                '*',
            ])
            ->orderBy('id', 'asc')
            ->paginate(10);

        return Response::view('admin/admin-role/index', [
            'roles' => $roles,
        ]);
    }

    public function news()
    {
        $permissions = AdminPermission::getTree();

        return Response::view('admin/admin-role/new', [
            'permissions' => $permissions,
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

            $role = DB::table('admin_role')
                ->select([
                    '*',
                ])
                ->where('id', '=', $query['id'])
                ->get()
                ->first();

            if (!$role) {
                throw new InvalidArgumentException('角色不存在', 400);
            }

            $role->permissions = array_column(obj2arr(AdminPermission::getByRoleId($role->id)), 'name', 'id');

            $permissions = AdminPermission::getTree();

            return Response::view('admin/admin-role/edit', [
                'role'        => $role,
                'permissions' => $permissions,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::redirectTo('admin/role');
        }
    }

    public function create()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'name'        => ['required'],
                'permissions' => ['nullable', 'array'],
            ], [
                'name.required' => '角色名不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $role = DB::table('admin_role')
                ->select([
                    '*',
                ])
                ->where(['name' => $query['name']])
                ->get()
                ->first();

            if ($role) {
                throw new InvalidArgumentException('该角色名已存在', 400);
            }

            DB::beginTransaction();

            $role = AdminRole::createByQuery($query);

            if (isset($query['permissions'])) {
                AdminRole::updatePermission($role->id, $query['permissions']);
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
                'id'          => ['required', 'integer'],
                'name'        => ['required'],
                'permissions' => ['nullable', 'array'],
            ], [
                'name.required' => '角色名不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            DB::beginTransaction();

            AdminRole::updateByQuery($query);

            AdminRole::updatePermission($query['id'], $query['permissions'] ?? []);

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

            DB::beginTransaction();

            AdminRole::deleteById($query['id']);

            DB::commit();

            return Response::json([
                'message' => '删除成功',
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
}
