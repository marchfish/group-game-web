<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class MissionController extends Controller
{
    // 显示
    public function show()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'mission_id' => ['required'],
            ], [
                'mission_id.required' => '任务id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $row = DB::query()
                ->select([
                    'm.*',
                ])
                ->from('mission AS m')
                ->where('m.id', '=', $query['mission_id'])
                ->get()
                ->first()
            ;

            $res = '[' . $row->name . ']' . '<br>';

            $res .= '描述=' . $row->description . '<br>';

//            $requirements = json_decode($row->requirements);


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
}
