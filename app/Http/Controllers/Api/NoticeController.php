<?php

namespace App\Http\Controllers\Api;

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
            $query = Request::all();

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
                ->paginate($query['size'])
            ;

            $res = '[告示] 共：' . $rows->lastPage() . '页' . '(' . $rows->currentPage() . ')'. '\r\n';

            foreach ($rows as $row) {
                $res .= '[' . $row->role_name . ']：' . $row->content . ' (' . date('m月d日', strtotime($row->created_at)). ')\r\n';
            }

            $res .= '\r\n发布告示：内容(15个字以内，1万金币/条，保存7天)';

            $res .= '\r\n翻页：告示 数字';

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

    public function create()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'content' => ['required', 'max:15'],
            ], [
                'content.required' => '内容不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $use_coin = 10000;

            if ($user_role->coin < $use_coin) {
                throw new InvalidArgumentException('您的金币不足：' . $use_coin, 400);
            }

            DB::beginTransaction();

            DB::table('user_role')
                ->where('id', '=', $user_role_id)
                ->update([
                    'coin' => DB::raw('`coin` - ' . $use_coin),
                ])
            ;

            DB::table('notice')
                ->insert([
                    'user_role_id' => $user_role_id,
                    'content'      => $query['content'],
                    'expired_at'   => date('Y-m-d H:i:s', strtotime('+7 days'))
                ])
            ;

            DB::commit();

            return Response::json([
                'code'    => 200,
                'message' => '发布成功！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function remove()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'content' => ['required', 'max:15'],
            ], [
                'content.required' => '内容不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role = $query['user_role'];

            $user_role_id = $user_role->id;

            $row = DB::query()
                ->select([
                    'n.*',
                ])
                ->from('notice AS n')
                ->where('n.content', 'like', '%' . $query['content'] . '%')
                ->where('n.user_role_id', '=', $user_role_id)
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('没有找到相关告示!', 400);
            }

            DB::table('notice')
                ->where('id', '=', $row->id)
                ->delete()
            ;

            return Response::json([
                'code'    => 200,
                'message' => '删除成功！',
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
