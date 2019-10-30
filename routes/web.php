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
    // 退出登录
    Route::get('logout', 'PublicController@logout');
    // 验证码
    Route::get('captcha', 'PublicController@captcha');
    // 显示注册
    Route::get('register', 'PublicController@registerShow');
    // 注册
    Route::post('register', 'PublicController@registerCreate');
    // 显示修改密码
    Route::get('update', 'PublicController@passwordUpdateShow');
    // 修改密码
    Route::post('update', 'PublicController@passwordUpdate');
    // 发送验证码
    Route::post('send-verify-code', 'PublicController@sendVerifyCode');

    Route::middleware(['web.check_login'])->group(function () {
        // 界面
        Route::get('index', 'GameController@index');
        // 会员信息
        Route::get('vip-show', 'UserVipController@vipShow');
        // 结束挂机
        Route::get('end-hook', 'UserVipController@endHook');
        // 购买会员
        Route::get('vip-buy', 'UserVipController@vipBuy');
        // 排行榜
        Route::get('ranking', 'GameController@ranking');

        Route::middleware(['web.check_on_hook'])->group(function () {
            // 复活
            Route::get('revive', 'GameController@revive');

            Route::middleware(['web.check_hp'])->prefix('game')->group(function () {
                // 位置信息
                Route::get('location', 'GameController@location');
                // 移动
                Route::get('move', 'GameController@move');
                // 攻击
                Route::get('attack', 'GameController@attack');
            });
            // 任务
            Route::prefix('mission')->group(function () {
                // 显示任务
                Route::get('', 'MissionController@show');
                // 接受任务
                Route::get('accept', 'MissionController@accept');
                // 提交任务
                Route::get('submit', 'MissionController@submit');
                // 显示玩家当前任务
                Route::get('user', 'MissionController@userMissionShow');
            });
            // 背包
            Route::prefix('user-knapsack')->group(function () {
                // 显示物品
                Route::get('', 'UserKnapsackController@show');
            });
            // 物品
            Route::middleware(['web.check_hp'])->prefix('item')->group(function () {
                // 使用物品
                Route::get('use', 'ItemController@useItem');
                // 显示物品
                Route::get('recycle-show', 'ItemController@recycleItemShow');
                // 回收
                Route::get('recycle', 'ItemController@recycle');
                // 查看物品
                Route::get('check', 'ItemController@check');
                // 快速使用血瓶
                Route::get('user-blood-bottle', 'ItemController@useBloodBottle');
            });
            // 装备
            Route::prefix('equip')->group(function () {
                // 显示物品
                Route::get('', 'EquipController@show');
                // 装备
                Route::post('', 'EquipController@equip');
            });
            // 用户
            Route::prefix('user')->group(function () {
                // 角色
                Route::prefix('role')->group(function () {
                    // 状态
                    Route::get('', 'UserRoleController@userRoleStatus');
                });
            });
            // 地图
            Route::prefix('map')->group(function () {
                // 传送
                Route::get('transfer', 'MapController@transfer');
            });
            // 仓库
            Route::prefix('warehouse')->group(function () {
                // 显示仓库物品
                Route::get('', 'UserWarehouseController@show');
                // 显示可存入物品
                Route::get('user-knapsack-show', 'UserWarehouseController@userKnapsackItemShow');
                // 取出物品
                Route::get('take-out', 'UserWarehouseController@delete');
            });
            // 会员功能
            Route::middleware(['web.check_vip'])->group(function () {
                // 会员
                Route::prefix('vip')->group(function () {
                    // 挂机
                    Route::get('on-hook', 'UserVipController@onHook');
                    // 自动攻击
                    Route::get('auto-attack', 'GameController@attack');
                    // 存入物品
                    Route::get('warehouse-save', 'UserWarehouseController@create');
                });

                // 商城
                Route::prefix('shop-mall')->group(function () {
                    // 显示
                    Route::get('', 'ShopMallController@show');
                    // 购买物品
                    Route::get('buy', 'ShopMallController@buy');
                });
            });
            // 拍卖行
            Route::prefix('shop-business')->group(function () {
                // 显示拍卖行物品
                Route::get('', 'ShopBusinessController@show');
                // 显示可出售的物品
                Route::get('sell-show', 'ShopBusinessController@sellShow');
                // 出售物品
                Route::get('sell', 'ShopBusinessController@sell');
                // 购买物品
                Route::get('buy', 'ShopBusinessController@buy');
                // 下架物品
                Route::get('unsell', 'ShopBusinessController@unSell');
            });
            //合成
            Route::prefix('synthesis')->group(function () {
                // 显示列表
                Route::get('', 'SynthesisController@showAll');
                // 显示详情
                Route::get('show', 'SynthesisController@show');
                // 合成
                Route::get('create', 'SynthesisController@create');
            });
            // 商店
            Route::prefix('shop')->group(function () {
                // 显示
                Route::get('', 'ShopController@show');
                // 购买物品
                Route::get('buy', 'ShopController@buy');
            });
            // 排位
            Route::prefix('rank')->group(function () {
                // 显示
                Route::get('', 'RankController@show');
                // 查看奖励
                Route::get('reward', 'RankController@reward');
                // 挑战
                Route::get('challenge', 'RankController@challenge');
            });
        });
    });
});
