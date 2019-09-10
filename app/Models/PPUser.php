<?php

namespace App\Models;

use App\Events\PPUserCreated;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PPUser
{
    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'user_id'     => $query['user_id'],
            'tel'         => $query['tel'],
            'realname'    => $query['realname'] ?? null,
            'area'        => $query['area'] ?? null,
            'near'        => $query['near'] ?? null,
            'idcard'      => $query['idcard'] ?? null,
            'gender'      => $query['gender'] ?? null,
            'dl'          => $query['dl'] ?? null,
            'contact_man' => $query['contact_man'] ?? null,
            'contact_tel' => $query['contact_tel'] ?? null,
            'intro'       => $query['intro'] ?? null,
            'avatar'      => $query['avatar'] ?? null,
            'is_quick'    => $query['is_quick'] ?? 1,
            'status'      => $query['status'] ?? null,
        ], function ($value) {
            return isset($value);
        });

        DB::table('pp_user')->insert($data);

        $ppUserRow = DB::query()
            ->select(['*'])
            ->from('pp_user')
            ->where('tel', '=', $query['tel'])
            ->limit(1)
            ->get()
            ->first()
        ;

        Event::dispatch(new PPUserCreated($ppUserRow));

        return $ppUserRow;
    }

    public static function updateLocation(array $query, bool $is_force = false)
    {
        $row = DB::query()
            ->select([
                'lon',
                'lat',
            ])
            ->from('pp_location')
            ->where('user_id', '=', $query['user_id'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get()
            ->first()
        ;

        $distance = 0;

        if (!$row || (($distance = get_distance($row->lon, $row->lat, $query['lon'], $query['lat'])) > 999) || $is_force) {
            // 跑跑经纬度记录
            DB::table('pp_location')->insert([
                'user_id'  => $query['user_id'],
                'lon'      => $query['lon'],
                'lat'      => $query['lat'],
                'distance' => $distance,
            ]);
        }

        // 更新最新经纬度信息
        DB::table('user_stat')
            ->where('user_id', '=', $query['user_id'])
            ->update([
                'pp_lon'                 => $query['lon'],
                'pp_lat'                 => $query['lat'],
                'pp_label'               => $query['label'],
                'pp_location_updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ])
        ;
    }

    public static function getIdsByQuery($query)
    {
        if (isset($query['group_id']) && $query['group_id']) {
            $model = DB::query()
                ->select([
                    'pu.user_id',
                ])
                ->from('pp_user AS pu')
                ->join('group_user AS gu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->where('pu.status', '=', 200)
            ;

            if (isset($query['area'])) {
                $model->where('pu.area', '=', $query['area']);
            }

            if (isset($query['is_work'])) {
                $model->where('pu.is_work', '=', $query['is_work']);
            }
        } else {
            $model = DB::query()
                ->select([
                    'user_id',
                ])
                ->from('pp_user')
                ->where('status', '=', 200)
            ;

            if (isset($query['area'])) {
                $model->where('area', '=', $query['area']);
            }

            if (isset($query['is_work'])) {
                $model->where('is_work', '=', $query['is_work']);
            }
        }

        return array_column(obj2arr($model->get()), 'user_id');
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
