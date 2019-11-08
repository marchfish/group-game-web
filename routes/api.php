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

// 注册
Route::get('register', 'PublicController@registerCreate');

Route::middleware(['api.check_token_qq'])->group(function () {
    // 结束挂机
    Route::get('end-hook', 'UserVipController@endHook');
    // 排行榜
    Route::get('ranking', 'GameController@ranking');

    // 彩票
    Route::middleware(['check_lottery'])->prefix('lottery')->group(function () {
        // 显示
        Route::get('', 'GameController@lotteryShow');
        // 购买
        Route::get('buy', 'GameController@lotteryBuy');
    });

    Route::middleware(['api.check_on_hook'])->group(function () {
        // 复活
        Route::get('revive', 'GameController@revive');

        // 用户
        Route::prefix('user')->group(function () {
            // 角色
            Route::prefix('role')->group(function () {
                // 状态
                Route::get('', 'UserRoleController@userRoleStatus');

                // 背包
                Route::prefix('knapsack')->group(function () {
                    // 显示物品
                    Route::get('', 'UserKnapsackController@show')->middleware(['format_paginate']);
                });
            });
        });

        // 装备
        Route::prefix('equip')->group(function () {
            // 显示
            Route::get('', 'EquipController@show');
            // 装备
    //        Route::post('', 'EquipController@equip');
            // 卸下装备
    //        Route::get('unequip', 'EquipController@unEquip');
        });

        // 商店
        Route::prefix('shop')->group(function () {
            // 拍卖行
            Route::prefix('business')->group(function () {
                // 显示拍卖行物品
                Route::get('', 'ShopBusinessController@show')->middleware(['format_paginate']);
            });

            // 显示可出售的物品
    //        Route::get('sell-show', 'ShopBusinessController@sellShow');
            // 出售物品
    //        Route::get('sell', 'ShopBusinessController@sell');
            // 购买物品
    //        Route::get('buy', 'ShopBusinessController@buy');
            // 下架物品
    //        Route::get('unsell', 'ShopBusinessController@unSell');

        });

        // 排位
        Route::middleware(['api.check_rank'])->prefix('rank')->group(function () {
            // 显示
            Route::get('', 'RankController@show');
    //        // 查看奖励
    //        Route::get('reward', 'RankController@reward');
            // 挑战
            Route::get('challenge', 'RankController@challenge');
        });

        // 物品
        Route::middleware(['api.check_hp'])->prefix('item')->group(function () {
            // 使用物品
            Route::get('use', 'ItemController@useItem');
    //        // 显示物品
    //        Route::get('recycle-show', 'ItemController@recycleItemShow');
            // 回收
            Route::get('recycle', 'ItemController@recycle');
    //        // 查看物品
    //        Route::get('check', 'ItemController@check');
    //        // 快速使用血瓶
    //        Route::get('user-blood-bottle', 'ItemController@useBloodBottle');
        });

        // 会员功能
        Route::middleware(['api.check_vip'])->group(function () {
            // 会员
            Route::prefix('vip')->group(function () {
                // 挂机
                Route::get('on-hook', 'UserVipController@onHook');
                // 自动攻击
    //            Route::middleware(['web.check_hp'])->get('auto-attack', 'GameController@attack');
                // 存入物品
    //            Route::get('warehouse-save', 'UserWarehouseController@create');
            });

            // 商城
            Route::prefix('shop/mall')->group(function () {
                // 显示
                Route::get('', 'ShopMallController@show');
                // 购买物品
                Route::get('buy', 'ShopMallController@buy');
            });
        });

        Route::middleware(['api.check_hp'])->prefix('game')->group(function () {
            // 位置信息
            Route::get('location', 'GameController@location');
            // 移动
            Route::get('move', 'GameController@move');
            // 攻击
            Route::get('attack', 'GameController@attack');
        });
    });
});

