<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Viewports
    |--------------------------------------------------------------------------
    */
    'viewports' => [
        'desktop' => ['width' => 1280, 'height' => 720],
        'mobile' => ['width' => 375, 'height' => 812],
    ],

    /*
    |--------------------------------------------------------------------------
    | Output directory
    |--------------------------------------------------------------------------
    */
    'output_directory' => public_path('images/screenshots/'),

    /*
    |--------------------------------------------------------------------------
    | Login URLs (relative to base URL)
    |--------------------------------------------------------------------------
    */
    'central_login_url' => '/login',
    'tenant_login_url' => '/login',

    /*
    |--------------------------------------------------------------------------
    | Delays (milliseconds)
    |--------------------------------------------------------------------------
    */
    'post_login_wait_ms' => 4000,
    'post_navigate_wait_ms' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Pre‑defined credentials
    |--------------------------------------------------------------------------
    */
    'credentials' => [
        'superadmin' => [
            'identity' => env('PWA_SUPERADMIN_IDENTITY', 'superadmin@sms.com'),
            'password' => env('PWA_SUPERADMIN_PASSWORD', 'superadmin'),
        ],
        'admin' => [
            'identity' => env('PWA_ADMIN_IDENTITY', 'admin@sms.com'),
            'password' => env('PWA_ADMIN_PASSWORD', 'admin'),
        ],
        'teacher' => [
            'identity' => env('PWA_TEACHER_IDENTITY', 'teacher@sms.com'),
            'password' => env('PWA_TEACHER_PASSWORD', 'teacher'),
        ],
        'accountant' => [
            'identity' => env('PWA_ACCOUNTANT_IDENTITY', 'accountant@sms.com'),
            'password' => env('PWA_ACCOUNTANT_PASSWORD', 'accountant'),
        ],
        'librarian' => [
            'identity' => env('PWA_LIBRARIAN_IDENTITY', 'librarian@sms.com'),
            'password' => env('PWA_LIBRARIAN_PASSWORD', 'librarian'),
        ],
        'parent' => [
            'identity' => env('PWA_PARENT_IDENTITY', 'parent@sms.com'),
            'password' => env('PWA_PARENT_PASSWORD', 'parent'),
        ],
        'student' => [
            'identity' => env('PWA_STUDENT_IDENTITY', 'student@sms.com'),
            'password' => env('PWA_STUDENT_PASSWORD', 'student'),
        ],
        'rootuser' => [
            'identity' => env('PWA_ROOTUSER_IDENTITY', 'rootuser@sms.com'),
            'password' => env('PWA_ROOTUSER_PASSWORD', 'root'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Screenshot jobs
    |--------------------------------------------------------------------------
    */
    'jobs' => [

        // ==================================================================
        // Superadmin (tenant)
        // ==================================================================
        [
            'name' => 'superadmin',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'superadmin',
            'pages' => [
                [
                    'route' => 'dashboard',
                    'output' => 'tenancy/superadmin-dashboard-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'students.create',
                    'output' => 'tenancy/superadmin-admit-student-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'users.index',
                    'output' => 'tenancy/superadmin-manage-users-light.png',
                    'viewport' => 'desktop',
                ],
            ],
        ],

        // ==================================================================
        // Teacher (tenant)
        // ==================================================================
        [
            'name' => 'teacher',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'teacher',
            'pages' => [
                [
                    'route' => 'dashboard',
                    'output' => 'tenancy/teacher-dashboard-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'dashboard',
                    'output' => 'tenancy/narrow-teacher-dashboard-light.png',
                    'viewport' => 'mobile',
                ],
                [
                    'route' => 'marks.batch',
                    'output' => 'tenancy/teacher-batch-marks-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'message_threads.index',
                    'output' => 'tenancy/teacher-messaging-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'statistics.index',
                    'output' => 'tenancy/teacher-statistics-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
            ],
        ],

        // ==================================================================
        // Accountant (tenant)
        // ==================================================================
        [
            'name' => 'accountant',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'accountant',
            'pages' => [
                [
                    'route' => 'dashboard',
                    'output' => 'tenancy/narrow-accountant-dashboard-dark.png',
                    'viewport' => 'mobile',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'payments.manage',
                    'output' => 'tenancy/accountant-manage-payment-light.png',
                    'viewport' => 'desktop',
                ],
            ],
        ],

        // ==================================================================
        // Librarian (tenant)
        // ==================================================================
        [
            'name' => 'librarian',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'librarian',
            'pages' => [
                [
                    'route' => 'books.index',
                    'output' => 'tenancy/librarian-manage-books-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'dashboard',
                    'output' => 'tenancy/narrow-librarian-dashboard-light.png',
                    'viewport' => 'mobile',
                ],
            ],
        ],

        // ==================================================================
        // Parent (tenant)
        // ==================================================================
        [
            'name' => 'parent',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'parent',
            'pages' => [
                [
                    'route' => 'parent.my_children',
                    'output' => 'tenancy/parent-my-dashboard-light.png',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'parent.my_children',
                    'output' => 'tenancy/narrow-parent-my-children-light.png',
                    'viewport' => 'mobile',
                ],
                [
                    'route' => 'my_account.edit',
                    'output' => 'tenancy/parent-my-profile-dark.png',
                    'viewport' => 'desktop',
                    'theme' => 'dark',
                ],
                [
                    'route' => 'notifications.index',
                    'output' => 'tenancy/parent-notifications-light.png',
                    'viewport' => 'desktop',
                ],
            ],
        ],

        // ==================================================================
        // Student (tenant)
        // ==================================================================
        [
            'name' => 'student',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'credentials' => 'student',
            'pages' => [
                [
                    'route' => 'my_account.edit',
                    'output' => 'tenancy/student-my-account-light.png',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'timetables.index',
                    'output' => 'tenancy/narrow-student-manage-timetables-light.png',
                    'viewport' => 'mobile',
                ],
            ],
        ],

        // ==================================================================
        // Root user (central)
        // ==================================================================
        [
            'name' => 'rootuser',
            'context' => 'central',
            'credentials' => 'rootuser',
            'pages' => [
                [
                    'route' => 'dashboard',
                    'output' => 'non_tenancy/dashboard-dark.png',
                    'theme' => 'dark',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'dashboard',
                    'output' => 'non_tenancy/narrow-dashboard-light.png',
                    'viewport' => 'mobile',
                ],
                [
                    'route' => 'settings.index',
                    'output' => 'non_tenancy/system-settings-light.png',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'tenants.index',
                    'output' => 'non_tenancy/manage-tenants-dark.png',
                    'theme' => 'dark',
                    'viewport' => 'desktop',
                ],
            ],
        ],

        // ==================================================================
        // public – no auth
        // ==================================================================
        [
            'name' => 'login',
            'context' => 'central',
            'skip_auth' => true,
            'pages' => [
                [
                    'route' => 'login',
                    'output' => 'non_tenancy/login.png',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'login',
                    'output' => 'non_tenancy/narrow-login.png',
                    'viewport' => 'mobile',
                ],
            ],
        ],
        [
            'name' => 'login',
            'context' => 'tenant',
            'tenant_id' => env('PWA_TENANT_ID', 1),
            'skip_auth' => true,
            'pages' => [
                [
                    'route' => 'pages.terms_of_use',
                    'output' => 'tenancy/narrow-terms-of-use.png',
                    'viewport' => 'desktop',
                ],
                [
                    'route' => 'login',
                    'output' => 'tenancy/login.png',
                    'viewport' => 'desktop',
                ],
            ],
        ],
    ],
];
