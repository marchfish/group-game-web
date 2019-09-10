<?php

namespace App\Models;

use App\Support\Facades\Push;
use App\Support\Facades\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Order
{
    public static function createByQuery(array $query)
    {
        $data = array_filter([
            'group_id'    => $query['group_id'],
            'zpp_user_id' => $query['zpp_user_id'],
            'pp_user_id'  => $query['pp_user_id'] ?? null,
            'no'          => self::geneNo(),
            'payway'      => $query['payway'] ?? null,
            'pay_fee'     => $query['pay_fee'] ?? null,
            'share_fee'   => $query['share_fee'] ?? null,
            'is_quick'    => $query['is_quick'] ?? null,
            'is_direct'   => $query['is_direct'] ?? null,
            'is_sandan'   => $query['is_sandan'] ?? null,
            'is_zpp_hide' => $query['is_zpp_hide'] ?? null,
            'is_pp_hide'  => $query['is_pp_hide'] ?? null,
            'status'      => $query['status'],
        ], function ($value) {
            return isset($value);
        });

        $id = DB::table('order')->insertGetId($data);

        $data = array_filter([
            'order_id'                => $id,
            'cancel_user_id'          => $query['cancel_user_id'] ?? null,
            'area'                    => $query['area'],
            'lon'                     => $query['lon'] ?? null,
            'lat'                     => $query['lat'] ?? null,
            'label'                   => $query['label'] ?? null,
            'description'             => $query['description'] ?? null,
            'pic'                     => $query['pic'] ?? null,
            'voice'                   => $query['voice'] ?? null,
            'duration'                => $query['duration'] ?? null,
            'price'                   => (int) ($query['price'] ?? null),
            'timelimit'               => $query['timelimit'] ?? null,
            'first_location_lon'      => $query['first_location_lon'] ?? null,
            'first_location_lat'      => $query['first_location_lat'] ?? null,
            'first_location_label'    => $query['first_location_label'] ?? null,
            'first_location_contact'  => $query['first_location_contact'] ?? null,
            'second_location_lon'     => $query['second_location_lon'] ?? null,
            'second_location_lat'     => $query['second_location_lat'] ?? null,
            'second_location_label'   => $query['second_location_label'] ?? null,
            'second_location_contact' => $query['second_location_contact'] ?? null,
            'pp_taked_lon'            => $query['pp_taked_lon'] ?? null,
            'pp_taked_lat'            => $query['pp_taked_lat'] ?? null,
            'pp_taked_label'          => $query['pp_taked_label'] ?? null,
            'pp_go_lon'               => $query['pp_go_lon'] ?? null,
            'pp_go_lat'               => $query['pp_go_lat'] ?? null,
            'pp_go_label'             => $query['pp_go_label'] ?? null,
            'pp_arrived_lon'          => $query['pp_arrived_lon'] ?? null,
            'pp_arrived_lat'          => $query['pp_arrived_lat'] ?? null,
            'pp_arrived_label'        => $query['pp_arrived_label'] ?? null,
        ], function ($value) {
            return isset($value);
        });

        DB::table('order_detail')->insert($data);

        $orderRow = DB::query()
            ->select([
                'o.*',
                'od.*',
                'o.id AS id',
            ])
            ->from('order AS o')
            ->join('order_detail AS od', function ($join) {
                $join
                    ->on('o.id', '=', 'od.order_id')
                ;
            })
            ->where('o.id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $orderRow;
    }

    public static function finish($query)
    {
        $row = DB::query()
            ->select([
                'o.*',
                'od.*',
                'o.id AS id',
                'zpp_u.parent_user_id AS zpp_parent_user_id',
                'pp_u.parent_user_id AS pp_parent_user_id',
                'us.pp_exp AS pp_exp',
                DB::raw('IFNULL(`gu`.`group_id`, 0) AS `pp_group_id`'),
            ])
            ->from('order AS o')
            ->join('order_detail AS od', function ($join) {
                $join
                    ->on('o.id', '=', 'od.order_id')
                ;
            })
            ->join('user AS zpp_u', function ($join) {
                $join
                    ->on('zpp_u.id', '=', 'o.zpp_user_id')
                ;
            })
            ->join('user AS pp_u', function ($join) {
                $join
                    ->on('pp_u.id', '=', 'o.pp_user_id')
                ;
            })
            ->join('user_stat AS us', function ($join) {
                $join
                    ->on('us.user_id', '=', 'o.pp_user_id')
                ;
            })
            ->leftJoin('group_user AS gu', function ($join) {
                $join
                    ->on('o.pp_user_id', '=', 'gu.user_id')
                    ->where('gu.status', '=', 200)
                ;
            })
            ->where('o.id', '=', $query['order_id'])
            ->where('o.status', '=', 180)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row) {
            throw new InvalidArgumentException('该订单不存在', 400);
        }

        // 订单里程
        $km = get_km($row->pp_user_id, $row->taked_at, $row->arrived_at);
        // 用时
        $use_time = Carbon::parse($row->taked_at)->diffInSeconds($row->arrived_at);
        // 分润
        $rate        = json_decode(SysConfig::get('share_profit_rate'), true);
        $share_fee   = calc_share($row->pay_fee, $rate[0]);
        $zpp_share   = $row->zpp_parent_user_id ? calc_share($row->pay_fee, $rate[1]) : 0; // 有上级才分润
        $pp_share    = $row->pp_parent_user_id ? calc_share($row->pay_fee, $rate[2]) : 0; // 有上级才分润
        $admin_share = $share_fee    - $zpp_share    - $pp_share;
        $real_fee    = $row->pay_fee - $share_fee;

        $orderData = [
            'group_id'    => $row->group_id ?: $row->pp_group_id,
            'share_fee'   => $share_fee,
            'is_overtime' => ($use_time > $row->timelimit) ? 1 : 0,
            'status'      => 200,
            'checked_at'  => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $finishData = [
            'order_id'           => $row->id,
            'zpp_parent_user_id' => $row->zpp_parent_user_id,
            'pp_parent_user_id'  => $row->pp_parent_user_id,
            'zpp_parent_share'   => $zpp_share,
            'pp_parent_share'    => $pp_share,
            'admin_share'        => $admin_share,
            'km'                 => $km,
            'use_time'           => $use_time,
        ];

        $zppData = [
            'user_id'      => $row->zpp_user_id,
            'zpp_pay'      => $row->pay_fee,
            'zpp_savetime' => $use_time,
            'zpp_lb'       => $km, // 1 懒币 = 1 里程
        ];

        $ppData = [
            'user_id' => $row->pp_user_id,
            // 个人钱包
            'deposit'   => $orderData['group_id'] ? 0 : $real_fee,
            'pp_exp'    => 1,
            'pp_km'     => $km,
            'pp_salary' => $orderData['group_id'] ? $real_fee : 0,
        ];

        DB::table('order')
            ->where('id', '=', $query['order_id'])
            ->update($orderData)
        ;

        DB::table('order_finish')->insert($finishData);

        DB::table('user_stat')
            ->where('user_id', '=', $zppData['user_id'])
            ->update([
                'zpp_pay'      => DB::raw('`zpp_pay` + ' . $zppData['zpp_pay']),
                'zpp_savetime' => DB::raw('`zpp_savetime` + ' . $zppData['zpp_savetime']),
                'zpp_lb'       => DB::raw('`zpp_lb` + ' . $zppData['zpp_lb']),
            ])
        ;

        DB::table('user_stat')
            ->where('user_id', '=', $ppData['user_id'])
            ->update([
                'deposit'   => DB::raw('`deposit` + ' . $ppData['deposit']),
                'pp_exp'    => DB::raw('`pp_exp` + ' . $ppData['pp_exp']),
                'pp_km'     => DB::raw('`pp_km` + ' . $ppData['pp_km']),
                'pp_salary' => DB::raw('`pp_salary` + ' . $ppData['pp_salary']),
            ])
        ;

        // 自动升级成闪送
        if (($row->pp_exp + $ppData['pp_exp']) > SysConfig::get('quick_min')) {
            DB::table('pp_user')
                ->where('user_id', '=', $ppData['user_id'])
                ->update([
                    'is_quick' => 1,
                ])
            ;
        }

        // 跑跑加入跑团结算给公司钱包
        if ($orderData['group_id']) {
            DB::table('group')
                ->where('id', '=', $orderData['group_id'])
                ->update([
                    'deposit' => DB::raw('`deposit` + ' . $ppData['pp_salary']),
                ])
            ;

            DB::table('group_user')
                ->where('group_id', '=', $orderData['group_id'])
                ->where('user_id', '=', $ppData['user_id'])
                ->update([
                    'salary'    => DB::raw('`salary` + ' . $ppData['pp_salary']),
                    'order_num' => DB::raw('`order_num` + 1'),
                ])
            ;
        // 跑跑未加入跑团结算给个人钱包
        } else {
            DB::table('user_deposit_in')->insert([
                'user_id'  => $ppData['user_id'],
                'order_id' => $query['order_id'],
                'amount'   => $ppData['deposit'],
            ]);
        }

        // 平台分润
        if ($admin_share) {
            DB::table('admin_deposit_in')->insert([
                'order_id' => $query['order_id'],
                'amount'   => $admin_share,
            ]);

            DB::table('admin_stat')
                ->where('id', '=', 1)
                ->update([
                    'deposit' => DB::raw('`deposit` + ' . $admin_share),
                ])
            ;
        }

        // 顾客上级分润
        if ($finishData['zpp_parent_user_id'] && $finishData['zpp_parent_share']) {
            DB::table('user_stat')
                ->where('user_id', '=', $finishData['zpp_parent_user_id'])
                ->update([
                    'deposit' => DB::raw('`deposit` + ' . $finishData['zpp_parent_share']),
                ])
            ;

            DB::table('user_deposit_in')->insert([
                'user_id'  => $finishData['zpp_parent_user_id'],
                'order_id' => $query['order_id'],
                'amount'   => $finishData['zpp_parent_share'],
                'is_share' => 1,
            ]);
        }

        // 骑手上级分润
        if ($finishData['pp_parent_user_id'] && $finishData['pp_parent_share']) {
            DB::table('user_stat')
                ->where('user_id', '=', $finishData['pp_parent_user_id'])
                ->update([
                    'deposit' => DB::raw('`deposit` + ' . $finishData['pp_parent_share']),
                ])
            ;

            DB::table('user_deposit_in')->insert([
                'user_id'  => $finishData['pp_parent_user_id'],
                'order_id' => $query['order_id'],
                'amount'   => $finishData['pp_parent_share'],
                'is_share' => 1,
            ]);
        }

        DB::table('order_history')->insert([
            'order_id' => $query['order_id'],
            'user_id'  => $row->zpp_user_id,
            'title'    => '已确认收货',
            'content'  => '交易完结',
        ]);

        Push::notify([
            'client'   => 'pp',
            'user_ids' => [$row->pp_user_id],
            'title'    => '订单验收',
            'content'  => '顾客已确认收货',
            'extra'    => [
                'type'     => 'order-check',
                'order_id' => (int) $query['order_id'],
            ],
        ]);
    }

    private static function geneNo()
    {
        do {
            $no = gene_no();
        } while (DB::query()->select(['id'])->from('order')->where('no', '=', $no)->get()->first());

        return $no;
    }
}
