<?php

/**
 * Tenant-level role & permission registry (fully separate from central `config/menu.php`).
 *
 * Each tenant User may belong to a `Role` (table: tenant_roles) whose `permissions`
 * JSON column holds an array of "module.action" strings defined here.
 *
 * `slug` => sub-menu / module key used both in the sidebar filter and the route
 * middleware, and mirrored in `resources/views/layouts/tenant.blade.php`.
 *
 * A user with `role_id = null` is treated as the tenant SUPER ADMIN and bypasses all checks.
 */

return [

    'groups' => [

        // ---- Dashboard (top link) ----
        [
            'id' => 'dashboard',
            'title' => 'Dashboard',
            'items' => [
                ['slug' => 'dashboard', 'label' => 'Dashboard', 'permissions' => ['list']],
            ],
        ],

        // ---- User Management ----
        [
            'id' => 'user_management',
            'title' => 'User Management',
            'items' => [
                ['slug' => 'user_management', 'label' => 'Users & Roles', 'permissions' => ['list', 'create', 'edit', 'delete']],
            ],
        ],

        // ---- Inventory (sub-menu granular) ----
        [
            'id' => 'inventory',
            'title' => 'Inventory',
            'items' => [
                ['slug' => 'inventory_dashboard',      'label' => 'Inventory Dashboard', 'permissions' => ['list']],
                ['slug' => 'products',                 'label' => 'Products',            'permissions' => ['list', 'create', 'edit', 'delete']],
                ['slug' => 'categories',               'label' => 'Categories',          'permissions' => ['list', 'create', 'edit', 'delete']],
                ['slug' => 'brands',                   'label' => 'Brands',              'permissions' => ['list', 'create', 'edit', 'delete']],
                ['slug' => 'warehouses',               'label' => 'Warehouses',          'permissions' => ['list', 'create', 'edit', 'delete']],
                ['slug' => 'stock_movements',          'label' => 'Stock Movements',     'permissions' => ['list', 'create']],
                ['slug' => 'stock_transfers',          'label' => 'Stock Transfers',     'permissions' => ['list', 'create']],
                ['slug' => 'stock_alerts',             'label' => 'Stock Alerts',        'permissions' => ['list', 'create', 'edit', 'delete']],
                ['slug' => 'attributes',               'label' => 'Attributes',          'permissions' => ['list', 'create', 'edit', 'delete']],
            ],
        ],

        // ---- Orders ----
        [
            'id' => 'orders',
            'title' => 'Orders',
            'items' => [
                ['slug' => 'orders', 'label' => 'Orders', 'permissions' => ['list', 'view', 'edit', 'export', 'delete']],
            ],
        ],

        // ---- POS (sub-menu granular) ----
        [
            'id' => 'pos',
            'title' => 'Point of Sale',
            'items' => [
                ['slug' => 'pos_terminal', 'label' => 'POS Terminal',   'permissions' => ['list', 'create', 'hold']],
                ['slug' => 'pos_sales',    'label' => 'POS Sales',      'permissions' => ['list', 'view', 'refund']],
                ['slug' => 'pos_sessions', 'label' => 'Register Sessions', 'permissions' => ['list', 'create', 'close']],
                ['slug' => 'pos_reports',  'label' => 'POS Reports',    'permissions' => ['list']],
                ['slug' => 'pos_settings', 'label' => 'POS Settings',   'permissions' => ['list', 'edit']],
            ],
        ],

        // ---- Storefront Settings ----
        [
            'id' => 'web_setup',
            'title' => 'Storefront Settings',
            'items' => [
                ['slug' => 'storefront_settings', 'label' => 'Storefront Settings', 'permissions' => ['list', 'edit']],
            ],
        ],

        // ---- Panel Settings ----
        [
            'id' => 'settings',
            'title' => 'Panel Settings',
            'items' => [
                ['slug' => 'settings', 'label' => 'Business Setup Settings', 'permissions' => ['list', 'edit']],
            ],
        ],

        // ---- Integrations ----
        [
            'id' => 'integration',
            'title' => 'Integration',
            'items' => [
                ['slug' => 'integration', 'label' => 'Integration', 'permissions' => ['list', 'edit']],
            ],
        ],

        // ---- AI Setup ----
        [
            'id' => 'ai_setup',
            'title' => 'AI Setup',
            'items' => [
                ['slug' => 'ai_setup', 'label' => 'AI Setup', 'permissions' => ['list', 'edit']],
            ],
        ],

        // ---- Conversations ----
        [
            'id' => 'conversations',
            'title' => 'Conversations',
            'items' => [
                ['slug' => 'conversations', 'label' => 'Conversations', 'permissions' => ['list', 'reply']],
            ],
        ],

        // ---- Image Matching ----
        [
            'id' => 'image_match',
            'title' => 'Image Matching',
            'items' => [
                ['slug' => 'image_match', 'label' => 'Image Matching', 'permissions' => ['list']],
            ],
        ],
    ],

    'permissions' => [
        'list' => 'View',
        'view' => 'View Details',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
        'reply' => 'Reply',
        'refund' => 'Refund',
        'hold' => 'Hold Order',
        'close' => 'Close Session',
    ],

];
