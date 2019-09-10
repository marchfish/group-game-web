<?php

// 登录
Route::post('login', 'PublicController@login');
// 获取文章列表
Route::get('article', 'PublicController@article');

Route::middleware(['api.zpp.check_login'])->group(function () {
    // 退出登录
    Route::get('logout', 'IndexController@logout');
    // 统一支付
    Route::get('pay', 'IndexController@pay');
    // 获取消息
    Route::get('message', 'IndexController@message');
    // 获取邀请分享信息
    Route::get('invitation', 'PublicController@invitation');

    // 顾客信息
    Route::prefix('user')->group(function () {
        // 获取个人信息
        Route::get('', 'UserController@index');
        // 编辑资料
        Route::put('', 'UserController@edit');
        // 获取黑名单
        Route::get('blacklist', 'UserController@userBlackList');
        // 添加黑名单
        Route::post('blacklist', 'UserController@userBlackListCreate');
        // 删除黑名单
        Route::delete('blacklist', 'UserController@userBlackListDelete');
        // 获取跑跑信息
        Route::get('pp', 'UserController@ppUser');
        // 获取钱包充值记录-分页
        Route::get('deposit-in', 'UserController@depositIn')->middleware(['format_paginate']);
        // 获取跑跑实时位置
        Route::get('pp-location', 'UserController@ppLocation');
        // 获取常用地址
        Route::get('address', 'UserController@address');
        // 删除常用地址
        Route::delete('address', 'UserController@addressDel');
        // 获取顾客信息
        Route::get('zpp', 'UserController@zpp');
    });
    // 公司
    Route::prefix('group')->group(function () {
        // 获取公司列表
        Route::get('', 'GroupController@index')->middleware(['format_paginate']);

        // 签约
        Route::prefix('sign')->group(function () {
            // 签约公司
            Route::post('', 'GroupController@userGroupCreate');
            // 解除签约
            Route::delete('', 'GroupController@userGroupDelete');
        });
    });

    Route::prefix('order')->group(function () {
        // 获取自己的订单
        Route::get('', 'OrderController@index')->middleware(['format_paginate']);
        // 获取自己的订单数量
        Route::get('count', 'OrderController@count');
        // 下单
        Route::post('', 'OrderController@create');
        // 延长订单时限
        Route::post('live', 'OrderController@liveUpdate');
        // 获取订单竞价
        Route::get('bid', 'OrderController@bid');
        // 确认送达
        Route::post('check', 'OrderController@check');
        // 评论
        Route::post('review', 'OrderController@review');
        // 取消
        Route::post('cancel', 'OrderController@cancel');
        // 撤销取消
        Route::post('recover', 'OrderController@recover');
        // 同意取消
        Route::post('agree-cancel', 'OrderController@agreeCancel');
        // 获取投诉
        Route::get('report', 'OrderController@reportIndex');
        // 投诉
        Route::post('report', 'OrderController@reportCreate');
        // 隐藏
        Route::post('hide', 'OrderController@hide');
    });
});
