<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;

class DocsController extends Controller
{
    public function index()
    {
        return Response::view('web/docs/index', [
            'files' => [
                '《找跑跑v4顾客端》接口文档.md',
                '《找跑跑v4骑手端》接口文档.md',
                '《找跑跑v4通用功能》接口文档.md',
            ],
        ]);
    }

    public function md()
    {
        try {
            $query = Request::all();

            $validator = Validator::make($query, [
                'file' => ['required'],
            ], [
                'file.required' => 'file 不能为空',
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $filePath = App::basePath('docs/md/' . $query['file']);

            if (!file_exists($filePath)) {
                throw new InvalidArgumentException('md 文件不存在', 400);
            }

            return Response::json([
                'code' => 200,
                'data' => [
                    'md' => file_get_contents($filePath),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
