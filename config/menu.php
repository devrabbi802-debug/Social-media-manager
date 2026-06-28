<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Each menu group has:
    |   - id: unique key
    |   - title: sidebar section title
    |   - items: array of menu items
    |       - slug: unique permission key (e.g. 'user_management')
    |       - title: display name
    |       - route: route name
    |       - icon: SVG path
    |       - permissions: array of actions ['list','create','edit','delete','view']
    |
    */

    'groups' => [

        [
            'id'    => 'dashboard',
            'title' => '',
            'items' => [
                [
                    'slug'       => 'dashboard',
                    'title'      => 'ড্যাশবোর্ড',
                    'route'      => 'admin.dashboard',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                    'permissions' => ['list'],
                ],
            ],
        ],

        [
            'id'    => 'user_management',
            'title' => 'ইউজার ম্যানেজমেন্ট',
            'items' => [
                [
                    'slug'       => 'user_management',
                    'title'      => 'ইউজার লিস্ট',
                    'route'      => 'admin.users.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
                ],
            ],
        ],

        [
            'id'    => 'lead_management',
            'title' => 'লিড ম্যানেজমেন্ট',
            'items' => [
                [
                    'slug'       => 'lead_management',
                    'title'      => 'লিড লিস্ট',
                    'route'      => 'admin.leads.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
                ],
            ],
        ],

        [
            'id'    => 'inventory',
            'title' => 'ইনভেন্টরি',
            'items' => [
                [
                    'slug'       => 'inventory',
                    'title'      => 'ইনভেন্টরি লিস্ট',
                    'route'      => 'admin.inventory.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
                ],
            ],
        ],

        [
            'id'    => 'whatsapp',
            'title' => 'হোয়াটসঅ্যাপ',
            'items' => [
                [
                    'slug'       => 'whatsapp_management',
                    'title'      => 'হোয়াটসঅ্যাপ ম্যানেজমেন্ট',
                    'route'      => 'admin.whatsapp.index',
                    'icon'       => '<path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
                ],
            ],
        ],

        [
            'id'    => 'facebook',
            'title' => 'ফেসবুক',
            'items' => [
                [
                    'slug'       => 'facebook_management',
                    'title'      => 'ফেসবুক ম্যানেজমেন্ট',
                    'route'      => 'admin.facebook.index',
                    'icon'       => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>',
                    'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
                ],
            ],
        ],

        [
            'id'    => 'settings',
            'title' => 'সেটিংস',
            'items' => [
                [
                    'slug'       => 'settings',
                    'title'      => 'সেটিংস',
                    'route'      => 'admin.settings.index',
                    'icon'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'permissions' => ['list', 'edit'],
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Labels (Bangla)
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'list'   => 'লিস্ট দেখা',
        'create' => 'তৈরি করা',
        'edit'   => 'এডিট করা',
        'delete' => 'ডিলিট করা',
        'view'   => 'দেখা',
    ],

];
