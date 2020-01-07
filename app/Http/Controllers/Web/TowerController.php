<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
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
                ->get()
            ;

            $res = '[镇妖塔]<br>';

            foreach ($rows as $row) {
                $res .= '<span class="wr-color-E53E27">['. $row->map_name . ']</span>：' . $row->coin . '金币 (等级：' . $row->min_level . '-' . $row->max_level . ')<br>';

                $res .= ' <input type="button" class="action" data-url="' . URL::to('tower/into') . "?map_transfer_id=" . $row->id . '" value="进入" />' . '<br>';
            }

            $res .= '均为付费地图，请对应等级进入！(建议组队前往)';

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
                'map_transfer_id' => ['required'],
            ], [
                'map_transfer_id.required' => 'id 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $user_role = UserRole::getUserRole();

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
                ->where('mt.id', '=', $query['map_transfer_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有该地图!', 400);
            }

            if ($user_role->coin < $row->coin) {
                throw new InvalidArgumentException('您的金币不足：' . $row->coin, 400);
            }

            if ($user_role->level > $row->max_level || $user_role->level < $row->min_level) {
                throw new InvalidArgumentException('您等级不符：' . $row->min_level . '-' . $row->max_level, 400);
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
