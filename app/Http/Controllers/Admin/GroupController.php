<?php

namespace App\Http\Controllers\Admin;

use App\Models\Group;
use App\Http\Controllers\Controller;
use App\Support\Facades\Message;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GroupController extends Controller
{
    // 列表
    public function index()
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
                    'g.*',
                    'pu.realname AS realname',
                    'pu.tel AS tel',
                    'sa.city AS city',
                ])
                ->from('group AS g')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'g.user_id')
                    ;
                })
                ->join('sys_area AS sa', function ($join) {
                    $join
                        ->on('sa.code', '=', 'g.area')
                    ;
                })
                ->orderBy('created_at', 'desc')
            ;

            if (isset($query['no'])) {
                $model->where('g.no', 'like', $query['no'] . '%');
            }

            if (isset($query['name'])) {
                $model->where('g.name', 'like', '%' . $query['name'] . '%');
            }

            if (isset($query['tel'])) {
                $model->where('pu.tel', '=', $query['tel']);
            }

            if (isset($query['city'])) {
                $model->where('sa.city', 'like', '%' . $query['city'] . '%');
            }

//            if (isset($query['date_from'])) {
//                $date_from = date('Y-m-d H:i:s', strtotime($query['date_from']));
//
//                $model->where('g.created_at', '>=', $date_from);
//            }
//
//            if (isset($query['date_to'])) {
//                $date_to = date('Y-m-d H:i:s', strtotime($query['date_to'] . ' +1 day'));
//
//                $model->where('g.created_at', '<', $date_to);
//            }

            $paginate = $model->paginate($query['size']);

            foreach ($paginate->items() as $item) {
                $item->logo    = upload_url($item->logo);
                $item->status1 = Group::statusToStr($item->status);

                //获取成员数
                $row = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS gu_count'),
                    ])
                    ->from('group_user')
                    ->where('group_id', '=', $item->id)
                    ->where('status', '=', 200)
                    ->get()
                    ->first();

                $item->gu_count = $row->gu_count;

                //获取商家数
                $row1 = DB::query()
                    ->select([
                        DB::raw('COUNT(`id`) AS ug_count'),
                    ])
                    ->from('user_group')
                    ->where('group_id', '=', $item->id)
                    ->where('status', '=', 200)
                    ->get()
                    ->first();


                $item->ug_count = $row1->ug_count;
            }

            return Response::view('admin/group/index', [
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
                'group_id' => ['required'],
            ], [
                'group_id.required' => 'group_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有该公司
            $row = DB::query()
                ->select([
                    'id',
                    'user_id',
                    'name',
                ])
                ->from('group')
                ->where('id', '=', $query['group_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待验证的公司', 400);
            }

            //判断是否有在加入公司
            $row1 = DB::query()
                ->select([
                    'id',
                ])
                ->from('group_user')
                ->where('user_id', '=', $row->user_id)
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            DB::beginTransaction();

            // 更改公司状态
            DB::table('group')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            if ($row1) {
                // 删除所有加入申请
                DB::table('group_user')
                    ->where('user_id', '=', $row->user_id)
                    ->where('status', '=', 150)
                    ->where('role_id', '=', 2)
                    ->delete()
                ;
            }

            DB::commit();

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$row->user_id],
                'title'    => '创建公司认证通过',
                'content'  => '恭喜您! 公司《' . $row->name . '》认证通过',
                'extra'    => [
                    'type'     => 'group-create-success',
                    'group_id' => $query['group_id'],
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
                'group_id' => ['required'],
            ], [
                'group_id.required' => 'group_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有该公司
            $row = DB::query()
                ->select([
                    'id',
                    'user_id',
                    'name',
                ])
                ->from('group')
                ->where('id', '=', $query['group_id'])
                ->where('status', '=', 150)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于待验证的公司', 400);
            }

            // 更改公司状态
            DB::table('group')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 50,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$row->user_id],
                'title'    => '创建公司认证失败',
                'content'  => '对不起! 公司《' . $row->name . '》认证失败',
                'extra'    => [
                    'type' => 'group-create-fail',
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

    // 禁用
    public function disabled()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => 'group_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有该公司
            $row = DB::query()
                ->select([
                    'id',
                    'user_id',
                    'name',
                ])
                ->from('group')
                ->where('id', '=', $query['group_id'])
                ->where('status', '=', 200)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于通过验证的公司', 400);
            }

            // 更改公司状态
            DB::table('group')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 0,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$row->user_id],
                'title'    => '公司禁用',
                'content'  => $row->name . '公司被禁用',
                'extra'    => [
                    'type'     => 'group-disabled',
                    'group_id' => $query['group_id'],
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
                'group_id' => ['required'],
            ], [
                'group_id.required' => 'group_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            // 验证是否有该公司
            $row = DB::query()
                ->select([
                    'id',
                    'user_id',
                    'name',
                ])
                ->from('group')
                ->where('id', '=', $query['group_id'])
                ->where('status', '=', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('不属于被禁用的公司', 400);
            }

            // 更改公司状态
            DB::table('group')
                ->where('id', '=', $row->id)
                ->update([
                    'status' => 200,
                ])
            ;

            // 推送
            Message::send([
                'client'   => 'pp',
                'user_ids' => [$row->user_id],
                'title'    => '公司解禁',
                'content'  => $row->name . '公司禁用已解除',
                'extra'    => [
                    'type'     => 'group-disabled-remove',
                    'group_id' => $query['group_id'],
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

    // 查看公司提交的资料
    public function check()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'group_id' => ['required'],
            ], [
                'group_id.required' => 'group_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'g.id AS id',
                    'g.user_id AS user_id',
                    'g.name AS name',
                    'g.logo AS logo',
                    'g.certificate_code AS certificate_code',
                    'g.address AS address',
                    'g.description AS description',
                    'pu.realname AS realname',
                    'pu.tel AS tel',
                    'pu.idcard AS idcard',
                    DB::raw('IFNULL(`ga`.`g_pic1`, "") AS g_pic1'),
                    'g.status AS status',
                ])
                ->from('group AS g')
                ->join('pp_user AS pu', function ($join) {
                    $join
                        ->on('pu.user_id', '=', 'g.user_id')
                    ;
                })
                ->leftJoin('group_attach AS ga', function ($join) {
                    $join
                        ->on('ga.group_id', '=', 'g.id')
                    ;
                })
                ->where('g.id', '=', $query['group_id'])
                ->get()
                ->first()
            ;

            $row->logo   = upload_url($row->logo);
            $row->g_pic1 = upload_url($row->g_pic1);

            return Response::view('admin/group/check', [
                'row' => $row,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
