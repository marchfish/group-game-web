<?php

return [
    [
        'name'       => '后台账号管理',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-user',
        'controller' => 'admin',
        'children'   => [
            [
                'name'       => '账号列表',
                'path'       => 'admin/admin_account',
                'qs'         => null,
                'controller' => 'AdminAccountController',
            ],
            [
                'name'       => '角色列表',
                'path'       => 'admin/admin_role',
                'qs'         => null,
                'controller' => 'AdminRoleController',
            ],
        ],
    ],
    [
        'name'       => '公司列表',
        'path'       => 'admin/group',
        'qs'         => null,
        'icon'       => 'fa-truck',
        'controller' => 'GroupController',
    ],
    [
        'name'       => '跑跑列表',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-users',
        'controller' => 'PPUserController',
        'children'   => [
            [
                'name'       => '跑跑管理',
                'path'       => 'admin/pp-user',
                'qs'         => null,
                'controller' => 'PPUserController',
            ],
            [
                'name'       => '头像审核',
                'path'       => 'admin/pp-user/verify/avatar',
                'qs'         => null,
                'controller' => 'PPUserController',
            ],
        ],
    ],
    [
        'name'       => '用户列表',
        'path'       => 'admin/user',
        'qs'         => null,
        'icon'       => 'fa-users',
        'controller' => 'UserController',
    ],
    [
        'name'       => '公告列表',
        'path'       => 'admin/sys-notice',
        'qs'         => null,
        'icon'       => 'fa-volume-up',
        'controller' => 'SysNoticeController',
    ],
    [
        'name'       => '微圈列表',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-user',
        'controller' => 'topic',
        'children'   => [
            [
                'name'       => '微圈管理',
                'path'       => 'admin/topic',
                'qs'         => null,
                'controller' => 'TopicController',
            ],
            [
                'name'       => '举报主题管理',
                'path'       => 'admin/topic/reportTopic',
                'qs'         => null,
                'controller' => 'TopicController',
            ],
            [
                'name'       => '举报评论管理',
                'path'       => 'admin/topic/reportComment',
                'qs'         => null,
                'controller' => 'TopicController',
            ],
        ],
    ],
    [
        'name'       => '提现列表',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-rmb',
        'controller' => 'WithdrawController',
        'children'   => [
            [
                'name'       => '公司提现',
                'path'       => 'admin/withdraw/group',
                'qs'         => null,
                'controller' => 'WithdrawController',
            ],
            [
                'name'       => '跑跑提现',
                'path'       => 'admin/withdraw/pp-user',
                'qs'         => null,
                'controller' => 'WithdrawController',
            ],
        ],
    ],
    [
        'name'       => '订单列表',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-shopping-cart',
        'controller' => 'OrderController',
        'children'   => [
            [
                'name'       => '订单管理',
                'path'       => 'admin/order',
                'qs'         => null,
                'controller' => 'OrderController',
            ],
            [
                'name'       => '订单投诉',
                'path'       => 'admin/order/report',
                'qs'         => null,
                'controller' => 'OrderController',
            ],
        ],
    ],
    [
        'name'       => '系统配置',
        'path'       => 'admin/sys-config',
        'qs'         => null,
        'icon'       => 'fa-wrench',
        'controller' => 'SysConfigController',
    ],
];
