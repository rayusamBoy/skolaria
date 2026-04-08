<?php

/*
|--------------------------------------------------------------------------
| Roles Configuration
|--------------------------------------------------------------------------
| This file defines the roles for both central and tenant contexts.
| Constants are used for role keys and are used internally in the system. 
| Changing the values (names) is safe, but keys should remain constant.
|
| Important: if you change the central root role name, make sure to update
| any middleware (like in config/log-viewer.php) that depends on that role.
*/

// --------------------
// Role Key Constants
// --------------------
// These constants prevent typos and keep role keys consistent across the app.
const ROLE_ROOT = 'root';
const ROLE_SUPERADMIN = 'superadmin';
const ROLE_ADMIN = 'admin';
const ROLE_TEACHER = 'teacher';
const ROLE_ACCOUNTANT = 'accountant';
const ROLE_LIBRARIAN = 'librarian';
const ROLE_PARENT = 'parent';
const ROLE_STUDENT = 'student';

// --------------------
// Central Roles
// --------------------
// Central roles are for the main system (non-tenant context).
$centralRoot = [
    ROLE_ROOT => 'Root', // The main superuser
];

// --------------------
// Tenant Roles
// --------------------
// Tenant roles are for individual tenants
$tenantRoot = [
    ROLE_SUPERADMIN => 'Super Admin', // Tenant superuser
];

// Staff roles
$tenantStaff = [
    ROLE_SUPERADMIN => 'Super Admin',
    ROLE_ADMIN => 'Admin',
    ROLE_TEACHER => 'Teacher',
    ROLE_ACCOUNTANT => 'Accountant',
    ROLE_LIBRARIAN => 'Librarian',
];

// Default roles
$tenantDefault = [
    ...$tenantStaff,
    ROLE_PARENT => 'Parent',
    ROLE_STUDENT => 'Student'
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
