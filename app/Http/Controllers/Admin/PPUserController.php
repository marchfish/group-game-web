<?php

namespace App\Http\Controllers\Admin;

use App\Models\PPUser;
use App\Http\Controllers\Controller;
use App\Support\Facades\Message;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class PPUserController extends Controller
{
    // 列表
    public function index()
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
                    'pu.*',
                    DB::raw('IFNULL(`sa`.`city`, "") AS city'),
                ])
                ->from('pp_user AS pu')
                ->leftJoin('sys_area AS sa', function ($join) {
                    $join
                        ->on('sa.code', '=', 'pu.area')
                    ;
                })
                ->orderBy('pu.created_at', 'desc')
            ;

            if (isset($query['realname'])) {
                $model->where('pu.realname', 'like', '%' . $query['realname'] . '%');
            }

            if (isset($query['tel'])) {
                $model->where('pu.tel', '=', $query['tel']);
            }

//            if (isset($query['date_from'])) {
//                $date_from = date('Y-m-d H:i:s', strtotime($query['date_from']));
//
//                $model = $model->where('g.created_at', '>=', $date_from);
//            }
//
//            if (isset($query['date_to'])) {
//                $date_to = date('Y-m-d H:i:s', strtotime($query['date_to'] . ' +1 day'));
//
//                $model = $model->where('g.created_at', '<', $date_to);
//            }

            $paginate = $model->paginate($query['size']);

            foreach ($paginate->items() as $item) {
                $item->avatar  = upload_url($item->avatar);
                $item->status1 = PPUser::statusToStr($item->status);
            }

            return Response::view('admin/pp-user/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 验证通过
    public function verificationPassed()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('pp_user')
                ->where('user_id', '=', $query['user_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待验证的跑跑', 400);
            }

            $picRow = DB::query()
                ->select([
                    'pp_pic4',
                ])
                ->from('pp_attach')
                ->where('user_id', '=', $query['user_id'])
                ->limit(1)
                ->get()
                ->first()
            ;

            // 更改跑跑状态
            DB::table('pp_user')
                ->where('id', '=', $row->id)
                ->update([
                    'avatar' => $picRow->pp_pic4,
                    'status' => 200,
                ])
            ;

            DB::table('user')
                ->where('id', '=', $query['user_id'])
                ->where('avatar', '=', '')
                ->update([
                    'avatar' => $picRow->pp_pic4,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '身份认证通过',
                'content'  => '恭喜您! 身份认证通过',
                'extra'    => [
                    'type'    => 'user-check-success',
                    'user_id' => $query['user_id'],
                ],
            ]);

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

    // 验证驳回
    public function verificationFailed()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('pp_user')
                ->where('user_id', '=', $query['user_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待验证的跑跑', 400);
            }

            // 更改跑跑状态
            DB::table('pp_user')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 50,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '身份认证驳回',
                'content'  => '对不起! 身份认证被驳回',
                'extra'    => [
                    'type'    => 'user-check-fail',
                    'user_id' => $query['user_id'],
                ],
            ]);

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

    // 查看跑跑提交的资料
    public function check()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'pu.user_id AS user_id',
                    'pu.realname AS realname',
                    'pu.tel AS tel',
                    'pu.idcard AS idcard',
                    'pu.contact_man AS contact_man',
                    'pu.contact_tel AS contact_tel',
                    DB::raw('IFNULL(`pa`.`pp_pic1`, "") AS pp_pic1'),
                    DB::raw('IFNULL(`pa`.`pp_pic2`, "") AS pp_pic2'),
                    DB::raw('IFNULL(`pa`.`pp_pic3`, "") AS pp_pic3'),
                    DB::raw('IFNULL(`pa`.`pp_pic4`, "") AS pp_pic4'),
                    DB::raw('IFNULL(`pa`.`pp_pic5`, "") AS pp_pic5'),
                    'pu.status AS status',
                ])
                ->from('pp_user AS pu')
                ->leftJoin('pp_attach AS pa', function ($join) {
                    $join
                        ->on('pa.user_id', '=', 'pu.user_id')
                    ;
                })
                ->where('pu.user_id', '=', $query['user_id'])
                ->get()
                ->first()
            ;

            $row->pp_pic1 = upload_url($row->pp_pic1);
            $row->pp_pic2 = upload_url($row->pp_pic2);
            $row->pp_pic3 = upload_url($row->pp_pic3);
            $row->pp_pic4 = upload_url($row->pp_pic4);
            $row->pp_pic5 = upload_url($row->pp_pic5);

            return Response::view('admin/pp-user/check', [
                'row' => $row,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 禁用
    public function disabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('pp_user')
                ->where('user_id', '=', $query['user_id'])
                ->where('status', '=', 200)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于通过验证的跑跑', 400);
            }

            // 更改跑跑状态
            DB::table('pp_user')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 0,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '禁用提醒',
                'content'  => '您已被管理员禁用',
                'extra'    => [
                    'type'    => 'user-disabled',
                    'user_id' => $query['user_id'],
                ],
            ]);

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

    // 解禁
    public function removeDisabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('pp_user')
                ->where('user_id', '=', $query['user_id'])
                ->where('status', '=', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被禁用的跑跑', 400);
            }

            // 更改跑跑状态
            DB::table('pp_user')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '解禁提醒',
                'content'  => '您的禁用已解除',
                'extra'    => [
                    'type'    => 'user-disabled-remove',
                    'user_id' => $query['user_id'],
                ],
            ]);

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

    // 获取所有审核的头像
    public function avatar()
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
                    'aa.*',
                    'pu.realname AS realname',
                    'pu.tel AS tel',
                    DB::raw('IFNULL(`aac`.`nickname`, "") AS replay_nickname'),
                    DB::raw('IFNULL(`d`.`value`, "") AS verify_status'),
                ])
                ->from('admin_audit AS aa')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'aa.user_id')
                    ;
                })
                ->leftJoin('admin_account AS aac', function ($join) {
                    $join
                        ->on('aac.id', '=', 'aa.replay_account_id')
                    ;
                })
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'aa.status')
                        ->where('d.col', '=', 'withdraw.status')
                    ;
                })
                ->where('aa.type', '=', 'tx')
                ->orderBy('aa.created_at', 'desc')
            ;

            $paginate = $model->paginate($query['size']);

            foreach ($paginate->items() as $item) {
                $item->avatar = upload_url($item->info);
            }

            return Response::view('admin/pp-user/verify/avatar', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 头像验证通过
    public function avatarSuccess()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有需要审核的跑跑
            $row = DB::query()
                ->select([
                    'id',
                    'info',
                ])
                ->from('admin_audit')
                ->where('user_id', '=', $query['user_id'])
                ->where('type', '=', 'tx')
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待审核的头像', 400);
            }

            DB::beginTransaction();

            // 更新数据
            DB::table('admin_audit')
                ->where('id', '=', $row->id)
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'replay'            => '',
                    'replay_at'         => date('Y-m-d H:i:s', time()),
                    'status'            => 200,
                ])
            ;

            // 更新跑跑信息
            DB::table('pp_user')
                ->where('user_id', '=', $query['user_id'])
                ->update([
                    'avatar' => $row->info,
                ])
            ;

            DB::commit();

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '头像审核通过',
                'content'  => '您上传的头像通过审核',
                'extra'    => [
                    'type'    => 'user-avatar-success',
                    'user_id' => $query['user_id'],
                ],
            ]);

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

    // 头像验证驳回
    public function avatarFail()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'user_id' => ['required'],
                'replay'  => ['required'],
            ], [
                'user_id.required' => 'user_id不能为空',
                'replay.required'  => '理由不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证
            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('admin_audit')
                ->where('user_id', '=', $query['user_id'])
                ->where('type', '=', 'tx')
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待审核的头像', 400);
            }

            // 更新数据
            DB::table('admin_audit')
                ->where('id', '=', $row->id)
                ->update([
                    'replay_account_id' => Session::get('admin.account.id'),
                    'replay'            => $query['replay'],
                    'replay_at'         => date('Y-m-d H:i:s', time()),
                    'status'            => 50,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$query['user_id']],
                'title'    => '头像审核未通过',
                'content'  => '请使用本人的正面清晰照片',
                'extra'    => [
                    'type'    => 'user-avatar-fail',
                    'user_id' => $query['user_id'],
                ],
            ]);

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
