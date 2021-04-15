<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserRoleController extends Controller
{
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    'ur.*'
                ])
                ->from('user_role AS ur')
                ->orderBy('ur.level', 'desc')
            ;

            if (isset($query['name'])) {
                $model->where('ur.name', 'like', '%'. $query['name'] . '%');
            }

            if (isset($query['level'])) {
                $model->where('ur.level', 'like', '%'. $query['level'] . '%');
            }

            if (isset($query['user_id'])) {
                $model->where('ur.user_id', '=', $query['user_id']);
            }

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/user-role/index', [
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
