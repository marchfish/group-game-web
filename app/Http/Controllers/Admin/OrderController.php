<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    // 列表
    public function index()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select([
                    'o.*',
                    'u.nickname as zpp_name',
                    'u.tel as zpp_tel',
                    'u.id as zpp_id',
                    'pu.id as pp_id',
                    'pu.realname as pp_name',
                    'pu.tel as pp_tel',
                    'area.city',
                    'area.province',
                    DB::raw('IFNULL(`d`.`value`, "") AS order_status'),
                ])
                ->from('order AS o')
                ->leftJoin('user as u', function ($join) {
                    $join
                        ->on('u.id', '=', 'o.zpp_user_id')
                    ;
                })
                ->leftJoin('order_detail as od', function ($join) {
                    $join
                        ->on('od.order_id', '=', 'o.id')
                    ;
                })
                ->leftJoin('sys_area as area', function ($join) {
                    $join
                        ->on('area.code', '=', 'od.area')
                    ;
                })
                ->leftJoin('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'o.pp_user_id')
                    ;
                })
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'o.status')
                        ->where('d.col', '=', 'order.status')
                    ;
                })
                ->orderBy('o.created_at', 'desc')
            ;

            if (isset($query['no'])) {
                $model->where('o.no', 'like', $query['no'] . '%');
            }

            if (isset($query['zpp_tel'])) {
                $model->where('u.tel', 'like', $query['zpp_tel'] . '%');
            }

            if (isset($query['pp_tel'])) {
                $model->where('pu.tel', 'like', $query['pp_tel'] . '%');
            }

            if (isset($query['city'])) {
                $model->where('area.city', 'like', '%' . $query['city'] . '%');
            }

            $paginate = $model->paginate(isset($query['size']) ? $query['size'] : 10);

            return Response::view('admin/order/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 查看订单详情
    public function orderCheck()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'order_id' => ['required'],
            ], [
                'order_id.required' => 'order_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 订单
            $row = DB::query()
                ->select([
                    'o.*',
                    'u.nickname AS nickname',
                    'u.avatar AS zpp_avatar',
                    'u.tel AS zpp_tel',
                    DB::raw('IFNULL(`pu`.`realname`, "") AS `realname`'),
                    DB::raw('IFNULL(`pu`.`avatar`, "") AS `pp_avatar`'),
                    DB::raw('IFNULL(`pu`.`tel`, "") AS `pp_tel`'),
                    DB::raw('IFNULL(`d`.`value`, "") AS `order_status`'),
                ])
                ->from('order AS o')
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 'o.zpp_user_id')
                    ;
                })
                ->leftJoin('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'o.pp_user_id')
                    ;
                })
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'o.status')
                        ->where('d.col', '=', 'order.status')
                    ;
                })
                ->where('o.id', '=', $query['order_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不存在的订单', 400);
            }

            $row->pp_avatar = upload_url($row->pp_avatar);
            $row->zpp_avatar = upload_url($row->zpp_avatar);

            //支付记录
            $row1 = DB::query()
                ->select([
                    'sp.*',
                    DB::raw('IFNULL(`d`.`value`, "") AS `sys_pay_status`'),
                ])
                ->from('sys_pay AS sp')
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'sp.status')
                        ->where('d.col', '=', 'sys_pay.status')
                    ;
                })
                ->where('sp.id', '=', $row->pay_id)
                ->get()
                ->first()
            ;

            if ($row1) {
                $row1->payway = $row1->payway == 1 ? '支付宝' : '微信';
            }

            // 历史记录
            $rows = DB::query()
                ->select([
                    'oh.*',
                ])
                ->from('order_history AS oh')
                ->where('oh.order_id', '=', $row->id)
                ->orderBy('oh.created_at', 'asc')
                ->get()
            ;

            // 竞价记录
            $rows1 = DB::query()
                ->select([
                    'pb.*',
                    'pu.realname AS realname',
                    'pu.avatar AS avatar',
                    'us.review_star AS review_star',
                    'us.review_num AS review_num',
                ])
                ->from('pp_bid AS pb')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'pb.user_id')
                    ;
                })
                ->join('user_stat AS us', function ($join) {
                    $join
                        ->on('us.user_id', '=', 'pb.user_id')
                    ;
                })
                ->where('pb.order_id', '=', $row->id)
                ->orderBy('pb.created_at', 'asc')
                ->get()
            ;

            foreach ($rows1 as $ro){
                $ro->star_img = 'http://paopao.myncic.com/public/img/level/' . ($ro->review_num > 0 ? intval($ro->review_star / $ro->review_num) : 5) . '.png';
                $ro->avatar = upload_url($ro->avatar);
            }

            return Response::view('admin/order/check', [
                'data' => [
                    'order'   => $row,
                    'pay'     => $row1,
                    'history' => $rows,
                    'bid'     => $rows1,
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 订单投诉列表
    public function orderReport()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select([
                    'or.*',
                    'o.no AS no',
                    'o.pp_user_id AS pp_user_id',
                    'o.zpp_user_id AS zpp_user_id',
                    'pu.realname AS realname',
                    'u.nickname AS nickname',
                ])
                ->from('order_report AS or')
                ->join('order AS o', function ($join) {
                    $join
                        ->on('o.id', '=', 'or.order_id')
                    ;
                })
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'o.pp_user_id')
                    ;
                })
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 'o.zpp_user_id')
                    ;
                })
                ->orderBy('or.created_at', 'desc')
            ;

            if (isset($query['no'])) {
                $model->where('o.no', 'like', $query['no'] . '%');
            }

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/order/report/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 查看投诉详情
    public function orderReportCheck()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'order_report_id' => ['required'],
            ], [
                'order_report_id.required' => 'order_report_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'or.*',
                    'o.pp_user_id AS pp_user_id',
                    'o.zpp_user_id AS zpp_user_id',
                    'pu.realname AS realname',
                    'u.nickname AS nickname',
                ])
                ->from('order_report AS or')
                ->join('order AS o', function ($join) {
                    $join
                        ->on('o.id', '=', 'or.order_id')
                    ;
                })
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'o.pp_user_id')
                    ;
                })
                ->join('user AS u', function ($join) {
                    $join
                        ->on('u.id', '=', 'o.zpp_user_id')
                    ;
                })
                ->where('or.id', '=', $query['order_report_id'])
                ->get()
                ->first()
            ;

            $row->pic = upload_url($row->pic);

            return Response::view('admin/order/report/check', [
                'row' => $row,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 处理完成
    public function orderReportSuccess()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'order_report_id' => ['required'],
                'result'          => ['required'],
            ], [
                'order_report_id.required' => 'order_report_id不能为空',
                'result.required'          => '处理结果不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 更新数据
            DB::table('order_report')
                ->where('id', '=', $query['order_report_id'])
                ->update([
                    'result' => $query['result'],
                    'status' => 200,
                ])
            ;

            return Response::json([
                'code'    => 200,
                'message' => '成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

}
