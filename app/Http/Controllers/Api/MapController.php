<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class MapController extends Controller
{
    // 传送
    public static function transfer()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_name' => ['required'],
            ], [
                'item_name.required' => '物品名称不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            // 判断是否存在物品
            $row = DB::query()
                ->select([
                    'i.*',
                ])
                ->from('user_knapsack AS uk')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 'uk.item_id')
                    ;
                })
                ->where('uk.user_role_id', '=', $user_role_id)
                ->where('i.name', '=', $query['item_name'])
                ->where('uk.item_num', '>', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('您没有该物品!', 400);
            }

            if ($row->type != 30) {
                throw new InvalidArgumentException('该物品不属于传送卷!', 400);
            }

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'map_id' => $row->content,
                ])
            ;

            DB::table('user_knapsack')
                ->where('user_role_id', '=', $user_role_id)
                ->where('item_id', '=', $row->id)
                ->update([
                    'item_num' => DB::raw('`item_num` - ' . 1),
                ])
            ;

            DB::commit();

            $userRole = UserRole::getUserRoleToQQ($user_role_id);

            $res = '您已传送至：' . $userRole->map_name;

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

    // 活动地图显示
    public function activity()
    {
        try {
            // 判断是否存在物品
            $rows = DB::query()
                ->select([
                    'm.*',
                    'md.hour AS hour',
                ])
                ->from('map AS m')
                ->join('map_date AS md', function ($join) {
                    $join
                        ->on('md.map_id', '=', 'm.id')
                    ;
                })
                ->where('m.is_activity', '=', 1)
                ->get()
            ;

            $res = '[当前活动地图]\r\n';

            foreach ($rows as $row) {
                if ($row->hour != '' && strpos($row->hour, date('H', time())) === false) {
                    continue ;
                }

                $res .=  $row->name . '：' . $row->description . '\r\n';
            }

            $res .= '输入：活动传送 地图名称';

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

    // 活动地图传送
    public function activityTransfer()
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
                    'md.*',
                    'm.name AS map_name',
                ])
                ->from('map_date AS md')
                ->join('map AS m', function ($join) {
                    $join
                        ->on('m.id', '=', 'md.map_id')
                    ;
                })
                ->where('m.name', '=', $query['map_name'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有该活动地图！', 400);
            }

            if ($row->hour != '' && strpos($row->hour, date('H', time())) === false) {
                throw new InvalidArgumentException('地图还未到开启时间！', 400);
            }

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'map_id' => $row->map_id,
                ])
            ;

            $res = '您已传送至：' . $row->map_name;

            return Response::json([
                'code'    => 200,
                'message' => $res ?? '',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
