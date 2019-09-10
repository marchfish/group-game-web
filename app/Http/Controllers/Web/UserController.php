<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Facades\AppSession;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class UserController extends Controller
{
    public function deposit()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [
                'date_from.required' => '开始时间不能为空',
                'date_to.required'   => '结束时间不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $query['table'] = $query['table'] ?? 1;

            $user_id = Session::get('user.id');

            $formatDate = formatDateToWeb($query);
            $date_from  = $formatDate['date_from'];
            $date_to    = $formatDate['date_to'];

            $stat = [
                'date_show' => $formatDate['date_show'],
            ];

            // 当前余额
            $row = DB::query()
                ->select([
                    'deposit',
                ])
                ->from('user_stat AS us')
                ->where('user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            $stat['amount'] = $row->deposit;

            // 总收入单
            $row1 = DB::query()
                ->select([
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('user_deposit_in AS udi')
                ->where('user_id', '=', $user_id)
                ->where('created_at', '>=', $date_from)
                ->where('created_at', '<', $date_to)
                ->get()
                ->first()
            ;

            // 收入明细
            $rows1 = DB::query()
                ->select([
                    'amount',
                    'order_id',
                    'is_share',
                    'created_at',
                ])
                ->from('user_deposit_in AS udi')
                ->where('user_id', '=', $user_id)
                ->where('created_at', '>=', $date_from)
                ->where('created_at', '<', $date_to)
                ->paginate(10)
            ;

            foreach ($rows1->items() as $ro) {
                $ro->title = $ro->order_id == 0 && $ro->is_share == 0 ? '充值' : '分享奖励金';
            }

            // 总支出单
            $row2 = DB::query()
                ->select([
                    DB::raw('COUNT(`id`) AS `count`'),
                ])
                ->from('user_deposit_out AS udo')
                ->where('user_id', '=', $user_id)
                ->where('created_at', '>=', $date_from)
                ->where('created_at', '<', $date_to)
                ->get()
                ->first()
            ;

            // 支出明细
            $rows2 = DB::query()
                ->select([
                    'amount',
                    'order_id',
                    'is_withdraw',
                    'created_at',
                ])
                ->from('user_deposit_out AS udo')
                ->where('user_id', '=', $user_id)
                ->where('created_at', '>=', $date_from)
                ->where('created_at', '<', $date_to)
                ->paginate(10)
            ;

            foreach ($rows2->items() as $ro) {
                $ro->title = $ro->order_id == 0 && $ro->is_withdraw == 1 ? '提现' : '跑腿费';
            }

            switch ($query['table']) {
                case 1:
                    $rows1 = $rows1->toArray();
                    $rows2 = $rows2->toArray();

                    $new_rows = array_merge($rows1['data'], $rows2['data']);

                    $time_str = [];

                    foreach ($new_rows as $key => $v) {
                        $new_rows[$key]->ts_time = strtotime($v->created_at);
                        $time_str[]              = $new_rows[$key]->ts_time;
                    }

                    array_multisort($time_str, SORT_DESC, $new_rows);

                    if ($rows1['total'] > $rows2['total']) {
                        $rows1['data'] = $new_rows;
                        $paginate      = $rows1;
                    } else {
                        $rows2['data'] = $new_rows;
                        $paginate      = $rows2;
                    }

                    $stat['count'] = $row1->count + $row2->count;

                    break;
                case 2:
                    $paginate      = $rows1->toArray();
                    $stat['count'] = $row1->count;

                    break;
                case 3:
                    $paginate      = $rows2->toArray();
                    $stat['count'] = $row2->count;

                    break;
                default:
                    $paginate      = $rows1->toArray();
                    $stat['count'] = $row1->count;

                    break;
            }

            return Response::view('web/user/deposit', [
                'data'       => $stat,
                'paginate'   => $paginate,
                'date_month' => $query['date_month'] ?? '',
                'table'      => $query['table'],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 会员中心-绑定欢迎页
    public function memberCenter()
    {
        try {
            return Response::view('web/user/member-center');
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
