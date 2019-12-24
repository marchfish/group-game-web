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
        // 开奖历史
        Route::get('history', 'GameController@lotteryHistory')->middleware(['format_paginate']);
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
            Route::get('up', 'EquipController@equip');
            // 卸下装备
            Route::get('down', 'EquipController@unEquip');
        });

        // 商店
        Route::prefix('shop')->group(function () {
            // 显示
            Route::get('', 'ShopController@show')->middleware(['format_paginate']);
            // 购买物品
            Route::get('buy', 'ShopController@buy');

            // 拍卖行
            Route::prefix('business')->group(function () {
                // 显示拍卖行物品
                Route::get('', 'ShopBusinessController@show')->middleware(['format_paginate']);
                // 出售物品
                Route::get('sell', 'ShopBusinessController@sell');
                // 下架物品
                Route::get('unsell', 'ShopBusinessController@unSell');
                // 购买物品
                Route::get('buy', 'ShopBusinessController@buy');
            });
        });

        // 排位
        Route::middleware(['api.check_rank'])->prefix('rank')->group(function () {
            // 显示
            Route::get('', 'RankController@show');
    //        // 查看奖励
            Route::get('reward', 'RankController@reward');
            // 挑战
            Route::get('challenge', 'RankController@challenge');
        });

        // 物品
        Route::middleware(['api.check_hp'])->prefix('item')->group(function () {
            // 使用物品
            Route::get('use', 'ItemController@useItem');
            // 回收
            Route::get('recycle', 'ItemController@recycle');
            // 查看物品
            Route::get('check', 'ItemController@check');
        });

        // 会员
        Route::prefix('vip')->group(function () {
            // 会员信息
            Route::get('', 'UserVipController@vipShow');
            // 购买会员
            Route::get('buy', 'UserVipController@vipBuy');
            // 会员功能
            Route::middleware(['api.check_vip'])->group(function () {
                // 挂机
                Route::get('on-hook', 'UserVipController@onHook');
                // 设置血量保护
                Route::get('protect', 'UserVipController@setProtectHp');
                // 存入物品
                Route::get('warehouse/save', 'UserWarehouseController@create');

                // 商城
                Route::prefix('shop/mall')->group(function () {
                    // 显示
                    Route::get('', 'ShopMallController@show');
                    // 购买物品
                    Route::get('buy', 'ShopMallController@buy');
                });
            });
        });

        //
        Route::middleware(['api.check_hp', 'api.check_map'])->prefix('game')->group(function () {
            // 位置信息
            Route::get('location', 'GameController@location');
            // 移动
            Route::get('move', 'GameController@move');
            // 攻击
            Route::get('attack', 'GameController@attack');
            // 改名
            Route::get('rename', 'GameController@rename');
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
            Route::get('history', 'MissionController@userMissionShow');
        });

        // 提炼
        Route::prefix('refine')->group(function () {
            // 显示列表
            Route::get('', 'RefineController@showAll')->middleware(['format_paginate']);
            // 显示详情
            Route::get('show', 'RefineController@show');
            // 提炼
            Route::get('create', 'RefineController@create');
        });

        //合成
        Route::prefix('synthesis')->group(function () {
            // 显示列表
            Route::get('', 'SynthesisController@showAll')->middleware(['format_paginate']);
            // 显示详情
            Route::get('show', 'SynthesisController@show');
            // 合成
            Route::get('create', 'SynthesisController@create');
        });

        //更换
        Route::prefix('change')->group(function () {
            // 显示列表
            Route::get('', 'ChangeController@showAll')->middleware(['format_paginate']);
            // 显示详情
            Route::get('show', 'ChangeController@show');
            // 更换
            Route::get('create', 'ChangeController@create');
        });

        // 地图
        Route::middleware(['api.check_hp'])->prefix('map')->group(function () {
            // 传送
            Route::get('transfer', 'MapController@transfer');
            // 活动地图
            Route::prefix('activity')->group(function () {
                // 展示
                Route::get('', 'MapController@activity');
                // 传送
                Route::get('transfer', 'MapController@activityTransfer');
            });
        });

        // 仓库
        Route::prefix('warehouse')->group(function () {
            // 显示仓库物品
            Route::get('', 'UserWarehouseController@show')->middleware(['format_paginate']);
            // 取出物品
            Route::get('out', 'UserWarehouseController@delete');
        });

        // 技能
        Route::middleware(['api.check_hp', 'api.check_map'])->prefix('skill')->group(function () {
            // 显示
            Route::get('', 'UserSkillController@show');
            // 学习
            Route::get('study', 'UserSkillController@study');
            // 使用技能
            Route::get('use', 'UserSkillController@usrSkill');
            // 设置快捷键
            Route::get('quick', 'UserSkillController@setQuick');
            // 遗忘
            Route::get('remove', 'UserSkillController@remove');
        });

        // pk
        Route::middleware(['api.check_hp'])->prefix('pk')->group(function () {
            // 邀请
            Route::get('invite', 'UserPKController@invite');
            // 接受
            Route::get('accept', 'UserPKController@accept');
            // pk
            Route::get('', 'UserPKController@pk');
            // 拒绝
            Route::get('refuse', 'UserPKController@refuse');
            // 认输
            Route::get('surrender', 'UserPKController@surrender');
            // 取消
            Route::get('cancel', 'UserPKController@cancel');
        });

        // 告示
        Route::prefix('notice')->group(function () {
            // 显示
            Route::get('', 'NoticeController@show')->middleware(['format_paginate']);
            // 创建
            Route::get('create', 'NoticeController@create');
            // 删除
            Route::get('remove', 'NoticeController@remove');
        });

        // 宠物
        Route::prefix('pets')->group(function () {
            // 显示
            Route::get('', 'UserPetsController@show');
            // 出战
            Route::get('fight', 'UserPetsController@fight');
            // 喂食
            Route::get('feed', 'UserPetsController@feed');
            // 放生
            Route::get('remove', 'UserPetsController@remove');
        });
    });
});

