<?php

// 显示登录
Route::get('login', 'PublicController@loginShow');
// 验证码
Route::get('captcha', 'PublicController@captcha');
// 登录
Route::post('login', 'PublicController@login');
// 退出登录
Route::get('logout', 'PublicController@logout');

Route::middleware(['admin.check_login', 'admin.check_permission'])->group(function () {
    // 首页
    Route::get('', 'IndexController@index');
});
