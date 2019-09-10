<?php

namespace App\Models;

use App\Events\PPUserCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class Group
{
    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'user_id'          => $query['user_id'],
            'no'               => self::geneNo(),
            'name'             => $query['name'],
            'logo'             => $query['logo'] ?? null,
            'area'             => $query['area'] ?? null,
            'certificate_code' => $query['certificate_code'] ?? null,
            'address'          => $query['address'] ?? null,
            'description'      => $query['description'] ?? null,
            'status'           => $query['status'] ?? null,
        ], function ($value) {
            return isset($value);
        });

        // 创建公司
        $id = DB::table('group')->insertGetId($data);

        DB::table('group_attach')->insert([
            'group_id' => $id,
            'g_pic1'   => $query['g_pic1'] ?? '',
        ]);

        DB::table('group_user')->insert([
            'group_id' => $id,
            'user_id'  => $query['user_id'],
            'role_id'  => 1,
            'status'   => 200,
        ]);

        $groupRow = DB::query()
            ->select(['*'])
            ->from('group')
            ->where('id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $groupRow;
    }

    private static function geneNo()
    {
        do {
            $no = mt_rand(100000, 199999);
        } while (DB::query()->select(['id'])->from('group')->where('no', '=', $no)->get()->first());

        return $no;
    }

    public static function statusToStr($status)
    {
        switch ($status) {
            case 0:
                $status = '禁用';

                break;
            case 10:
                $status = '解散';

                break;
            case 50:
                $status = '认证失败';

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
