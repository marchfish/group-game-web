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
        'name'       => '用户管理',
        'path'       => null,
        'qs'         => null,
        'icon'       => 'fa-group',
        'controller' => 'UserController',
        'children'   => [
            [
                'name'       => '账号列表',
                'path'       => 'admin/user',
                'qs'         => null,
                'controller' => 'UserController',
            ],
            [
                'name'       => '角色列表',
                'path'       => 'admin/user/role',
                'qs'         => null,
                'controller' => 'UserRoleController',
            ],
            [
                'name'       => '装备列表',
                'path'       => 'admin/user/equip',
                'qs'         => null,
                'controller' => 'UserEquipController',
            ],
        ],
    ],
    [
        'name'       => '地图管理',
        'path'       => 'admin/map/index',
        'qs'         => null,
        'icon'       => 'fa-globe',
        'controller' => 'MapController',
    ],
    [
        'name'       => '物品管理',
        'path'       => 'admin/item',
        'qs'         => null,
        'icon'       => 'fa-archive',
        'controller' => 'ItemController',
    ],
    [
        'name'       => '怪物管理',
        'path'       => 'admin/enemy',
        'qs'         => null,
        'icon'       => 'fa-optin-monster',
        'controller' => 'EnemyController',
    ],
    [
        'name'       => '系统配置',
        'path'       => 'admin/sys-config',
        'qs'         => null,
        'icon'       => 'fa-wrench',
        'controller' => 'SysConfigController',
    ],
];
