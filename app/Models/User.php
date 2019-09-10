<?php

namespace App\Models;

use App\Events\UserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;

class User
{
    public static function getByName(string $username)
    {
        $userRow = DB::table('user')
            ->select([
                '*',
            ])
            ->where('username', '=', $username)
            ->where('status', '=', 200)
            ->take(1)
            ->get()
            ->first()
        ;

        return $userRow;
    }

    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'username'  => $query['username'] ?? null,
            'password'  => $query['password'] ?? null,
            'nickname'  => $query['nickname'] ?? null,
            'email'     => $query['email'] ?? null,
            'gender'    => $query['gender'] ?? null,
        ], function ($value) {
            return isset($value);
        });

        DB::beginTransaction();

        $id = DB::table('user')->insertGetId($data);

        DB::table('user_role')->insert([
            'user_id' => $id,
            'name' => $query['nickname'] ?? ''
        ]);

        DB::commit();

        $userRow = DB::query()
            ->select(['*'])
            ->from('user')
            ->where('id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $userRow;
    }

    public static function statusToStr($status)
    {
        switch ($status) {
            case 0:
                $status = '禁用';

                break;
            case 50:
                $status = '认证失败';

                break;
            case 100:
                $status = '未认证';

                break;
            case 150:
                $status = '认证中';

                break;
            case 200:
                $status = '认证通过';

                break;
            default:
                $status = '未知状态';

                break;
        }

        return $status;
    }
}
