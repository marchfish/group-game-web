<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{
    public function show()
    {
        try {
            DB::table('notice')
                ->where('expired_at', '<=', date('Y-m-d H:i:s',time()))
                ->delete()
            ;

            $rows = DB::query()
                ->select([
                    'n.*',
                    'ur.name AS role_name',
                ])
                ->from('notice AS n')
                ->join('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'n.user_role_id')
                    ;
                })
                ->orderBy('n.created_at', 'desc')
                ->get()
            ;

            $res = '<div class="wr-color-E53E27">[告示]</div>';

            foreach ($rows as $row) {
                $res .= '[' . $row->role_name . ']：' . $row->content . ' (' . date('m月d日', strtotime($row->created_at)). ')<br>';
            }

            $res .= '<br><div class="wr-color-1490F7">发布告示：内容(15个字以内，1万金币/条，保存7天，需在群内发布!)</div>';

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
