<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserController extends Controller
{
    public function index()
    {
        try {
            $query = Request::all();

            $model = DB::query()
                ->select([
                    'u.*'
                ])
                ->from('user AS u')
                ->orderBy('u.created_at', 'desc')
            ;

            if (isset($query['username'])) {
                $model->where('u.username', 'like', '%'. $query['username'] . '%');
            }

            if (isset($query['nickname'])) {
                $model->where('u.nickname', 'like', '%'. $query['nickname'] . '%');
            }

            if (isset($query['qq'])) {
                $model->where('u.qq', 'like', '%'. $query['qq'] . '%');
            }

            if (isset($query['email'])) {
                $model->where('u.email', 'like', '%'. $query['email'] . '%');
            }

            $paginate = $model->paginate($query['size']);

            return Response::view('admin/user/index', [
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
