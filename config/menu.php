<?php

return [

    'groups' => [

        [
            'id'    => 'dashboard',
            'title' => '',
            'items' => [
                [
                    'slug'       => 'dashboard',
                    'title'      => 'Dashboard',
                    'route'      => 'admin.dashboard',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                    'permissions' => ['list'],
                ],
            ],
        ],

        [
            'id'    => 'user_management',
            'title' => 'User Management',
            'items' => [
                [
                    'slug'       => 'user_management',
                    'title'      => 'Users',
                    'route'      => 'admin.users.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view', 'login'],
                ],
            ],
        ],

    ],

    'permissions' => [
        'list'   => 'View List',
        'create' => 'Create',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'view'   => 'View Details',
        'login'  => 'Login as User',
    ],

];
