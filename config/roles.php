<?php

/*
|--------------------------------------------------------------------------
| Roles Configuration
|--------------------------------------------------------------------------
| This file contains the configuration for roles in both tenant and central contexts.
| All roles defined here have specific key-value pairs where the key is the role identifier
| and the value is the human-readable name of the role.
| Please do not modify the keys of the roles as they are used internally by the system.
| Only the values (names) can be changed to suit your application's needs.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant and Central root roles
    |--------------------------------------------------------------------------
    | Roles defined here have all permissions by default.
    | They cannot be deleted or modified.
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
    | Roles defined here are created during the initial setup of the application.
    | These roles names can not be modified or deleted, but their permissions can be changed (except for root roles).
    | They do not have all permissions by default.
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
     | Here you may define the roles that are considered staff members.
     | 
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