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
    // 后台账户列表
    Route::prefix('admin_account')->group(function () {
        // 显示列表
        Route::get('', 'AdminAccountController@index');
        // 显示新增
        Route::get('new', 'AdminAccountController@news');
        // 显示编辑
        Route::get('edit', 'AdminAccountController@edit');
        // 新增
        Route::post('', 'AdminAccountController@create');
        // 编辑
        Route::put('', 'AdminAccountController@update');
        // 删除
        Route::delete('', 'AdminAccountController@delete');
    });

    // 角色列表
    Route::prefix('admin_role')->group(function () {
        // 显示列表
        Route::get('', 'AdminRoleController@index');
        // 显示新增
        Route::get('new', 'AdminRoleController@news');
        // 显示编辑
        Route::get('edit', 'AdminRoleController@edit');
        // 新增
        Route::post('', 'AdminRoleController@create');
        // 编辑
        Route::put('', 'AdminRoleController@update');
        // 删除
        Route::delete('', 'AdminRoleController@delete');
    });

    // 用户管理
    Route::prefix('user')->group(function () {
        // 显示列表
        Route::get('', 'UserController@index')->middleware(['format_paginate']);
        // 角色管理
        Route::prefix('role')->group(function () {
            // 显示列表
            Route::get('', 'UserRoleController@index')->middleware(['format_paginate']);
        });
        // 装备管理
        Route::prefix('equip')->group(function () {
            // 显示列表
            Route::get('', 'UserEquipController@index')->middleware(['format_paginate']);
        });
    });

    // 地图列表
    Route::prefix('map')->group(function () {
        // 显示列表
        Route::get('index', 'MapController@index')->middleware(['format_paginate']);
        // 获取地图列表
        Route::get('', 'MapController@map');
        // 获取npc列表
        Route::get('npc', 'MapController@npc');
        // 获取怪物列表
        Route::get('enemy', 'MapController@enemy');
        // 显示新增
        Route::get('new', 'MapController@new');
        // 显示编辑
        Route::get('edit', 'MapController@edit');
        // 新增
        Route::post('', 'MapController@create');
        // 编辑
        Route::put('', 'MapController@update');
        // 删除
        Route::delete('', 'MapController@delete');
    });

    // 物品列表
    Route::prefix('item')->group(function () {
        // 获取地图列表
        Route::get('', 'ItemController@index')->middleware(['format_paginate']);
        // 获取npc列表
        Route::get('npc', 'MapController@npc');
        // 获取怪物列表
        Route::get('enemy', 'MapController@enemy');
        // 显示新增
        Route::get('new', 'MapController@new');
        // 显示编辑
        Route::get('edit', 'MapController@edit');
        // 新增
        Route::post('', 'MapController@create');
        // 编辑
        Route::put('', 'MapController@update');
        // 删除
        Route::delete('', 'MapController@delete');
    });
});

// 测试
Route::get('index', 'SystemController@index');
Route::get('test', 'SystemController@test');
Route::get('test1', 'SystemController@test1');
//Route::get('test2', 'SystemController@test2');
//Route::get('test3', 'SystemController@test3');

// 设置level
//Route::get('level', 'PublicController@level');
// 设置宠物level
//Route::get('level', 'PublicController@levelPets');

// 设置装备
//Route::get('equip', 'PublicController@equip');
//Route::get('equip', 'EquipController@equip');
//Route::get('equip', 'EquipController@colorEquip');

// 设置提炼
//Route::get('refine', 'SystemController@refine');

// 设置法宝
//Route::get('magic-weapon', 'SystemController@magicWeapon');

// 设置怪物
//Route::get('enemy', 'SystemController@enemy');

// 设置角色测试
//Route::get('user/role', 'SystemController@userRole');
//Route::get('user/knapsack', 'SystemController@userKnapsack');
