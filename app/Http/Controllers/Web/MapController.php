<?php

namespace App\Http\Controllers\Web;

use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class MapController extends Controller
{
    // 传送
    public function transfer()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'item_id' => ['required'],
            ], [
                'item_id.required' => '物品id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('uk.item_id', '=', $query['item_id'])
                ->where('uk.item_num', '>', 0)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('您没有该物品', 400);
            }

            if ($row->type == 30) {
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

                $userRole = UserRole::getUserRole();

                $res = '您已传送至：' . $userRole->map_name;
            }

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

    // 活动地图显示
    public function activity()
    {
        try {
            // 判断是否存在物品
            $rows = DB::query()
                ->select([
                    'm.*',
                    'md.hour AS hour',
                    'md.description AS description1',
                ])
                ->from('map AS m')
                ->join('map_date AS md', function ($join) {
                    $join
                        ->on('md.map_id', '=', 'm.id')
                    ;
                })
                ->where('md.is_activity', '=', 1)
                ->get()
            ;

            $res = '[当前活动地图]<br>';

            foreach ($rows as $row) {
                if ($row->hour != '' && strpos($row->hour, date('H', time())) === false) {
                    continue ;
                }

                $res .= '<span class="wr-color-E53E27">'. $row->name . '</span>：' . $row->description1 . '<br>';

                $res .= ' <input type="button" class="action" data-url="' . URL::to('map/activity/transfer') . "?map_id=" . $row->id . '" value="传送" />' . '<br>';
            }

            $res .= '地图均为限时地图，别犹豫看见就进！';

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
                'map_id' => ['required', 'integer'],
            ], [
                'map_id.required' => '地图id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

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
                ->where('md.map_id', '=', $query['map_id'])
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
                    'map_id' => $query['map_id'],
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
