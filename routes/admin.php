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

// 测试
Route::get('index', 'SystemController@index');
Route::get('test', 'SystemController@test');
//Route::get('test1', 'SystemController@test1');
//Route::get('test2', 'SystemController@test2');
//Route::get('test3', 'SystemController@test3');

// 设置level
//Route::get('level', 'PublicController@level');

// 设置装备
//Route::get('equip', 'PublicController@equip');
//Route::get('equip', 'SystemController@equip');

// 设置法宝
//Route::get('magic-weapon', 'SystemController@magicWeapon');

// 设置怪物
//Route::get('enemy', 'SystemController@enemy');