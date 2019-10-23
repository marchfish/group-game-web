<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // 'throttle:60,1',
            // 'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // 'auth'          => \App\Http\Middleware\Authenticate::class,
        // 'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        // 'bindings'      => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // 'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        // 'can'           => \Illuminate\Auth\Middleware\Authorize::class,
        // 'guest'         => \App\Http\Middleware\RedirectIfAuthenticated::class,
        // 'signed'        => \Illuminate\Routing\Middleware\ValidateSignature::class,
        // 'verified'      => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'throttle'               => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'debug_input'            => \App\Http\Middleware\DebugInput::class,
        'debug_sql'              => \App\Http\Middleware\DebugSql::class,
        'format_paginate'        => \App\Http\Middleware\FormatPaginate::class,
        'api.start_app_session'  => \App\Http\Middleware\Api\StartAppSession::class,
        'api.app.check_login'    => \App\Http\Middleware\Api\App\CheckLogin::class,
        'api.zpp.check_login'    => \App\Http\Middleware\Api\ZPP\CheckLogin::class,
        'api.pp.check_login'     => \App\Http\Middleware\Api\PP\CheckLogin::class,
        'wechat.auth_base'       => \App\Http\Middleware\Wechat\AuthBase::class,
        'wechat.auth_userinfo'   => \App\Http\Middleware\Wechat\AuthUserinfo::class,
        'web.api_token'          => \App\Http\Middleware\Web\ApiToken::class,
        'web.check_login'        => \App\Http\Middleware\Web\CheckLogin::class,
        'web.check_hp'           => \App\Http\Middleware\Web\CheckHP::class,
        'web.check_on_hook'      => \App\Http\Middleware\Web\CheckOnHook::class,
        'web.check_vip'          => \App\Http\Middleware\Web\CheckVip::class,
        'admin.check_login'      => \App\Http\Middleware\Admin\CheckLogin::class,
        'admin.check_permission' => \App\Http\Middleware\Admin\CheckPermission::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // \App\Http\Middleware\Authenticate::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
