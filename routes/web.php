<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 后台
Route::prefix('admin')->namespace('Admin')->group(base_path('routes/admin.php'));

Route::namespace('Web')->group(function () {
    // 显示登录
    Route::get('', 'PublicController@loginShow');
    // 登录
    Route::post('login', 'PublicController@login');
    // 验证码
    Route::get('captcha', 'PublicController@captcha');
    // 显示注册
    Route::get('register', 'PublicController@registerShow');
    // 注册
    Route::post('register', 'PublicController@registerCreate');
    // 发送验证码
    Route::post('send-verify-code', 'PublicController@sendVerifyCode');
    // 协议说明页面
    Route::middleware(['web.check_login'])->group(function () {
        // 界面
        Route::get('index', 'GameController@index');
        //
        Route::prefix('game')->group(function () {
            // 位置信息
            Route::get('location', 'GameController@location');
            // 移动
            Route::get('move', 'GameController@move');
        });
    });
});
