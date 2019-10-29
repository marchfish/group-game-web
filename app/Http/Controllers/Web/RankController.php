<?php

namespace App\Http\Controllers\Web;

use App\Models\Item;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class RankController extends Controller
{
    public function show()
    {
        try {
            $rows = DB::query()
                ->select([
                    'r.*',
                    DB::raw('IFNULL(`ur`.`name`, "虚位以待") AS user_role_name'),
                ])
                ->from('rank AS r')
                ->leftJoin('user_role AS ur', function ($join) {
                    $join
                        ->on('ur.id', '=', 'r.user_role_id')
                    ;
                })
                ->orderBy('r.num', 'asc')
                ->get()
            ;

            $res = '<div class="wr-color-E53E27">[排位]</div>';

            foreach ($rows as $row) {
                $res .=  $row->user_role_name . ' ：第' . $row->num . '名<br>';
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

    public function reward()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'rank_id' => ['required'],
            ], [
                'rank_id.required' => 'rank_id不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $user_role_id = Session::get('user.account.user_role_id');

            $row = DB::query()
                ->select([
                    's.*',
                    'i.name AS item_name',
                ])
                ->from('synthesis AS s')
                ->join('item AS i', function ($join) {
                    $join
                        ->on('i.id', '=', 's.item_id')
                    ;
                })
                ->where('s.id', '=', $query['synthesis_id'])
                ->get()
                ->first()
            ;

            if (!$row) {
                throw new InvalidArgumentException('找不到该合成', 400);
            }

            $res = '[' . $row->item_name . ']' . '<br>';

            $res .= '所需物品=';

            // 所需物品
            $requirements = json_decode($row->requirements);

            $ids = array_column($requirements,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');

            foreach ($requirements as $requirement) {
                $requirement->name = $items[$requirement->id] ?? '？？？';

                $res .= $requirement->name . '*' . $requirement->num . '，';
            }

            $res = rtrim($res, "，") . '<br>';

            // 失败保留
            $retains = json_decode($row->retains);

            $ids = array_column($retains,'id');

            $items = Item::getItems($ids);

            $items = array_column(obj2arr($items), 'name','id');
            $res .= '失败保留物品=';

            foreach ($retains as $retain) {
                $retain->name = $items[$retain->id] ?? '？？？';
                $res .= $retain->name . '*' . $retain->num . '，';
            }

            $res = rtrim($res, "，") . '<br>';

            $res .= '成功机率=' . $row->success_rate . '% <br>';

            $res .= '<input type="button" class="action" data-url="' . URL::to('synthesis/create') . "?synthesis_id=" . $row->id . '" value="合成" />' . '<br>';

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
