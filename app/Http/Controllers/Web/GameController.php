<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class GameController extends Controller
{
    // 界面
    public function index()
    {
        return Response::view('web/game/index');
    }

    // 获取位置信息
    public function location()
    {
        try {
            $user_id = Session::get('user.account.id');

            $row = DB::query()
                ->select([
                    'm.name AS name',
                    'm.npc AS npc',
                    'm.enemy AS enemy',
                    DB::raw('IFNULL(`map1`.`name`, "") AS `left_name`'),
                ])
                ->from('map AS m')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.map_id', '=', 'm.id')
                    ;
                })
                ->leftJoin('map AS map1', function ($join) {
                    $join
                        ->on('map1.id', '=', 'm.left')
                    ;
                })
                ->where('ur.user_id', '=', $user_id)
                ->get()
                ->first()
            ;

            $data = '[当前位置]<br>' . $row->name . '<br>';

            $data .= 'npc=' . $row->npc . '<br>';

            $data .= '怪物=' . $row->enemy . '<br> ';

            return Response::json([
                'code'    => 200,
                'message' => $data,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 上
    public function up()
    {
        return Response::json([
            'code'    => 200,
            'message' => '上',
        ]);
    }

    // 下
    public function down()
    {
        return Response::json([
            'code'    => 200,
            'message' => '下',
        ]);
    }
}
