<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenancyRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $default_guard = config('auth.defaults.guard');

        // Load roles from config
        $rootRoles = (array) array_values(config('roles.root_roles.tenant', []));
        $defaultRoles = (array) array_values(config('roles.default_roles.tenant', []));

        // Clean existing roles (safe delete)
        Role::query()->delete();

        // Create all default roles (these will include any root roles too if present in default list)
        $createdRoles = [];
        foreach ($defaultRoles as $roleName) {
            $roleName = (string) $roleName;
            $createdRoles[$roleName] = Role::create([
                'name' => $roleName,
                'guard_name' => $default_guard,
            ]);
        }

        // Grant all permissions to root roles
        $allPermModels = Permission::all();
        foreach ($rootRoles as $rootName) {
            $role = null;
            // case-insensitive match
            foreach ($createdRoles as $rName => $rObj) {
                if (mb_strtolower($rName) === mb_strtolower($rootName)) {
                    $role = $rObj;
                    break;
                }
            }
            if ($role) {
                $role->givePermissionTo($allPermModels);
            }
        }

        // Fetch all permission names present in this tenant DB
        $allPermissions = Permission::all()->pluck('name')->toArray();

        /**
         * Role -> list of regex patterns to match permission names.
         * Patterns are evaluated against the permission string; first match wins.
         *
         * NOTE: these keys are canonical role names used to choose which patterns to apply.
         * If your config contains slightly different spellings (e.g. "Libararian") the seeder
         * will match roles case-insensitively below.
         */
        $rolePatterns = [
            // Admin: broad tenant-level administration
            config('roles.default_roles.tenant.admin') => [
                '/.*/', // everything in the tenant permissions table
            ],

            // Teacher: exams, assessments, grades, marks, subjects, schedules, timetables, messages, student records, sections, divisions
            config('roles.default_roles.tenant.teacher') => [
                '/^exams?\\./',
                '/^assessments?\\./',
                '/^grades?\\./',
                '/^marks?\\./',
                '/^subjects?\\./',
                '/^schedules?\\./',
                '/^time_tables?\\./',
                '/^messages?\\./',
                '/^student_records?\\./',
                '/^sections?\\./',
                '/^divisions?\\./',
                '/^noticeboards?\\./',
                '/^notices?\\./',
                '/^reports?\\./',
            ],

            // Parent: primarily view-related permissions for their children's data, payments (view/pay), messages
            config('roles.default_roles.tenant.parent') => [
                '/^student_records?\\./',
                '/^payments?\\./',
                '/^messages?\\./',
                '/^books?\\./',
                '/^book_requests?\\./',
                '/^statistics?\\./',
                '/^reports?\\./',
                '/^marks?\\./',
                '/^schedules?\\./',
                '/^mies?\\./',
            ],

            // Student: view-only for own records, exams, marks, attendance-like info (if present), messaging and library
            config('roles.default_roles.tenant.student') => [
                '/^student_records?\\./',
                '/^exams?\\./',
                '/^marks?\\./',
                '/^subjects?\\./',
                '/^messages?\\./',
                '/^books?\\./',
                '/^book_requests?\\./',
                '/^time_tables?\\./',
                '/^schedules?\\./',
            ],

            // Librarian: manage books and book requests
            config('roles.default_roles.tenant.librarian') => [
                '/^books?\\./',
                '/^book_requests?\\./',
                '/^messages?\\./',
                '/^schedules?\\./',
            ],

            // Accountant: financial actions - payments, fees-like, reports, transactions if present
            config('roles.default_roles.tenant.accountant') => [
                '/^student_records?\\./',
                '/^payments?\\./',
                '/^fees?\\./',
                '/^transactions?\\./',
                '/^reports?\\./',
                '/^statistics?\\./',
                '/^schedules?\\./',
            ],
        ];

        // sensitive regexes to keep out of Admin (and other protected roles if desired)
        $sensitiveRegexes = [
            '/restore/i',
            '/force[_-]?delete/i',
            '/empty[_-]?soft[_-]?deleted/i',
            '/(bin|recycle|soft_deletes?)/i',
            '/super[_-]?admin/i',
            '/^central_/i',
        ];

        $filterSensitive = fn(array $perms) => array_values(array_filter($perms, function ($p) use ($sensitiveRegexes) {
            foreach ($sensitiveRegexes as $rx) {
                if (preg_match($rx, $p)) {
                    return false; // exclude this permission
                }
            }
            return true;
        }));

        // Helper: collect permission names matching any of the regex patterns
        $collectMatching = function (array $patterns) use ($allPermissions) {
            $matches = [];
            foreach ($allPermissions as $perm) {
                foreach ($patterns as $pat) {
                    // treat patterns as regular expressions
                    try {
                        if (preg_match($pat, $perm)) {
                            $matches[] = $perm;
                            break;
                        }
                    } catch (\Throwable $e) {
                        // if pattern invalid, skip it
                        continue;
                    }
                }
            }

            return array_values(array_unique($matches));
        };

        // Build a lowercase name -> Role instance map for robust matching (handles case differences / small typos)
        $rolesMap = [];
        foreach ($createdRoles as $rName => $rObj) {
            $rolesMap[mb_strtolower($rName)] = $rObj;
        }

        // Assign permissions for each pattern-defined role based on patterns
        foreach ($rolePatterns as $patternRoleName => $patterns) {
            // Find a matching created role by case-insensitive name
            $key = mb_strtolower($patternRoleName);
            $role = $rolesMap[$key] ?? null;

            if (!$role) {
                // try fuzzy match: look for roles whose lowercase contains the pattern role name (e.g. "libararian" vs "librarian")
                foreach ($rolesMap as $lowerName => $r) {
                    if (Str::contains($lowerName, $key) || Str::contains($key, $lowerName)) {
                        $role = $r;
                        break;
                    }
                }
            }

            if (!$role) {
                // If still not found, skip silently (role not configured for this tenant)
                continue;
            }

            // Skip root roles because they already have all permissions
            if (\in_array($role->name, $rootRoles, true)) {
                continue;
            }

            $permsForRole = $collectMatching($patterns);

            // Apply sensitive filter for Admin role (if this role maps to Admin)
            if (mb_strtolower($patternRoleName) === mb_strtolower(config('roles.default_roles.tenant.admin'))) {
                $permsForRole = $filterSensitive($permsForRole);
            }

            // Dry-run / print preview if seeding via artisan
            if (property_exists($this, 'command') && $this->command) {
                $this->command->info("Role: {$role->name} will get " . \count($permsForRole) . " permissions:");
                foreach ($permsForRole as $p) {
                    $this->command->line(" - {$p}");
                }
            }

            if (!empty($permsForRole)) {
                $role->givePermissionTo($permsForRole);
            }
        }
    }
}
