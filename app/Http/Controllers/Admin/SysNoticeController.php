<?php

namespace App\Http\Controllers\Admin;

use App\Models\SysNotice;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Facades\Session;

class SysNoticeController extends Controller
{
    // 列表
    public function index()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'sys_notice_id' => ['nullable', 'integer'],
                'date_from'     => ['nullable', 'date_format:Y-m-d'],
                'date_to'       => ['nullable', 'date_format:Y-m-d'],
            ], [

            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $paginate = DB::query()
                ->select([
                    'sn.*',
                    DB::raw('IFNULL(`sa`.`city`, "") AS city'),
                ])
                ->from('sys_notice AS sn')
                ->leftJoin('sys_area AS sa', function ($join) {
                    $join
                        ->on('sa.code', '=', 'sn.area')
                    ;
                })
                ->paginate($query['size']);


            foreach ($paginate->items() as $item) {
                $item->status1 = SysNotice::statusToStr($item->status);
            }

            return Response::view('admin/sys-notice/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function edit()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'sys_notice_id' => ['nullable', 'integer'],
            ], [
                'sys_notice_id.required' => 'id 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'sn.*',
                ])
                ->from('sys_notice AS sn')
                ->where('id', '=', $query['sys_notice_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有该通知', 400);
            }

            $citys = [];

            $row1s = DB::query()
                ->select([
                    'code',
                    'city',
                    'word',
                ])
                ->from('sys_area AS sa')
                ->orderBy('short', 'asc')
                ->get()
            ;

            foreach ($row1s as $row1) {
                $citys[$row1->word][$row1->code] = $row1->city;
            }

            $row->citys = $citys;

            return Response::view('admin/sys-notice/edit', [
                'row' => $row,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'sys_notice_id' => ['required', 'integer'],
                'title'         => ['required'],
                'content'       => ['required'],
                'start_at'      => ['required', 'date_format:Y-m-d H:i:s'],
                'end_at'        => ['required', 'date_format:Y-m-d H:i:s'],
                'hour'          => ['nullable', 'array'],
                'isUrgent'      => ['required', 'integer'],
            ], [
                'sys_notice_id.required' => 'id 不能为空',
                'title.required'         => '标题不能为空',
                'content.required'       => '内容不能为空',
                'start_at.required'      => '开始时间不能为空',
                'end_at.required'        => '结束时间不能为空',
                'area.required'          => '城市不能为空',
                'isUrgent.required'      => '是否立即推送不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'id',
                ])
                ->from('sys_notice AS sn')
                ->where('id', '=', $query['sys_notice_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有该通知', 400);
            }

            DB::table('sys_notice')
                ->where('id', '=', $row->id)
                ->update([
                    'title'    => $query['title'],
                    'content'  => $query['content'],
                    'start_at' => $query['start_at'],
                    'end_at'   => $query['end_at'],
                    'hour'     => implode(',', $query['hour']),
                    'client'   => $query['client'] ?? '',
                    'area'     => $query['area'] ?? '',
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

    public function new()
    {
        try {
            $citys = [];

            $rows = DB::query()
                ->select([
                    'code',
                    'city',
                    'word',
                ])
                ->from('sys_area AS sa')
                ->orderBy('short', 'asc')
                ->get()
            ;

            foreach ($rows as $row) {
                $citys[$row->word][$row->code] = $row->city;
            }

            return Response::view('admin/sys-notice/new', [
                'row' => $citys,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function create()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'title'    => ['required'],
                'content'  => ['required'],
                'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
                'end_at'   => ['required', 'date_format:Y-m-d H:i:s'],
                'hour'     => ['nullable', 'array'],
                'isUrgent' => ['required', 'integer'],
            ], [
                'title.required'    => '标题不能为空',
                'content.required'  => '内容不能为空',
                'start_at.required' => '开始时间不能为空',
                'end_at.required'   => '结束时间不能为空',
                'area.required'     => '城市不能为空',
                'isUrgent.required' => '是否立即推送不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            DB::table('sys_notice')
                ->insert([
                    'title'    => $query['title'],
                    'content'  => $query['content'],
                    'start_at' => $query['start_at'],
                    'end_at'   => $query['end_at'],
                    'hour'     => implode(',', $query['hour']),
                    'client'   => $query['client'] ?? '',
                    'area'     => $query['area'] ?? '',
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

    public function delete()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'sys_notice_id' => ['nullable', 'integer'],
            ], [
                'sys_notice_id.required' => 'id 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            DB::table('sys_notice')
                ->where('id', '=', $query['sys_notice_id'])
                ->delete()
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
