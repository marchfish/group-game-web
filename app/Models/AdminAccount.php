<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class AdminAccount
{
    public static function getByName(string $username)
    {
        $adminRow = DB::table('admin_account')
           ->select([
               '*',
           ])
           ->where('username', '=', $username)
           ->where('status', '=', 200)
           ->take(1)
           ->get()
           ->first()
       ;

        return $adminRow;
    }

    //创建
    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'username' => $query['username'],
            'password' => Hash::make($query['password']),
            'nickname' => $query['nickname'],
            'status'   => 200,
        ], function ($value) {
            return isset($value);
        });

        // 创建用户
        $id = DB::table('admin_account')->insertGetId($data);

        $adminRow = DB::query()
            ->select(['*'])
            ->from('admin_account')
            ->where('id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $adminRow;
    }

    //权限修改
    public static function updateRole(int $account_id, array $role_ids): void
    {
        DB::table('admin_account_role')
            ->where('account_id', '=', $account_id)
            ->delete();

        $data = [];

        foreach ($role_ids as $role_id) {
            $data[] = [
                'account_id' => $account_id,
                'role_id'    => $role_id,
            ];
        }

        DB::table('admin_account_role')->insert($data);
    }

    //账户信息修改
    public static function updateByQuery(array $query): void
    {
        $account = [
            'nickname' => $query['nickname'],
        ];

        if (isset($query['password'])) {
            $account['password'] = Hash::make($query['password']);
        }

        DB::table('admin_account')
            ->where('id', '=', $query['id'])
            ->update($account);
    }

    //删除账户
    public static function deleteById(int $id): void
    {
        DB::table('admin_account')
            ->where('id', '=', $id)
            ->update(['status' => 0]);
    }
}
