<?php

namespace App\Http\Middleware;

use Closure;

class DebugInput
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
        echo json_encode(([
            'header' => [
                'Content-Type' => $request->headers->get('content-type'),
            ],
            'body' => file_get_contents('php://input'),
        ]), JSON_UNESCAPED_UNICODE);

        exit;
    }
}
