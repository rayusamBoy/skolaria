<?php

namespace App\Enums;

/**
 * RoleKey enum defines the keys for different roles in the system.
 * These keys are used internally for role management and should remain constant.
 */
enum RoleKey: string
{
    case ROOT = 'root';
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case TEACHER = 'teacher';
    case ACCOUNTANT = 'accountant';
    case LIBRARIAN = 'librarian';
    case PARENT = 'parent';
    case STUDENT = 'student';
}
