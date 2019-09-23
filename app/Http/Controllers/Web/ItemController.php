<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class ItemController extends Controller
{
    // 使用物品
    public function useItem()
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

            $res = '';

            $item = json_decode($row->content)[0];
            $item->id = $row->id;

            if ($row->type == 1) {
                $res .= Item::useDrug($item);
            }

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
