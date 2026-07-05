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

        [
            'id'    => 'ai_management',
            'title' => 'AI Management',
            'items' => [
                [
                    'slug'       => 'ai_management',
                    'title'      => 'AI System Prompt',
                    'route'      => 'admin.ai-prompt.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
                    'permissions' => ['list'],
                ],
                [
                    'slug'       => 'ai_image_prompt',
                    'title'      => 'AI Image Prompt',
                    'route'      => 'admin.ai-image-prompt.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                    'permissions' => ['list'],
                ],
            ],
        ],

        [
            'id'    => 'tenant_management',
            'title' => 'Tenant Management',
            'items' => [
                [
                    'slug'       => 'tenant_management',
                    'title'      => 'Tenants',
                    'route'      => 'admin.tenants.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete'],
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
