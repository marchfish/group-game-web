<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class AdminRole
{
    public static function getByAccountIds(int $account_id): Collection
    {
        $roleRow = DB::query()
                ->select([
                    'r.id',
                    'r.name',
                ])
                ->from('admin_role AS r')
                ->join('admin_account_role AS ar', 'r.id', '=', 'ar.role_id')
                ->where('ar.account_id', '=', $account_id)
                ->orderBy('r.id', 'asc')
                ->get()
            ;

        return $roleRow;
    }

    //创建
    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'name' => $query['name'],
        ], function ($value) {
            return isset($value);
        });

        // 创建用户
        $id = DB::table('admin_role')->insertGetId($data);


        $adminRow = DB::query()
            ->select(['*'])
            ->from('admin_role')
            ->where('id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $adminRow;
    }

    public static function updateByQuery(array $query): void
    {
        $role = [
            'name' => $query['name'],
        ];

        DB::table('admin_role')
            ->where('id', '=', $query['id'])
            ->update($role);
    }

    public static function updatePermission(int $role_id, array $permission_ids): void
    {
        DB::table('admin_role_permission')
            ->where('role_id', '=', $role_id)
            ->delete();

        $data = [];

        foreach ($permission_ids as $permission_id) {
            $data[] = [
                'role_id'       => $role_id,
                'permission_id' => $permission_id,
            ];
        }

        DB::table('admin_role_permission')->insert($data);
    }

    public static function deleteById(int $id): void
    {
        DB::table('admin_role')->where('id', '=', $id)->delete();

        DB::table('admin_account_role')->where('role_id', '=', $id)->delete();

        DB::table('admin_role_permission')->where('role_id', '=', $id)->delete();
    }
}
