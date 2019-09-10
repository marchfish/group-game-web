<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class AdminPermission
{
    public static function getTree(): array
    {
        $permissions = DB::table('admin_permission')
            ->orderBy('id', 'asc')
            ->get()
        ;

        $permissions = obj2arr($permissions);

        $tree = [];

        foreach ($permissions as $permission) {
            if ($permission['parent_id'] == 0) {
                $tree[$permission['id']] = $permission;
            } else {
                $tree[$permission['parent_id']]['children'][] = $permission;
            }
        }

        return $tree;
    }

    public static function all(): Collection
    {
        $permission = DB::query()
            ->select([
                'ap.id',
                'ap.name',
                'ap.controller',
            ])
            ->from('admin_permission AS ap')
            ->orderBy('ap.id', 'asc')
            ->get()
        ;

        return $permission;
    }

    public static function getByAccountId(int $account_id): Collection
    {
        $permission = DB::query()
            ->select([
                'ap.id',
                'ap.name',
                'ap.controller',
            ])
            ->from('admin_permission AS ap')
            ->join('admin_role_permission AS arp', 'ap.id', '=', 'arp.permission_id')
            ->join('admin_role AS ar', 'ar.id', '=', 'arp.role_id')
            ->join('admin_account_role AS aar', 'ar.id', '=', 'aar.role_id')
            ->where('aar.account_id', '=', $account_id)
            ->orderBy('ap.id', 'asc')
            ->get()
            ;

        return $permission;
    }

    public static function getByRoleId(int $role_id): Collection
    {
        $roleRow = DB::query()
            ->select([
                'ap.id',
                'ap.name',
                'ap.controller',
            ])
            ->from('admin_permission AS ap')
            ->join('admin_role_permission AS arp', 'ap.id', '=', 'arp.permission_id')
            ->where('arp.role_id', '=', $role_id)
            ->orderBy('ap.id', 'asc')
            ->get()
            ;

        return $roleRow;
    }
}
