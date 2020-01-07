<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TowerController extends Controller
{
    public function show()
    {
        try {
            $query = Request::all();

            $rows = DB::query()
                ->select([
                    'mt.*',
                    'm.name AS map_name',
                ])
                ->from('map_transfer AS mt')
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.id', '=', 'mt.map_id')
                    ;
                })
                ->where('mt.type', '=', 1)
                ->paginate($query['size'])
            ;

            $res = '[镇妖塔] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .='[' . $row->map_name . '] :' . $row->coin . '金币 (等级：' . $row->min_level . '-' . $row->max_level . ')\r\n';
            }

            $res .= '进入：地图名称 (均为付费地图，请对应等级进入！)';
            $res .= '翻页：镇妖塔 数字';

            return Response::json([
                'code'    => 200,
                'message' => $res,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 进入
    public function into()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'map_name' => ['required'],
            ], [
                'map_name.required' => '地图名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'mt.*',
                    'm.name AS map_name',
                ])
                ->from('map_transfer AS mt')
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.id', '=', 'mt.map_id')
                    ;
                })
                ->where('m.name', '=', $query['map_name'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有该地图：' . $query['map_name'] . '!', 400);
            }

            if ($user_role->coin < $row->coin) {
                throw new InvalidArgumentException('您的金币不足：' . $row->coin . '!', 400);
            }

            if ($user_role->level > $row->max_level || $user_role->level < $row->min_level) {
                throw new InvalidArgumentException('您等级不符：' . $row->min_level . '-' . $row->max_level . '!', 400);
            }

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'map_id' => $row->map_id,
                    'coin' => DB::raw('`coin` - ' . $row->coin)
                ])
            ;

            $res = '您已传送至：' . $row->map_name;

            return Response::json([
                'code'    => 200,
                'message' => $res
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
