<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Closure;
use InvalidArgumentException;

class FormatPaginate
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $query = $request->all();

            $validator = Validator::make($query, [
                'page' => ['nullable', 'integer', 'min:1'],
                'size' => ['nullable', 'integer', 'min:1', 'max:500'],
            ]);

            if ($validator->fails()) {
                throw new InvalidArgumentException($validator->errors()->first(), 400);
            }

            $page = (int) ($query['page'] ?? 1);
            $size = (int) ($query['size'] ?? 10);

            $request->merge([
                'page'   => $page,
                'size'   => $size,
                'limit'  => $size,
                'offset' => ($page - 1) * $size,
            ]);

            return $next($request);
        } catch (InvalidArgumentException $e) {
            return Response::json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
