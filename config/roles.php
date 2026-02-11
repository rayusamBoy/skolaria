<?php

/*
|--------------------------------------------------------------------------
| Roles configuration
|--------------------------------------------------------------------------
| This file contains the configuration for roles in both tenant and central contexts.
| All roles defined here are key-value pairs: the key is the role identifier
| and the value is the human-readable name of the role.
| Please do not modify the role keys, as they are used internally by the system.
| Only the values (names) can be changed to suit your application's needs.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant and Central root roles
    |--------------------------------------------------------------------------
    | Roles defined here have all permissions by default.
    */
    'root_roles' => [
        'central' => [
            'root' => 'Root',
        ],
        'tenant' => [
            'superadmin' => 'Super Admin',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant and Central default roles
    |--------------------------------------------------------------------------
    | These roles are created during the initial setup of the application.
    | They do not have all permissions by default (except for root roles).
    */
    'default_roles' => [
        'central' => [
            'root' => 'Root',
        ],
        'tenant' => [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
            'parent' => 'Parent',
            'student' => 'Student',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff roles
    |--------------------------------------------------------------------------
    | Define the roles that are considered staff members here.
    */
    'staff_roles' => [
        'central' => [
            'root' => 'Root',
        ],
        'tenant' => [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
        ]
    ]
];
