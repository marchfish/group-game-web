<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->group(function () {
    // 第三方回调
    Route::prefix('notify')->group(function () {
        // 微信公众号验证
        Route::get('wechat', 'NotifyController@wechatVerify');
        // 微信公众号回调
        Route::post('wechat', 'NotifyController@wechat');
        // 支付宝支付回调
        Route::post('alipay', 'NotifyController@alipay');
        // 顾客端微信支付回调
        Route::post('zpp-wxpay', 'NotifyController@zppWxpay');
        // 骑手端微信支付回调
        Route::post('pp-wxpay', 'NotifyController@ppWxpay');
        // 支付宝退款回调
        Route::post('alipay-refund', 'NotifyController@alipayRefund');
        // 顾客端微信退款回调
        Route::post('zpp-wxpay-refund', 'NotifyController@zppWxpayRefund');
        // 骑手端微信退款回调
        Route::post('pp-wxpay-refund', 'NotifyController@ppWxpayRefund');
    });

    Route::middleware(['api.start_app_session'])->group(function () {
        // 顾客端 v0
        Route::prefix('zpp/v0')->namespace('ZPP\V0')->group(base_path('routes/api/zpp/v0.php'));
        // 骑手端 v0
        Route::prefix('pp/v0')->namespace('PP\V0')->group(base_path('routes/api/pp/v0.php'));
        // app 通用 v0
        Route::prefix('app/v0')->namespace('App\V0')->group(base_path('routes/api/app/v0.php'));
    });
});
