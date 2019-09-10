<?php

Route::get('test', 'PublicController@test'); //->middleware(['debug_sql']);
// 文件上传
Route::post('upload', 'PublicController@upload');
// 发送短信验证码
Route::post('verify-code', 'PublicController@verifyCode');
// 验证短信验证码
Route::post('check-verify-code', 'PublicController@checkVerifyCode');
// 检查手机号是否注册
Route::post('check-tel', 'PublicController@checkTel');
// 检查支付单是否支付成功
Route::post('check-pay', 'PublicController@checkPay');
// 获取系统配置
Route::get('config', 'PublicController@config');
// 获取通知
Route::get('sys-notice', 'PublicController@sysNotice')->middleware(['format_paginate']);
//发现主题列表
Route::post('topicList', 'PublicController@TopicList');
//发现点赞列表
Route::post('topicAgreeList', 'TopicController@TopicAgreeList');
//发现评论列表
Route::post('topicCommentList', 'TopicController@TopicCommentList');
//发现举报列表
Route::post('topicReportList', 'TopicController@TopicReportList');
// 获取正在进行的订单（广场）
Route::get('order-work', 'PublicController@orderWork');
// 隐私协议
Route::get('privacy', 'PublicController@privacy');
// 获取跑跑评价列表
Route::get('pp-review', 'PublicController@ppReview')->middleware(['format_paginate']);

Route::middleware(['api.app.check_login'])->group(function () {
    // 绑定/更换手机号码
    Route::post('bind-tel', 'IndexController@bindTel');
    // 意见反馈
    Route::post('advice', 'IndexController@advice');
    // 更新推送token
    Route::post('push-token', 'IndexController@pushToken');
    // 绑定第三方
    Route::post('bind-third', 'IndexController@bindThird');

    Route::middleware([])->group(function () {
        //发布主题
        Route::post('topicPost', 'TopicController@TopicPost');
        //点赞
        Route::post('agreePost', 'TopicController@AgreePost');
        //评论
        Route::post('commentPost', 'TopicController@CommentPost');
        //删除主题
        Route::post('topicDelete', 'TopicController@TopicDelete');
        //取消点赞
        Route::post('agreeDelete', 'TopicController@AgreeDelete');
        //删除评论
        Route::post('commentDelete', 'TopicController@CommentDelete');
        //举报主题
        Route::post('topicReportPost', 'TopicController@TopicReportPost');
        //举报
        Route::post('commentReportPost', 'TopicController@CommentReportPost');
        //获取未读消息数
        Route::post('topicReadCount', 'TopicController@TopicReadCount');
        //查看个人微圈圈所有消息
        Route::post('topicNewsList', 'TopicController@TopicNewsList');
    });
});
