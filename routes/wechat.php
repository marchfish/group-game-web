<?php

// 网页 snsapi_base 授权
Route::get('web-auth-base', 'PublicController@webAuthBase');
// 网页 snsapi_userinfo 授权
Route::get('web-auth-userinfo', 'PublicController@webAuthUserinfo');

Route::middleware(['wechat.auth_base'])->group(function () {
});

Route::middleware(['wechat.auth_userinfo'])->group(function () {
    // 微圈列表
    Route::get('topic', 'TopicController@shareTopic');
    // 绑定页面
    Route::get('user-bind', 'PublicController@userBind');
    // 绑定
    Route::post('user-bind', 'PublicController@userBindCreate');

    Route::post('commentPost', 'TopicController@CommentPost');
    Route::post('agreePost', 'TopicController@AgreePost');
});
