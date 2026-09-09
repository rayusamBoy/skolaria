<?php

use App\Enums\RoleKey;

/*
|--------------------------------------------------------------------------
| Roles Configuration
|--------------------------------------------------------------------------
| This file defines the roles for both central and tenant contexts.
| Enums constants are used for role keys and are used internally in the system.
| Changing the values (names) is safe, but keys should remain constant.
|
| Important: if you change the central root role name, make sure to update
| any middleware (like in config/log-viewer.php) that depends on that role.
*/

// --------------------
// Central Roles
// --------------------
// Central roles are for the main system (central context).
$centralRoot = [
    RoleKey::ROOT->value => 'Root', // The main superuser
];

// --------------------
// Tenant Roles
// --------------------
// Tenant roles are for individual tenants
$tenantRoot = [
    RoleKey::SUPERADMIN->value => 'Super Admin', // Tenant superuser
];

// Staff roles
$tenantStaff = [
    RoleKey::SUPERADMIN->value => 'Super Admin',
    RoleKey::ADMIN->value => 'Admin',
    RoleKey::TEACHER->value => 'Teacher',
    RoleKey::ACCOUNTANT->value => 'Accountant',
    RoleKey::LIBRARIAN->value => 'Librarian',
];

// Default roles
$tenantDefault = [
    ...$tenantStaff,
    RoleKey::PARENT->value => 'Parent',
    RoleKey::STUDENT->value => 'Student',
];

return [

    /*
    |--------------------------------------------------------------------------
    | Root Roles
    |--------------------------------------------------------------------------
    | Roles that have all permissions by default.
    | Usually used for access checks, gates, and full administrative privileges.
    */
    'root_roles' => [
        'central' => $centralRoot,
        'tenant' => $tenantRoot,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    | Roles created during initial setup. They may not have all permissions.
    | Includes root roles by default.
    */
    'default_roles' => [
        'central' => $centralRoot,
        'tenant' => $tenantDefault,
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff Roles
    |--------------------------------------------------------------------------
    | Roles considered staff members.
    | Typically a subset of default roles that have staff information.
    */
    'staff_roles' => [
        'central' => $centralRoot,
        'tenant' => $tenantStaff,
    ],
];
