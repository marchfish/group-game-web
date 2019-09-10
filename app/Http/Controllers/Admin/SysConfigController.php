<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SysConfigController extends Controller
{
    public function index()
    {
        try {
            $rows = DB::table('sys_config')
                ->select([
                    '*',
                ])
                ->orderBy('sort', 'asc')
                ->get()
            ;

            return Response::view('admin/sys-config/index', [
                'rows' => $rows,
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
                'settings' => ['required', 'array'],
            ], [
                'settings.required' => 'settings 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            //先获取加急跑跑门槛
            $row = DB::query()
                ->select([
                    '*',
                ])
                ->from('sys_config')
                ->where('key', '=', 'quick_min')
                ->get()
                ->first()
            ;

            DB::beginTransaction();

            foreach ($query['settings'] as $id => $setting) {
                DB::table('sys_config')
                    ->where('id', '=', $id)
                    ->update([
                        'value' => $setting,
                    ])
                ;

                if ($row && $row->id == $id && $row->value != $setting) {
                    // 查询所有达标的跑跑user_id
                    $rows = DB::query()
                        ->select([
                            'user_id',
                        ])
                        ->from('user_stat')
                        ->where('pp_exp', '>=', $setting)
                        ->get()
                    ;

                    $user_ids = array_column(obj2arr($rows), 'user_id');

                    // 更新所有跑跑的加急
                    DB::table('pp_user')
                        ->whereIn('user_id', $user_ids)
                        ->update([
                            'is_quick' => 1,
                        ])
                    ;

                    DB::table('pp_user')
                        ->whereNotIn('user_id', $user_ids)
                        ->update([
                            'is_quick' => 0,
                        ])
                    ;
                }
            }

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '保存成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        } catch (QueryException $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
