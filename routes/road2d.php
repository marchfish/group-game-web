<?php
// 登录
Route::post('login', 'PublicController@login');
// 退出登录
Route::get('logout', 'PublicController@logout');
// 验证码
Route::get('captcha', 'PublicController@captcha');
// 发送信息
Route::post('message', 'PublicController@sendMessage');


