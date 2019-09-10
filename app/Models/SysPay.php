<?php

namespace App\Models;

use App\Support\Facades\Push;
use App\Support\Facades\Message;
use App\Support\Facades\Alipay;
use App\Support\Facades\Wxpay;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SysPay
{
    public static function createByQuery(array $query)
    {
        $data = [
            'user_id' => $query['user_id'],
            'use'     => $query['use'],
            'payway'  => $query['payway'],
            'pay_fee' => $query['pay_fee'],
            'params'  => isset($query['params']) ? json_encode($query['params'], JSON_UNESCAPED_UNICODE) : '',
            'no'      => self::geneNo(),
        ];

        $id = DB::table('sys_pay')->insertGetId($data);

        $row = DB::query()
            ->select([
                'sp.*',
                'd.value AS use_name',
            ])
            ->from('sys_pay AS sp')
            ->leftJoin('dict AS d', function ($join) {
                $join
                    ->on('sp.use', '=', 'd.key')
                    ->where('d.col', '=', 'sys_pay.use')
                ;
            })
            ->where('sp.id', '=', $id)
            ->limit(1)
            ->get()
            ->first()
        ;

        return $row;
    }

    public static function finishOrderBid(array $query)
    {
        $params = json_decode($query['params'], true);

        $row = DB::query()
            ->select([
                'pb.*',
                'o.group_id',
                'o.zpp_user_id',
                'o.is_quick',
                'od.area',
                'u.nickname',
                'u.tel AS zpp_tel',
                'pu.tel',
                'pu.realname',
            ])
            ->from('pp_bid AS pb')
            ->join('order AS o', function ($join) {
                $join
                    ->on('o.id', '=', 'pb.order_id')
                ;
            })
            ->join('order_detail AS od', function ($join) {
                $join
                    ->on('od.order_id', '=', 'o.id')
                ;
            })
            ->join('user AS u', function ($join) {
                $join
                    ->on('u.id', '=', 'o.zpp_user_id')
                ;
            })
            ->join('pp_user AS pu', function ($join) {
                $join
                    ->on('pu.user_id', '=', 'pb.user_id')
                ;
            })
            ->where('pb.id', '=', $params['bid_id'])
            ->whereIn('pb.status', [10, 150])
            ->where('o.status', '=', 50)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row) {
            throw new InvalidArgumentException('该竞价不存在', 400);
        }

        $nowAt = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('order')
            ->where('id', '=', $row->order_id)
            ->update([
                'pay_id'     => $query['id'],
                'pp_user_id' => $row->user_id,
                'payway'     => $query['payway'],
                'pay_fee'    => $query['pay_fee'],
                'status'     => 100,
                'payed_at'   => $nowAt,
                'taked_at'   => $nowAt,
            ])
        ;

        DB::table('order_detail')
            ->where('order_id', '=', $row->order_id)
            ->update([
                'bid_message'    => $row->message,
                'pp_taked_lon'   => $row->lon,
                'pp_taked_lat'   => $row->lat,
                'pp_taked_label' => $row->label,
                'timelimit'      => $row->timelimit,
            ])
        ;

        DB::table('pp_bid')
            ->where('id', '=', $params['bid_id'])
            ->update([
                'status' => 200,
            ])
        ;

        DB::table('order_history')->insert([
            'order_id' => $row->order_id,
            'user_id'  => $row->zpp_user_id,
            'title'    => '竞价下单成功',
            'content'  => ($row->is_quick ? '闪送加急，' : '') . '费用：' . rmb($query['pay_fee']) . '元，跑跑：' . $row->realname . '，电话：' . $row->tel,
        ]);

        Push::notify([
            'client'   => 'pp',
            'user_ids' => [$row->user_id],
            'title'    => '竞价成功',
            'content'  => '竞价成功',
            'extra'    => [
                'type'     => 'bid-success',
                'order_id' => $row->order_id,
                'zpp_tel'  => $row->zpp_tel,
            ],
        ]);

        $bidRows = DB::query()
            ->select([
                'user_id',
            ])
            ->from('pp_bid')
            ->where('order_id', '=', $row->order_id)
            ->where('user_id', '<>', $row->user_id)
            ->where('status', '=', 150)
            ->get()
        ;

        $bidUserIds = array_column(obj2arr($bidRows), 'user_id');

        if (!empty($bidUserIds)) {
            DB::table('pp_bid')
                ->where('order_id', '=', $row->order_id)
                ->whereIn('user_id', $bidUserIds)
                ->update([
                    'status' => 50,
                ])
            ;

            Push::notify([
                'client'   => 'pp',
                'user_ids' => $bidUserIds,
                'title'    => '竞价失败',
                'content'  => '竞价失败！顾客『' . $row->nickname . '』的订单被其他人抢走了：最终价' . rmb($query['pay_fee']) . '元',
                'extra'    => [
                    'type' => 'bid-fail',
                ],
            ]);
        }

        Push::message([
            'client'   => 'pp',
            'user_ids' => PPUser::getIdsByQuery([
                'group_id' => $row->group_id,
                'area'     => $row->area,
                'is_work'  => 1,
            ]),
            'title'   => '听单刷新',
            'content' => '听单刷新',
            'extra'   => [
                'type'     => 'order-wait-refresh',
                'order_id' => $row->order_id,
            ],
        ]);
    }

    public static function finishOrderDirect(array $query)
    {
        $params = json_decode($query['params'], true);

        $row = DB::query()
            ->select([
                'o.id',
                'o.group_id',
                'o.zpp_user_id',
                'o.is_quick',
                'od.area',
                'od.price',
            ])
            ->from('order AS o')
            ->join('order_detail AS od', function ($join) {
                $join
                    ->on('o.id', '=', 'od.order_id')
                ;
            })
            ->where('o.id', '=', $params['order_id'])
            ->where('o.status', '=', 40)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row) {
            throw new InvalidArgumentException('该订单不存在', 400);
        }

        $nowAt = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('order')
            ->where('id', '=', $params['order_id'])
            ->update([
                'pay_id'   => $query['id'],
                'payway'   => $query['payway'],
                'pay_fee'  => $query['pay_fee'],
                'status'   => 60,
                'payed_at' => $nowAt,
            ])
        ;

        DB::table('order_history')->insert([
            'order_id' => $params['order_id'],
            'user_id'  => $row->zpp_user_id,
            'title'    => '直接下单成功',
            'content'  => ($row->is_quick ? '闪送加急，' : '') . '费用：' . rmb($query['pay_fee']) . '元',
        ]);

        Push::notify([
            'client'   => 'pp',
            'user_ids' => PPUser::getIdsByQuery([
                'group_id' => $row->group_id,
                'area'     => $row->area,
                'is_work'  => 1,
            ]),
            'title'   => '找跑跑新订单',
            'content' => '公司客户来订单了，请尽快处理',
            'extra'   => [
                'type'     => 'order-new',
                'order_id' => $row->id,
            ],
        ]);
    }

    public static function finishDepositIn(array $query)
    {
        DB::table('user_stat')
            ->where('user_id', '=', $query['user_id'])
            ->update([
                'deposit' => DB::raw('`deposit` + ' . $query['pay_fee']),
            ])
        ;

        DB::table('user_deposit_in')->insert([
            'pay_id'  => $query['id'],
            'user_id' => $query['user_id'],
            'amount'  => $query['pay_fee'],
        ]);
    }

    public static function finishTicket(array $query)
    {
        $params = json_decode($query['params'], true);

        $now = Carbon::now();

        switch ($params['type']) {
        case 'day':
            $now->addDays(1);

            break;
        case 'week':
            $now->addDays(7);

            break;
        case 'month':
            $now->addDays(30);

            break;
        default:
            throw new InvalidArgumentException('无效的 params.type', 400);
        }

        DB::table('pp_ticket')->insert([
            'pay_id'     => $query['id'],
            'user_id'    => $query['user_id'],
            'pay'        => $query['pay_fee'],
            'text'       => $params['text'],
            'expired_at' => $now->format('Y-m-d H:i:s'),
        ]);
    }

    public static function refundOrder(array $query)
    {
        $orderRow = DB::table('order')
            ->select([
                'o.id',
                'o.zpp_user_id',
                'o.payway',
                'o.pay_fee',
                'o.pay_id',
                'sp.no',
                'sp.refund_no',
            ])
            ->from('order AS o')
            ->leftJoin('sys_pay AS sp', function ($join) {
                $join
                    ->on('o.pay_id', '=', 'sp.id');
            })
            ->where('o.id', '=', $query['order_id'])
            ->whereIn('o.status', [35, 60, 80, 100])
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$orderRow) {
            throw new InvalidArgumentException('该订单不存在', 400);
        }

        $nowAt = Carbon::now()->format('Y-m-d H:i:s');

        switch ($orderRow->payway) {
        case 1:
            throw new InvalidArgumentException('支付宝暂不支持退款', 400);

            break;
        case 2:
            if ($orderRow->no) {
                $refund_no = $orderRow->refund_no ?: self::geneRefundNo();

                DB::table('sys_pay')
                    ->where('id', '=', $orderRow->pay_id)
                    ->update([
                        'refund_no' => $refund_no,
                        'status'    => 30,
                    ])
                ;

                DB::table('order')
                    ->where('id', '=', $query['order_id'])
                    ->update([
                        'status' => 30,
                    ])
                ;

                Wxpay::refund('zpp', [
                    'out_trade_no'  => $orderRow->no,
                    'out_refund_no' => $refund_no,
                    'total_fee'     => $orderRow->pay_fee,
                    'refund_fee'    => $orderRow->pay_fee,
                ]);
            }

            break;
        case 3:
            DB::table('user_deposit_out')
                ->where('order_id', '=', $query['order_id'])
                ->update([
                    'status' => 100,
                ])
            ;

            DB::table('user_stat')
                ->where('user_id', '=', $orderRow->zpp_user_id)
                ->update([
                    'deposit' => DB::raw('`deposit` + ' . $orderRow->pay_fee),
                ])
            ;

            DB::table('order')
                ->where('id', '=', $query['order_id'])
                ->update([
                    'status'      => 20,
                    'refunded_at' => $nowAt,
                ])
            ;

            DB::table('order_history')->insert([
                'order_id' => $query['order_id'],
                'user_id'  => 0,
                'title'    => '退款成功',
                'content'  => '订单关闭，钱包退款金额：' . rmb($orderRow->pay_fee) . '元',
            ]);

            Message::send([
                'client'   => 'zpp',
                'user_ids' => [$orderRow->zpp_user_id],
                'title'    => '退款成功',
                'content'  => '订单退款成功，钱包退款金额：' . rmb($orderRow->pay_fee) . '元',
                'extra'    => [
                    'type'     => 'order-refund-success',
                    'order_id' => $orderRow->id,
                ],
            ]);

            break;
        default:
            throw new InvalidArgumentException('无效的支付方式', 400);
        }
    }

    public static function finishRefundOrder(array $query)
    {
        $params = json_decode($query['params'], true);

        $row = DB::query()
            ->select([
                'id',
                'zpp_user_id',
            ])
            ->from('order')
            ->where('id', '=', $params['order_id'])
            ->where('status', '=', 30)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row) {
            throw new InvalidArgumentException('该订单不存在', 400);
        }

        $nowAt = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('order')
            ->where('id', '=', $params['order_id'])
            ->update([
                'status'      => 20,
                'refunded_at' => $nowAt,
            ])
        ;

        DB::table('order_history')->insert([
            'order_id' => $row->id,
            'user_id'  => 0,
            'title'    => '退款成功',
            'content'  => '订单关闭，退款金额：' . rmb($query['refund_fee']) . '元',
        ]);

        Message::send([
            'client'   => 'zpp',
            'user_ids' => [$row->zpp_user_id],
            'title'    => '退款成功',
            'content'  => '订单退款成功，退款金额：' . rmb($query['refund_fee']) . '元',
            'extra'    => [
                'type'     => 'order-refund-success',
                'order_id' => $row->id,
            ],
        ]);
    }

    private static function geneNo()
    {
        do {
            $no = gene_no();
        } while (DB::query()->select(['id'])->from('sys_pay')->where('no', '=', $no)->limit(1)->get()->first());

        return $no;
    }

    private static function geneRefundNo()
    {
        do {
            $no = gene_no();
        } while (DB::query()->select(['id'])->from('sys_pay')->where('refund_no', '=', $no)->limit(1)->get()->first());

        return $no;
    }
}
