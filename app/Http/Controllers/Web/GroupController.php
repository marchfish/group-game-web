<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Facades\AppSession;
use App\Support\Facades\Message;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class GroupController extends Controller
{
    public function show($id)
    {
        try {
            //查出公司成员uid
            $row = DB::query()
                ->select([
                    'g.name AS name',
                    'g.logo AS logo',
                    'g.description AS description',
                    'g.created_at AS created_at',
                    'pu.realname AS realname',
                ])
                ->from('group AS g')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'g.user_id')
                    ;
                })
                ->where('g.id', '=', $id)
                ->whereIn('g.status', [0, 200])
                ->get()
                ->first()
            ;

            if (!$row) {
                abort(404);
            }

            $row1 = DB::query()
                ->select([
                    DB::raw('COUNT(`gu`.`id`) AS `count`'),
                ])
                ->from('group_user AS gu')
                ->where('gu.group_id', '=', $id)
                ->where('gu.status', '=', 200)
                ->get()
                ->first()
            ;

            $row->logo  = upload_url($row->logo);
            $row->count = $row1->count;

            return Response::view('web/group/index', [
                'data' => $row,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function stat()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $formatDate = formatDateToWeb($query);

            return Response::view('web/group/stat', [
                'data'       => $formatDate,
                'date_month' => $query['date_month'] ?? '',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function incomeTrend()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['required', 'date_format:Y-m-d'],
                'date_to'   => ['required', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $ts_from = strtotime($query['date_from']);
            $ts_to   = strtotime($query['date_to']);

            $stats = [];

            // 收入 + 单数
            $row = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`pay_fee`), 0) - IFNULL(SUM(`share_fee`), 0) AS SIGNED) AS `pay_fee`'),
                    DB::raw('COUNT(id) AS `count`'),
                ])
                ->from('order AS o')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;
            $stats['pay_fee'] = $row->pay_fee;

            $stats['count'] = $row->count;

            $date_format = '%Y-%m-%d';

            if (isset($query['date_month']) && $query['date_month'] > 0) {
                $date_format = '%Y-%m';
            }

            // 收入折线图数据
            $rows = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`pay_fee`), 0) - IFNULL(SUM(`share_fee`), 0) AS SIGNED) AS `pay_fee`'),
                    DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(`created_at`), "' . $date_format . '") AS `date`'),
                ])
                ->from('order AS o')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->groupBy('date')
                ->get()
            ;

            // 单数折线图数据
            $rows1 = DB::query()
                ->select([
                    DB::raw('COUNT(id) AS `count`'),
                    DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(`created_at`), "' . $date_format . '") AS `date`'),
                ])
                ->from('order AS o')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->groupBy('date')
                ->get()
            ;

            $rows  = array_column($rows->toArray(), 'pay_fee', 'date');
            $rows1 = array_column($rows1->toArray(), 'count', 'date');

            if (isset($query['date_month']) && $query['date_month'] > 0) {
                $temp = date('Y-m', $ts_from);
            } else {
                $temp = date('Y-m-d', $ts_from);
            }

            $result = [
                'xAxis.data'    => [],
                'series.0.data' => [],
                'series.1.data' => [],
            ];

            do {
                $result['xAxis.data'][]    = $temp;
                $result['series.0.data'][] = rmb($rows[$temp] ?? 0);
                $result['series.1.data'][] = $rows1[$temp] ?? 0;

                if (isset($query['date_month']) && $query['date_month'] > 0) {
                    $temp = date('Y-m', strtotime($temp . ' +1 month'));
                } else {
                    $temp = date('Y-m-d', strtotime($temp . ' +1 day'));
                }
            } while (strtotime($temp) <= $ts_to);

            $option = [
                'tooltip' => [
                    'trigger' => 'axis',
                ],
                'color' => ['#E9868B', '#515884'],
                'grid'  => [
                    'top'          => '15%',
                    'left'         => '5%',
                    'right'        => '12%',
                    'bottom'       => '13%',
                    'containLabel' => true,
                ],
                'toolbox' => [
                    'feature' => [
                        'saveAsImage' => [],
                    ],
                ],
                'xAxis' => [
                    'type'        => 'category',
                    'boundaryGap' => false,
                    'axisLabel'   => [
                        'show'      => true,
                        'textStyle' => [
                            'color'      => '#353537',
                            'fontWeight' => 'bold',
                            'fontSize'   => '14',
                        ],
                    ],
                    'axisLine' => [
                        'show' => false,
                    ],
                    'axisTick' => [
                        'show' => false,
                    ],
                    'splitLine' => [
                        'show' => false,
                    ],
                    'data' => $result['xAxis.data'],
                ],
                'yAxis' => [
                    'type' => 'value',
                    'show' => false,
                ],
                'series' => [
                    [
                        'name'       => '收入',
                        'type'       => 'line',
                        'symbol'     => 'circle',
                        'symbolSize' => 8,
                        'data'       => $result['series.0.data'],
                    ],
                    [
                        'name'       => '单数',
                        'type'       => 'line',
                        'symbol'     => 'circle',
                        'symbolSize' => 8,
                        'data'       => $result['series.1.data'],
                    ],
                ],
            ];

            return Response::json([
                'code'  => 200,
                'data'  => $option,
                'stats' => $stats,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function order()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['required', 'date_format:Y-m-d'],
                'date_to'   => ['required', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $stats = [];

            // 公司单
            $row = DB::query()
                ->select([
                    DB::raw('COUNT(id) AS `count`'),
                ])
                ->from('order AS o')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;

            // 散单
            $row1 = DB::query()
                ->select([
                    DB::raw('COUNT(id) AS `count`'),
                ])
                ->from('order AS o')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 1)
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;

            // 总订单数
            $totalOrderCount = $row->count + $row1->count;

            $stats['stat1']  = bfb($row->count, $totalOrderCount, false);
            $stats['stat2']  = bfb($row1->count, $totalOrderCount, false);
            $stats['count1'] = $row->count;
            $stats['count2'] = $row1->count;

            $option1 = [
                'series' => [
                    [
                        'name'              => '访问来源',
                        'type'              => 'pie',
                        'radius'            => ['73%', '100%'],
                        'avoidLabelOverlap' => false,
                        'legend'            => [
                            'selectedMode' => false,
                        ],
                        'label' => [
                            'normal' => [
                                'show'     => false,
                                'position' => 'center',
                            ],
                        ],
                        'color'     => ['#E9868B', '#F1F1F2'],
                        'labelLine' => [
                            'normal' => [
                                'show' => false,
                            ],
                        ],
                        'data' => [
                            [
                                'value' => $row->count,
                                'name'  => '公司客户单',
                            ],
                            [
                                'value' => $row1->count,
                                'name'  => '散单',
                            ],
                        ],
                    ],
                ],
            ];

            $option2 = [
                'series' => [
                    [
                        'name'              => '访问来源',
                        'type'              => 'pie',
                        'radius'            => ['73%', '100%'],
                        'avoidLabelOverlap' => false,
                        'legend'            => [
                            'selectedMode' => false,
                        ],
                        'label' => [
                            'normal' => [
                                'show'     => false,
                                'position' => 'center',
                            ],
                        ],
                        'color'     => ['#E9868B', '#F1F1F2'],
                        'labelLine' => [
                            'normal' => [
                                'show' => false,
                            ],
                        ],
                        'data' => [
                            [
                                'value' => $row1->count,
                                'name'  => '散单',
                            ],
                            [
                                'value' => $row->count,
                                'name'  => '公司客户单',
                            ],
                        ],
                    ],
                ],
            ];

            // 超时订单数
            $stat3 = DB::query()
                ->select([
                    DB::raw('COUNT(id) AS `count`'),
                ])
                ->from('order AS o')
                ->where('is_overtime', '=', 1)
                ->where('group_id', '=', $query['group_id'])
                ->where('status', '=', 200)
                ->where('created_at', '>=', $query['date_from'])
                ->where('created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;

            $stats['stat3'] = bfb($stat3->count, $row->count, false);

            $option3 = [
                'series' => [
                    [
                        'name'              => '访问来源',
                        'type'              => 'pie',
                        'radius'            => ['73%', '100%'],
                        'avoidLabelOverlap' => false,
                        'legend'            => [
                            'selectedMode' => false,
                        ],
                        'label' => [
                            'normal' => [
                                'show'     => false,
                                'position' => 'center',
                            ],
                        ],
                        'color'     => ['#E9868B', '#F1F1F2'],
                        'labelLine' => [
                            'normal' => [
                                'show' => false,
                            ],
                        ],
                        'data' => [
                            [
                                'value' => $stat3->count,
                                'name'  => '超时率',
                            ],
                            [
                                'value' => $row->count,
                                'name'  => '总数',
                            ],
                        ],
                    ],
                ],
            ];

            // 跑单王 单数 跑费
                $stat4 = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`o`.`pay_fee`), 0) - IFNULL(SUM(`o`.`share_fee`), 0) AS SIGNED) AS `pay_fee`'),
                    'o.pp_user_id AS pp_user_id',
                    'pu.realname AS realname',
                ])
                ->from('order AS o')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'o.pp_user_id')
                    ;
                })
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 200)
                ->where('o.created_at', '>=', $query['date_from'])
                ->where('o.created_at', '<', $query['date_to'])
                ->groupBy('o.pp_user_id')
                ->orderBy('pay_fee', 'desc')
                ->limit(1)
                ->get()
                ->first()
            ;

            if ($stat4) {
                $user_count = DB::query()
                    ->select([
                        DB::raw('COUNT(`o`.`id`) AS `count`'),
                    ])
                    ->from('order AS o')
                    ->where('o.pp_user_id', '=', $stat4->pp_user_id)
                    ->where('o.is_sandan', '=', 0)
                    ->where('o.status', '=', 200)
                    ->where('o.created_at', '>=', $query['date_from'])
                    ->where('o.created_at', '<', $query['date_to'])
                    ->limit(1)
                    ->get()
                    ->first()
                ;
            }

//            $stat4 = DB::query()
//                ->select([
//                    DB::raw('CAST(IFNULL(SUM(`o`.`pay_fee`), 0) - IFNULL(SUM(`o`.`share_fee`), 0) AS SIGNED) AS `pay_fee`'),
//                    DB::raw('COUNT(`o`.`id`) AS `count`'),
//                    'o.pp_user_id AS pp_user_id',
//                    'pu.realname AS realname',
//                ])
//                ->from('order AS o')
//                ->join('pp_user AS pu', function ($join) {
//                    $join
//                        ->on('pu.user_id', '=', 'o.pp_user_id')
//                    ;
//                })
//                ->where('o.group_id', '=', $query['group_id'])
//                ->where('o.is_sandan', '=', 0)
//                ->where('o.status', '=', 200)
//                ->where('o.created_at', '>=', $query['date_from'])
//                ->where('o.created_at', '<', $query['date_to'])
//                ->groupBy('o.pp_user_id')
//                ->orderBy('pay_fee', 'desc')
//                ->limit(1)
//                ->get()
//                ->first()
//            ;

            if (!$stat4) {
                $stats['stat4'] = [
                    'realname' => '- -',
                    'pay_fee'  => '- -',
                    'count'    => '- -',
                ];
            } else {
                $stats['stat4'] = [
                    'realname' => $stat4->realname,
                    'pay_fee'  => $stat4->pay_fee,
                    'count'    => $user_count->count ?? '- -',
                ];
            }

            return Response::json([
                'code' => 200,
                'data' => [
                    'option1' => $option1,
                    'option2' => $option2,
                    'option3' => $option3,
                ],
                'stats' => $stats,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function order1()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['required', 'date_format:Y-m-d'],
                'date_to'   => ['required', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $stats = [];

            // 当前配送数
            $row1 = DB::query()
                ->select([
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 150)
                ->where('o.created_at', '>=', $query['date_from'])
                ->where('o.created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;

            $stats['delivery'] = $row1->count;

            // 待验收单数
            $row2 = DB::query()
                ->select([
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 180)
                ->where('o.created_at', '>=', $query['date_from'])
                ->where('o.created_at', '<', $query['date_to'])
                ->get()
                ->first()
            ;

            $stats['check'] = $row2->count;

            // 上班中人数
            $row3 = DB::query()
                ->select([
                    DB::raw('COUNT(`pu`.`id`) AS `count`'),
                ])
                ->from('group_user AS gu')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->where('pu.is_work', '=', 1)
                ->first()
            ;

            $stats['work'] = $row3->count;

            return Response::json([
                'code' => 200,
                'data' => $stats,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function order2()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['required', 'date_format:Y-m-d'],
                'date_to'   => ['required', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 公司内所有成员
            $stats = DB::query()
                ->select([
                    'gu.user_id AS user_id',
                    'pu.realname AS realname',
                    'pu.tel AS tel',
                ])
                ->from('group_user AS gu')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->orderBy('pu.created_at', 'asc')
                ->get()
            ;

            $user_ids = array_column(obj2arr($stats), 'user_id');

            // 跑团内总单数
            $total = DB::query()
                ->select([
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 200)
                ->get()
                ->first()
            ;

            // 中单数
            $stat2 = DB::query()
                ->select([
                    'user_id',
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('pp_bid')
                ->whereIn('user_id', $user_ids)
                ->where('group_id', '=', $query['group_id'])
                ->where('status', '=', 200)
                ->groupBy('user_id')
                ->get()
            ;

            $stat2 = array_column($stat2->toArray(), 'count', 'user_id');

            foreach ($stats as $stat) {
                $stat->win = isset($stat2[$stat->user_id]) ? bfb($stat2[$stat->user_id], $total->count) : 0;
            }

            // 背单率
            $stat3 = DB::query()
                ->select([
                    'pp_user_id',
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('order')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->whereIn('status', [100, 150])
                ->groupBy('pp_user_id')
                ->get()
            ;

            $stat3 = array_column($stat3->toArray(), 'count', 'pp_user_id');

            foreach ($stats as $stat) {
                $stat->back = isset($stat3[$stat->user_id]) ? bfb($stat3[$stat->user_id], $total->count) : 0;
            }

            // 总跑费 + 总订单
            $stat4 = DB::query()
                ->select([
                    'pp_user_id',
                    DB::raw('SUM(IFNULL(`pay_fee`, 0)) AS `total_pay_fee`'),
                    DB::raw('COUNT(`id`) AS `order_count`'),
                ])
                ->from('order')
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->where('status', '=', 200)
                ->groupBy('pp_user_id')
                ->get()
            ;

            $stat4 = array_column($stat4->toArray(), null, 'pp_user_id');

            foreach ($stats as $stat) {
                $stat->total_pay_fee = isset($stat4[$stat->user_id]->total_pay_fee) ? rmb($stat4[$stat->user_id]->total_pay_fee) : 0;
                $stat->order_count   = $stat4[$stat->user_id]->order_count ?? 0;
            }

            // 最新位置 时间 (经纬度) + 里程数
            $stat5 = DB::query()
                ->select([
                    'user_id',
                    'pp_lon',
                    'pp_lat',
                    'pp_label',
                    'pp_location_updated_at',
                    'pp_km',
                ])
                ->from('user_stat')
                ->whereIn('user_id', $user_ids)
                ->get()
            ;

            $stat5 = array_column($stat5->toArray(), null, 'user_id');

            foreach ($stats as $stat) {
                if (isset($stat5[$stat->user_id])) {
                    $stat->lon           = $stat5[$stat->user_id]->pp_lon;
                    $stat->lat           = $stat5[$stat->user_id]->pp_lat;
                    $stat->address       = $stat5[$stat->user_id]->pp_label;
                    $stat->position_time = $stat5[$stat->user_id]->pp_location_updated_at;
                    $stat->km            = $stat5[$stat->user_id]->pp_km;
                } else {
                    $stat->lon           = 0;
                    $stat->lat           = 0;
                    $stat->address       = '';
                    $stat->position_time = 0;
                    $stat->km            = 0;
                }
            }

            // 上次工作时长 (循环查询)
            foreach ($stats as $stat) {
                $stat6 = DB::query()
                    ->select([
                        'start_at',
                        'end_at',
                        'duration',
                    ])
                    ->from('pp_work')
                    ->where('user_id', '=', $stat->user_id)
                    ->orderBy('end_at', 'desc')
                    ->limit(1)
                    ->get()
                    ->first()
                ;

                $stat->duration = isset($stat6) ? beauty_date($stat6->end_at) : '0 分钟';
                // 上次听单时间
                $stat->worktime = isset($stat6) ? $stat6->start_at : 0;
            }

            // 上次获单时间 + 上次验收时间
            $stat7 = DB::query()
                ->select([
                    DB::raw('MAX(`created_at`) AS `order_at`'), //获单
                    DB::raw('MAX(`checked_at`) AS `check_at`'), //验收
                    'pp_user_id',
                ])
                ->from('order')
                ->whereIn('pp_user_id', $user_ids)
                ->where('group_id', '=', $query['group_id'])
                ->groupBy('pp_user_id')
                ->get()
            ;

            $stat7 = array_column($stat7->toArray(), null, 'pp_user_id');

            foreach ($stats as $stat) {
                $stat->order_at = $stat7[$stat->user_id]->order_at ?? 0;
                $stat->check_at = $stat7[$stat->user_id]->check_at ?? 0;
            }

            // 上次竞价时间 + 参与接单数
            $stat8 = DB::query()
                ->select([
                    'user_id',
                    DB::raw('MAX(`created_at`) AS `actor_at`'),
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('pp_bid')
                ->whereIn('user_id', $user_ids)
                ->where('group_id', '=', $query['group_id'])
                ->groupBy('user_id')
                ->get()
            ;

            $stat8 = array_column($stat8->toArray(), null, 'user_id');

            foreach ($stats as $stat) {
                $stat->actor_at = $stat8[$stat->user_id]->actor_at ?? 0;
                // 参与率
                $stat->participate = isset($stat8[$stat->user_id]->count) ? bfb($stat8[$stat->user_id]->count, $total->count) : 0;
            }

            // 工作总时长
            $stat9 = DB::query()
                ->select([
                    'user_id',
                    DB::raw('CAST(IFNULL(SUM(`duration`), 0) AS SIGNED) AS `duration`'),
                ])
                ->from('pp_work')
                ->whereIn('user_id', $user_ids)
                ->groupBy('user_id')
                ->get()
            ;

            $stat9 = array_column($stat9->toArray(), 'duration', 'user_id');

            foreach ($stats as $stat) {
                $stat->total_duration = isset($stat9[$stat->user_id]) ? secToTime1($stat9[$stat->user_id]) : 0;
            }

            return Response::json([
                'code'  => 200,
                'stats' => $stats,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => 400,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function settlement()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'g.name AS name',
                    DB::raw('CAST(IFNULL(SUM(`gu`.`salary`), 0) AS SIGNED) AS `salary`'),
                ])
                ->from('group_user AS gu')
                ->join('group AS g', function ($join) {
                    $join
                        ->on('g.id', '=', 'gu.group_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->groupBy('gu.group_id')
                ->get()
                ->first()
            ;

            $rows = DB::query()
                ->select([
                    'gu.user_id AS user_id',
                    'gu.salary AS salary',
                    'pu.realname AS realname',
                    'pu.avatar AS avatar',
                ])
                ->from('group_user AS gu')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->get()
            ;

            foreach ($rows as $row1) {
                $row1->avatar = upload_url($row1->avatar);
            }

            return Response::view('web/group/settlement/index', [
                'data' => [
                    'name'   => $row->name ?? '- -',
                    'salary' => $row->salary,
                    'rows'   => $rows,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function settlementCreate()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 公司信息
            $group = DB::query()
                ->select([
                    'name',
                ])
                ->from('group')
                ->where('id', '=', $query['group_id'])
                ->get()
                ->first()
            ;

            // 检查账目是否有需要结算
            $row = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`gu`.`salary`), 0) AS SIGNED) AS `salary`'),
                ])
                ->from('group_user AS gu')
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->get()
                ->first()
            ;

            if ($row && $row->salary == 0) {
                throw new InvalidArgumentException('没有成员需要结算', 400);
            }

            //TODO 验证资金？

            $rows1 = DB::query()
                ->select([
                    'user_id',
                    'salary',
                ])
                ->from('group_user AS gu')
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('gu.status', '=', 200)
                ->get()
            ;

            $data = [];

            $user_ids = [];

            $count = 0;

            $date = date('Y-m-d H:i:s', time());

            foreach ($rows1 as $v) {
                if ($v->salary <= 0) {
                    continue;
                }
                $user_ids[] = $v->user_id;

                $data[$count]['group_id']   = $query['group_id'];
                $data[$count]['user_id']    = $v->user_id;
                $data[$count]['salary']     = $v->salary;
                $data[$count]['created_at'] = $date;

                ++$count;
            }

            if (empty($data)) {
                throw new InvalidArgumentException('暂无需要结算的成员', 400);
            }

            DB::beginTransaction();

            DB::table('group_user_salary_history')
                ->insert($data)
            ;

            DB::table('group_user')
                ->whereIn('user_id', $user_ids)
                ->update([
                    'payed_salary' => DB::raw('`payed_salary` + `salary`'),
                    'salary'       => 0,
                ])
            ;

            DB::commit();

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => $user_ids,
                'title'    => '发工资',
                'content'  => '平台已经将工资结算给公司，请联系公司领取，如果有拖欠工资情况，请联系平台举报，联系电话：4000852400，公司唯一官网：www.zhaopaopao.net',
                'extra'    => [
                    'type'     => 'group-salary-out',
                    'group_id' => $query['group_id'],
                ],
            ]);

            return Response::json([
                'code'    => 200,
                'message' => '操作成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function settlementHistory()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $rows = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`gush`.`salary`), 0) AS SIGNED) AS `salary`'),
                    DB::raw('COUNT(`gush`.`user_id`) AS `count`'),
                    'gush.created_at AS created_at',
                ])
                ->from('group_user_salary_history AS gush')
                ->where('gush.group_id', '=', $query['group_id'])
                ->orderBy('gush.created_at', 'desc')
                ->groupBy('gush.created_at')
                ->get()
            ;

            return Response::view('web/group/settlement/history', [
                'data' => [
                    'rows' => $rows,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function settlementDetailed()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['required', 'date_format:Y-m-d H:i:s'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $rows = DB::query()
                ->select([
                    'gush.salary AS salary',
                    'gush.user_id AS user_id',
                    'gush.created_at AS created_at',
                    'pu.realname AS realname',
                    'pu.avatar AS avatar',
                ])
                ->from('group_user_salary_history AS gush')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gush.user_id')
                    ;
                })
                ->where('gush.group_id', '=', $query['group_id'])
                ->where('gush.created_at', '=', $query['date_from'])
                ->get()
            ;

            foreach ($rows as $row) {
                $row->avatar = upload_url($row->avatar);
            }

            return Response::view('web/group/settlement/detailed', [
                'data' => [
                    'total_salary' => $query['salary'] ?? 0,
                    'count'        => $query['count'] ?? 0,
                    'date_from'    => $query['date_from'] ?? '- -',
                    'rows'         => $rows,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function withdraw()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_id = Session::get('user.id');

            $row = DB::query()
                ->select([
                    'g.name AS name',
                    'g.deposit AS deposit', // 可提现
                    'g.withdrawed_deposit AS withdrawed_deposit', // 总提现
                    DB::raw('IFNULL(uba.bank_account_name, "") AS bank_account_name'),
                    DB::raw('IFNULL(uba.bank_card_no, "") AS bank_card_no'),
                    DB::raw('IFNULL(uba.bank_address, "") AS bank_address'),

                ])
                ->from('group AS g')
                ->leftJoin('user_bank_account AS uba', function ($join) {
                    $join
                        ->on('uba.user_id', '=', 'g.user_id')
                    ;
                })
                ->where('g.id', '=', $query['group_id'])
                ->where('g.user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            //上次提现
            $row1 = DB::query()
                ->select([
                    'amount',
                ])
                ->from('group_deposit_withdraw AS gdw')
                ->where('group_id', '=', $query['group_id'])
                ->whereIn('status', [150, 200])
                ->orderBy('created_at', 'desc')
                ->get()
                ->first()
            ;

            // 获取配置信息
            $row2 = DB::query()
                ->select([
                    'value',
                ])
                ->from('sys_config AS sc')
                ->where('key', '=', 'cash_min1')
                ->get()
                ->first()
            ;

            // 不可提现金额
            $row3 = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`gu`.`salary`), 0) AS `salary`'),
                ])
                ->from('group_user AS gu')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('pu.status', '=', 0)
                ->get()
                ->first()
            ;

            return Response::view('web/group/withdraw/index', [
                'data' => [
                    'name'               => $row->name ?? '- -',
                    'deposit'            => $row->deposit,
                    'withdrawed_deposit' => $row->withdrawed_deposit,
                    'deposit_last'       => $row1->amount ?? 0,
                    'cash_min'           => $row2->value, //提现门槛
                    'salary'             => $row3->salary, //不能提现的余额
                    'bank_account_name'  => $row->bank_account_name,
                    'bank_card_no'       => $row->bank_card_no,
                    'bank_address'       => $row->bank_address,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function withdrawCreate()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_id = Session::get('user.id');

            $row = DB::query()
                ->select([
                    'bank_account_name',
                    'bank_card_no',
                    'bank_address',
                ])
                ->from('user_bank_account AS uba')
                ->where('user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('未绑定银行卡', 400);
            }

            // 获取公司信息
            $group = DB::query()
                ->select([
                    'status',
                ])
                ->from('group AS g')
                ->where('id', '=', $query['group_id'])
                ->get()
                ->first()
            ;

            if ($group->status == 0) {
                throw new InvalidArgumentException('被禁用的公司无法提现', 400);
            }

            // 获取配置信息
            $sys_config = DB::query()
                ->select([
                    'value',
                ])
                ->from('sys_config AS sc')
                ->where('key', '=', 'cash_min1')
                ->get()
                ->first()
            ;

            $row1 = DB::query()
                ->select([
                    'deposit',
                    'withdrawing_deposit',
                    'withdrawed_deposit',
                    'frozen_deposit',
                    'created_at',
                ])
                ->from('group AS g')
                ->where('id', '=', $query['group_id'])
                ->where('status', '=', 200)
                ->where('deposit', '>=', $sys_config->value)
                ->where('user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            if (!$row1) {
                throw new InvalidArgumentException('没有可用余额', 400);
            }

            // TODO 资金验证
            $row2 = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`o`.`pay_fee`), 0) - IFNULL(SUM(`o`.`share_fee`), 0) AS SIGNED) AS `pay_fee`'),
                ])
                ->from('order AS o')
                ->where('status', '=', 200)
                ->where('group_id', '=', $query['group_id'])
                ->where('is_sandan', '=', 0)
                ->get()
                ->first()
            ;

            if ($row1->deposit + $row1->withdrawing_deposit + $row1->withdrawed_deposit + $row1->frozen_deposit != $row2->pay_fee) {
                throw new InvalidArgumentException('可用余额异常，请联系管理员处理', 400);
            }

            $row3 = DB::query()
                ->select([
                    'area',
                    'realname',
                    'tel',
                ])
                ->from('pp_user AS pu')
                ->where('user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            if (!$row3) {
                throw new InvalidArgumentException('没有该用户', 400);
            }

            // 不可提现金额
            $row4 = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`gu`.`salary`), 0) AS `salary`'),
                ])
                ->from('group_user AS gu')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gu.user_id')
                    ;
                })
                ->where('gu.group_id', '=', $query['group_id'])
                ->where('pu.status', '=', 0)
                ->get()
                ->first()
            ;

            $money = $row1->deposit - ($row4->salary ?? 0);

            if ($money < $sys_config->value) {
                throw new InvalidArgumentException('可提现余额未达到提现要求', 400);
            }

            DB::beginTransaction();

            DB::table('group_deposit_withdraw')
                ->insert([
                    'bank_account_name' => $row->bank_account_name,
                    'bank_card_no'      => $row->bank_card_no,
                    'bank_address'      => $row->bank_address,
                    'group_id'          => $query['group_id'],
                    'user_id'           => $user_id,
                    'amount'            => $money,
                    'created_at'        => date('Y-m-d H:i:s', time()),
                ])
            ;

            DB::table('group')
                ->where('id', '=', $query['group_id'])
                ->update([
                    'deposit'             => DB::raw('`deposit` - ' . $money),
                    'withdrawing_deposit' => DB::raw('`withdrawing_deposit` + ' . $money),
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '操作成功, 请等待处理',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function withdrawHistory()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => '公司id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $rows = DB::query()
                ->select([
                    'amount',
                    'created_at',
                ])
                ->from('group_deposit_withdraw AS gdw')
                ->where('group_id', '=', $query['group_id'])
                ->where('status', '=', 200)
                ->orderBy('created_at', 'desc')
                ->get()
            ;

            return Response::view('web/group/withdraw/history', [
                'data' => $rows,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function account()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $formatDate = formatDateToWeb($query);
            $date_from  = $formatDate['date_from'];
            $date_to    = $formatDate['date_to'];

            $stat = [
                'date_show' => $formatDate['date_show'],
                'count'     => 0,
            ];

            // 公司客户单
            $row = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`o`.`pay_fee`), 0) AS `pay_fee`'), //总金额
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 200)
                ->get()
                ->first()
            ;

            $stat['group_pay_fee'] = $row->pay_fee;
            $stat['count'] += $row->count;

            // 普通用户单
            $row1 = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`o`.`pay_fee`), 0) AS `pay_fee`'), //总金额
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 1)
                ->where('o.status', '=', 200)
                ->get()
                ->first()
            ;

            $stat['ordinary_pay_fee'] = $row1->pay_fee;
            $stat['count'] += $row1->count;

            // 流水明细
            $rows = DB::query()
                ->select([
                    'o.id AS id',
                    'o.pp_user_id AS user_id',
                    'u.nickname AS nickname',
                    'o.pay_fee AS pay_fee',
                ])
                ->from('order AS o')
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 'o.zpp_user_id')
                    ;
                })
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.status', '=', 200)
                ->paginate(10)
            ;

            return Response::view('web/group/account/index', [
                'data'       => $stat,
                'paginate'   => $rows,
                'date_month' => $query['date_month'] ?? '',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function analysis()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['required'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [
                'group_id.required'  => '公司id不能为空',
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $formatDate = formatDateToWeb($query);
            $date_from  = $formatDate['date_from'];
            $date_to    = $formatDate['date_to'];
            $ts_from    = $formatDate['ts_from'];
            $ts_to      = $formatDate['ts_to'];

            $data = [
                'date_show' => $formatDate['date_show'],
                'count'     => 0,
            ];

            // 公司客户单
            $row = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`o`.`pay_fee`), 0) AS `pay_fee`'), //总金额
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 200)
                ->get()
                ->first()
            ;

            // 普通用户单
            $row1 = DB::query()
                ->select([
                    DB::raw('IFNULL(SUM(`o`.`pay_fee`), 0) AS `pay_fee`'), //总金额
                    DB::raw('COUNT(`o`.`id`) AS `count`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 1)
                ->where('o.status', '=', 200)
                ->get()
                ->first()
            ;

            $data['group_pay_fee']    = $row->pay_fee;
            $data['ordinary_pay_fee'] = $row1->pay_fee;
            $data['count']            = $row->count + $row1->count;

            // 流水占比
            $option1 = [
                'series' => [
                    [
                        'name'              => '访问来源',
                        'type'              => 'pie',
                        'radius'            => ['73%', '100%'],
                        'avoidLabelOverlap' => false,
                        'legend'            => [
                            'selectedMode' => false,
                        ],
                        'label' => [
                            'normal' => [
                                'show'     => false,
                                'position' => 'center',
                            ],
                        ],
                        'color'     => ['#E9868B', '#515884'],
                        'labelLine' => [
                            'normal' => [
                                'show' => false,
                            ],
                        ],
                        'data' => [
                            [
                                'value' => $data['group_pay_fee'],
                                'name'  => '公司客户单',
                            ],
                            [
                                'value' => $data['ordinary_pay_fee'],
                                'name'  => '普通用户单',
                            ],
                        ],
                    ],
                ],
            ];

            $date_format = '%Y-%m-%d';

            if (isset($query['date_month']) && $query['date_month'] > 0) {
                $date_format = '%Y-%m';
            }

            // 公司客户单折线图数据
            $row3 = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`o`.`pay_fee`), 0) AS SIGNED) AS `pay_fee`'), //总金额
                    DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(`o`.`created_at`), "' . $date_format . '") AS `date`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 0)
                ->where('o.status', '=', 200)
                ->groupBy('date')
                ->get()
            ;

            // 普通用户单折线图数据
            $row4 = DB::query()
                ->select([
                    DB::raw('CAST(IFNULL(SUM(`o`.`pay_fee`), 0) AS SIGNED) AS `pay_fee`'), //总金额
                    DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(`o`.`created_at`), "' . $date_format . '") AS `date`'),
                ])
                ->from('order AS o')
                ->where('o.group_id', '=', $query['group_id'])
                ->where('o.created_at', '>=', $date_from)
                ->where('o.created_at', '<', $date_to)
                ->where('o.is_sandan', '=', 1)
                ->where('o.status', '=', 200)
                ->groupBy('date')
                ->get()
            ;

            $line  = array_column(obj2arr($row3), 'pay_fee', 'date');
            $line1 = array_column(obj2arr($row4), 'pay_fee', 'date');

            if (isset($query['date_month']) && $query['date_month'] > 0) {
                $temp = date('Y-m', $ts_from);
            } else {
                $temp = date('Y-m-d', $ts_from);
            }

            $result = [
                'xAxis.data'    => [],
                'series.0.data' => [],
                'series.1.data' => [],
            ];

            do {
                $result['xAxis.data'][]    = $temp;
                $result['series.0.data'][] = rmb($line[$temp] ?? 0);
                $result['series.1.data'][] = rmb($line1[$temp] ?? 0);

                if (isset($query['date_month']) && $query['date_month'] > 0) {
                    $temp = date('Y-m', strtotime($temp . ' +1 month'));
                } else {
                    $temp = date('Y-m-d', strtotime($temp . ' +1 day'));
                }
            } while (strtotime($temp) <= $ts_to);

            // 流水趋势
            $option2 = [
                'tooltip' => [
                    'trigger' => 'axis',
                ],
                'legend' => [
                    'y'    => '90%',
                    'data' => [
                        ['name' => '公司客户单'],
                        ['name' => '普通用户单'],
                    ],
                ],
                'color' => ['#E9868B', '#515884', '#1490F7'],
                'grid'  => [
                    'top'          => '3%',
                    'left'         => '9%',
                    'right'        => '10%',
                    'bottom'       => '15%',
                    'containLabel' => true,
                ],
                'toolbox' => [
                    'feature' => [
                        'saveAsImage' => [],
                    ],
                ],
                'xAxis' => [
                    'type'        => 'category',
                    'boundaryGap' => false,
                    'splitLine'   => [
                        'show' => false,
                    ],
                    'data' => $result['xAxis.data'],
                ],
                'yAxis' => [
                    'type'      => 'value',
                    'splitLine' => [
                        'show' => false,
                    ],
                ],
                'series' => [
                    [
                        'name'       => '公司客户单',
                        'type'       => 'line',
                        'symbol'     => 'circle',
                        'symbolSize' => 8,
                        'data'       => empty($line) ? '' : $result['series.0.data'],
                    ],
                    [
                        'name'       => '普通用户单',
                        'type'       => 'line',
                        'symbol'     => 'circle',
                        'symbolSize' => 8,
                        'data'       => empty($line1) ? '' : $result['series.1.data'],
                    ],
                ],
            ];

            return Response::view('web/group/account/analysis', [
                'data'       => $data,
                'echarts1'   => $option1,
                'echarts2'   => $option2,
                'date_month' => $query['date_month'] ?? '',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
