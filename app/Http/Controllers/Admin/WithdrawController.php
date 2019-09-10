<?php

namespace App\Http\Controllers\Admin;

use App\Services\Wxpay\WxpayManager as Wxpay;
use App\Http\Controllers\Controller;
use App\Support\Facades\Message;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;

class WithdrawController extends Controller
{
    // 公司提现列表
    public function group()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id'  => ['nullable', 'integer'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select([
                    'gdw.*',
                    'pu.realname as realname',
                    'pu.tel as tel',
                    DB::raw('IFNULL(`d`.`value`, "") AS withdraw_status'),
                    DB::raw('IFNULL(`sa`.`city`, "") AS city'),
                    DB::raw('IFNULL(`aa`.`nickname`, "") AS reply_nickname'),
                ])
                ->from('group_deposit_withdraw AS gdw')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'gdw.user_id')
                    ;
                })
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'gdw.status')
                        ->where('d.col', '=', 'withdraw.status')
                    ;
                })
                ->leftJoin('sys_area AS sa', function ($join) {
                    $join
                        ->on('sa.code', '=', 'pu.area')
                    ;
                })
                ->leftJoin('admin_account AS aa', function ($join) {
                    $join
                        ->on('aa.id', '=', 'gdw.replay_account_id')
                    ;
                })
                ->orderBy('gdw.created_at', 'desc')
            ;

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/withdraw/group', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 公司同意提现
    public function groupDepositWithdraw()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_withdraw_id' => ['required'],
            ], [
                'group_withdraw_id.required' => 'id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证提现
            $row = DB::query()
                ->select([
                    'gdw.id AS id',
                    'gdw.user_id AS user_id',
                    'gdw.group_id AS group_id',
                    'gdw.amount AS amount',
//                    'ue.wx_gzh_openid AS wx_gzh_openid',
                ])
                ->from('group_deposit_withdraw AS gdw')
//                ->join('user_extend AS ue', function ($join) {
//                    $join
//                        ->on('ue.user_id', '=', 'gdw.user_id')
//                    ;
//                })
                ->where('gdw.id', '=', $query['group_withdraw_id'])
                ->where('gdw.status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于审核中的提现', 400);
            }

//            if ($row->wx_gzh_openid == '') {
//                throw new InvalidArgumentException('该用户未绑定公众号', 400);
//            }

            // 验证今天是否已经提过一次现
//            $row1 = DB::query()
//                ->select([
//                    DB::raw('MAX(`reply_at`) AS `reply_at`'),
//                ])
//                ->from('sys_transfer AS st')
//                ->where('user_id', '=', $row->user_id)
//                ->get()
//                ->first()
//            ;
//
//            if ($row1 && date('Y-m-d', strtotime($row1->reply_at)) == date('Y-m-d', time())) {
//                throw new InvalidArgumentException('该用户今日已提现', 400);
//            }

            // 开始提现
//            $partner_trade_no = gene_no();
            $replay_at = date('Y-m-d H:i:s', time());

            DB::beginTransaction();

            DB::table('group_deposit_withdraw')
                ->where('id', '=', $query['group_withdraw_id'])
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'reply'             => '银行卡提现',
                    'replay_at'         => $replay_at,
//                    'partner_trade_no' => $partner_trade_no,
                    'status' => 200,
                ])
            ;

//            $res = Wxpay::instance()->transferToWallet([
//                'partner_trade_no' => $partner_trade_no,
//                'openid'           => $row->wx_gzh_openid,
//                'amount'           => $row->amount,
            ////                'openid' => 'o3JfrwamRI3kIzroccOlVC2dxB8o',//o3JfrwamRI3kzroccOlVC2dxB8o
//                'desc' => 'api 发工资',
//            ]);


//            if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {

            DB::table('group')
                ->where('id', '=', $row->group_id)
                ->update([
                    'withdrawing_deposit' => DB::raw('`withdrawing_deposit` - ' . $row->amount),
                    'withdrawed_deposit'  => DB::raw('`withdrawed_deposit` + ' . $row->amount),
                ])
            ;

            DB::commit();

//            DB::table('sys_transfer')
//                ->insert([
//                    'group_withdraw_id' => $query['group_withdraw_id'],
//                    'group_id'          => $row->group_id,
//                    'user_id'           => $row->user_id,
//                    'amount'            => $row->amount,
//                    'reply_at'          => $reply_at,
//                ])
//            ;

//            } else {
//                DB::table('group_deposit_withdraw')
//                    ->where('id', '=', $query['group_withdraw_id'])
//                    ->update([
//                        'partner_trade_no' => '',
//                        'reply'            => '公众号提现错误,状态重置',
//                        'status'           => 150,
//                    ])
//                ;
//
//                throw new InvalidArgumentException($res['err_code_des'], 400);
//            }

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

    // 公司拒绝提现
    public function disGroupDepositWithdraw()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_withdraw_id' => ['required'],
                'reply'             => ['required'],
            ], [
                'group_withdraw_id.required' => 'group_withdraw_id 不能为空',
                'reply.required'             => '拒绝理由不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证提现
            $row = DB::query()
                ->select([
                    'id',
                    'group_id',
                    'amount',
                ])
                ->from('group_deposit_withdraw AS gdw')
                ->where('id', '=', $query['group_withdraw_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于审核中的提现', 400);
            }

            DB::beginTransaction();

            // 更新状态
            DB::table('group_deposit_withdraw')
                ->where('id', '=', $query['group_withdraw_id'])
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'reply'             => $query['reply'],
                    'replay_at'         => date('Y-m-d H:i:s', time()),
                    'status'            => 50,
                ])
            ;

            // 把钱退还给公司余额
            DB::table('group')
                ->where('id', '=', $row->group_id)
                ->update([
                    'withdrawing_deposit' => DB::raw('`withdrawing_deposit` - ' . $row->amount),
                    'deposit'             => DB::raw('`deposit` + ' . $row->amount),
                ])
            ;

            DB::commit();

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

    // 跑跑提现列表
    public function user()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id'   => ['nullable', 'integer'],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to'   => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $model = DB::query()
                ->select([
                    'udw.*',
                    'pu.realname as realname',
                    'pu.tel as tel',
                    DB::raw('IFNULL(`d`.`value`, "") AS withdraw_status'),
                    DB::raw('IFNULL(`sa`.`city`, "") AS city'),
                    DB::raw('IFNULL(`aa`.`nickname`, "") AS reply_nickname'),
                ])
                ->from('user_deposit_withdraw AS udw')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'udw.user_id')
                    ;
                })
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'udw.status')
                        ->where('d.col', '=', 'withdraw.status')
                    ;
                })
                ->leftJoin('sys_area AS sa', function ($join) {
                    $join
                        ->on('sa.code', '=', 'pu.area')
                    ;
                })
                ->leftJoin('admin_account AS aa', function ($join) {
                    $join
                        ->on('aa.id', '=', 'udw.replay_account_id')
                    ;
                })
                ->orderBy('udw.created_at', 'desc')
            ;

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/withdraw/user', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 跑跑同意提现
    public function userDepositWithdraw()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_withdraw_id' => ['required'],
            ], [
                'user_withdraw_id.required' => 'id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证提现
            $row = DB::query()
                ->select([
                    'udw.id AS id',
                    'udw.user_id AS user_id',
                    'udw.amount AS amount',
//                    'ue.wx_gzh_openid AS wx_gzh_openid',
                ])
                ->from('user_deposit_withdraw AS udw')
//                ->join('user_extend AS ue', function ($join) {
//                    $join
//                        ->on('ue.user_id', '=', 'udw.user_id')
//                    ;
//                })
                ->where('udw.id', '=', $query['user_withdraw_id'])
                ->where('udw.status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于审核中的提现', 400);
            }

//            if ($row->wx_gzh_openid == '') {
//                throw new InvalidArgumentException('该用户未绑定公众号', 400);
//            }

            // 验证今天是否已经提过一次现
//            $row1 = DB::query()
//                ->select([
//                    DB::raw('MAX(`reply_at`) AS `reply_at`'),
//                ])
//                ->from('sys_transfer AS st')
//                ->where('user_id', '=', $row->user_id)
//                ->get()
//                ->first()
//            ;
//
//            if ($row1 && date('Y-m-d', strtotime($row1->reply_at)) == date('Y-m-d', time())) {
//                throw new InvalidArgumentException('该用户今日已提现', 400);
//            }

            // 开始提现
//            $partner_trade_no = gene_no();
            $replay_at = date('Y-m-d H:i:s', time());

            DB::beginTransaction();

            DB::table('user_deposit_withdraw')
                ->where('id', '=', $query['user_withdraw_id'])
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'reply'             => '银行卡提现',
                    'replay_at'         => $replay_at,
//                    'partner_trade_no'  => $partner_trade_no,
                    'status' => 200,
                ])
            ;

//            $res = Wxpay::instance()->transferToWallet([
//                'partner_trade_no' => $partner_trade_no,
//                'openid'           => $row->wx_gzh_openid,
//                'amount'           => $row->amount,
            ////                'openid' => 'o3JfrwamRI3kIzroccOlVC2dxB8o',//o3JfrwamRI3kzroccOlVC2dxB8o
//                'desc' => 'api 发工资',
//            ]);


//            if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            DB::table('user_stat')
                ->where('user_id', '=', $row->user_id)
                ->update([
                    'withdrawing_deposit' => DB::raw('`withdrawing_deposit` - ' . $row->amount),
                    'withdrawed_deposit'  => DB::raw('`withdrawed_deposit` + ' . $row->amount),
                ])
            ;

            DB::commit();
//                DB::table('sys_transfer')
//                    ->insert([
//                        'user_withdraw_id' => $query['user_withdraw_id'],
//                        'user_id'          => $row->user_id,
//                        'amount'           => $row->amount,
//                        'reply_at'         => $reply_at,
//                    ])
//                ;
//            } else {
//                DB::table('user_deposit_withdraw')
//                    ->where('id', '=', $query['user_withdraw_id'])
//                    ->update([
//                        'partner_trade_no' => '',
//                        'reply'            => '公众号提现错误,状态重置',
//                        'status'           => 150,
//                    ])
//                ;
//
//                throw new InvalidArgumentException($res['err_code_des'], 400);
//            }

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

    // 跑跑拒绝提现
    public function disUserDepositWithdraw()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_withdraw_id' => ['required'],
                'reply'            => ['required'],
            ], [
                'user_withdraw_id.required' => 'user_withdraw_id 不能为空',
                'reply.required'            => '拒绝理由不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证提现
            $row = DB::query()
                ->select([
                    'id',
                    'user_id',
                    'amount',
                ])
                ->from('user_deposit_withdraw AS udw')
                ->where('id', '=', $query['user_withdraw_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于审核中的提现', 400);
            }

            DB::beginTransaction();

            // 更新状态
            DB::table('user_deposit_withdraw')
                ->where('id', '=', $query['user_withdraw_id'])
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'reply'             => $query['reply'],
                    'replay_at'         => date('Y-m-d H:i:s', time()),
                    'status'            => 50,
                ])
            ;

            // 把钱退还给跑跑余额
            DB::table('user_stat')
                ->where('user_id', '=', $row->user_id)
                ->update([
                    'withdrawing_deposit' => DB::raw('`withdrawing_deposit` - ' . $row->amount),
                    'deposit'             => DB::raw('`deposit` + ' . $row->amount),
                ])
            ;

            DB::commit();

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
