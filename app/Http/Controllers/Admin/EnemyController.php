<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use App\Models\Item;

class EnemyController extends Controller
{
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    'i.*',
                    DB::raw('IFNULL(`d`.`value`, "") AS `type_name`'),
                ])
                ->from('item AS i')
                ->leftJoin('dict AS d', function ($join) {
                    $join
                        ->on('d.key', '=', 'i.type')
                        ->where('d.col', '=', 'item.type')
                    ;
                })
                ->orderBy('i.id', 'desc')
            ;

            if (isset($query['name'])) {
                $model->where('i.name', 'like', '%'. $query['name'] . '%');
            }

            if (isset($query['type_name'])) {
                $model->where('d.value', 'like', '%'. $query['type_name'] . '%');
            }

            if (isset($query['level'])) {
                $model->where('i.level', '=', $query['level']);
            }

            if (isset($query['item_id'])) {
                $model->where('i.id', '=', $query['item_id']);
            }

            $paginate = $model->paginate($query['size']);

            foreach ($paginate->items() as $item) {
                $content = json_decode($item->content)[0];
                $str = '';

                if (!$content) {
                    continue ;
                }

                foreach ($content as $k => $v) {
                    if ($k == 'type'){
                        $str .= '属性：' . Item::englishToChinese($v) . '，';
                    }else {
                        if ($item->type == 3){
                            $str .= Item::englishToChinese($k) . '：' . $v . '%，';
                        }else{
                            $str .= Item::englishToChinese($k) . '：' . $v . '，';
                        }
                    }
                }

                $item->content = $str;
            }

            return Response::view('admin/item/index', [
                'paginate' => $paginate,
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
