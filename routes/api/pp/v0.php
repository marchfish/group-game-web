<?php

// 登录
Route::post('login', 'PublicController@login');

Route::middleware(['api.pp.check_login'])->group(function () {
    // 退出登录
    Route::get('logout', 'IndexController@logout');
    // 统一支付
    Route::get('pay', 'IndexController@pay');
    // 获取消息
    Route::get('message', 'IndexController@message');
    // 获取邀请分享信息
    Route::get('invitation', 'PublicController@invitation');

    // 跑跑信息
    Route::prefix('user')->group(function () {
        // 获取个人信息
        Route::get('', 'PpUserController@index');
        // 上传身份信息
        Route::post('', 'PpUserController@update');
        // 编辑资料
        Route::put('', 'PpUserController@edit');
        // 上传经纬度
        Route::post('location', 'PpUserController@locationCreate');
        // 上下班
        Route::post('work', 'PpUserController@workCreate');
        // 获取门票购买记录-分页
        Route::get('ticket', 'PpUserController@ticket')->middleware(['format_paginate']);
        // 获取门票是否过期
        Route::get('ticket-is-expired', 'PpUserController@ticketIsExpired');

        // 获取顾客信息
        Route::get('zpp', 'PpUserController@user');
        // 获取跑跑信息
        Route::get('pp', 'PpUserController@ppUser');
    });

    // 公司
    Route::prefix('group')->group(function () {
        // 获取appList
        Route::get('applist', 'GroupController@groupAppList');
        // 获取公司列表
        Route::get('', 'GroupController@index')->middleware(['format_paginate']);
        // 创建公司
        Route::post('', 'GroupController@create');
        // 解散公司
        Route::delete('', 'GroupController@delete');
        // 成员
        Route::prefix('user')->group(function () {
            // 获取成员列表
            Route::get('', 'GroupController@user')->middleware(['format_paginate']);
            // 申请加入公司
            Route::post('', 'GroupController@userCreate');
            // 撤销申请
            Route::delete('', 'GroupController@userDelete');
            // 申请离职
            Route::delete('quit', 'GroupController@userQuit');
            // 移除职员
            Route::delete('remove', 'GroupController@userRemove');
            // 同意入职
            Route::put('agree', 'GroupController@userAgree');
            // 拒绝申请
            Route::delete('refuse', 'GroupController@userRefuse');
            // 获取跑跑相关公司（申请与入职）
            Route::get('list', 'GroupController@groupUserList')->middleware(['format_paginate']);
            // 设置跑跑为加急
            Route::post('quick', 'GroupController@userQuick');
            // 取消跑跑加急
            Route::delete('quick', 'GroupController@disUserQuick');
        });
        // 签约的客户
        Route::get('sign', 'GroupController@userGroup')->middleware(['format_paginate']);
        // 获取公司历史订单
        Route::get('order', 'GroupController@order')->middleware(['format_paginate']);
        // 获取公司未提现订单
        Route::get('wait-withdraw-order', 'GroupController@waitWithdrawOrder')->middleware(['format_paginate']);
        // 获取个人团内未结算订单
        Route::get('wait-salary-order', 'GroupController@waitSalaryOrder')->middleware(['format_paginate']);
        // 获取公司余额
        Route::get('deposit', 'GroupController@groupDeposit');
    });

    Route::prefix('order')->group(function () {
        // 获取自己的订单
        Route::get('', 'OrderController@index')->middleware(['format_paginate']);
        // 获取自己的订单数量
        Route::get('count', 'OrderController@count');
        // 获取等待处理的订单
        Route::get('waiting', 'OrderController@waiting'); //->middleware(['debug_sql']);
        // 竞价
        Route::post('bid', 'OrderController@bidCreate');
        // 取消竞价
        Route::post('bid-cancel', 'OrderController@bidCancel');
        // 开跑
        Route::post('go', 'OrderController@go');
        // 送达
        Route::post('arrive', 'OrderController@arrive');
        // 转派
        Route::post('assign', 'OrderController@assign');
        // 接单
        Route::post('take', 'OrderController@take');
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
