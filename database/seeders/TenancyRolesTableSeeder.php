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

        // Remove root roles from default roles – they are not seeded here
        $rolesToSeed = array_diff($defaultRoles, $rootRoles);

        if (empty($rolesToSeed)) {
            $this->command->info('No non‑root roles to seed.');
            return;
        }

        // Clean existing roles (safe delete – all roles will be recreated)
        Role::query()->delete();

        // Create only non‑root roles
        $createdRoles = [];
        foreach ($rolesToSeed as $roleName) {
            $roleName = (string) $roleName;
            $createdRoles[$roleName] = Role::create([
                'name' => $roleName,
                'guard_name' => $default_guard,
            ]);
        }

        // Fetch all permission names present in this tenant DB
        $allPermissions = Permission::all()->pluck('name')->toArray();

        /**
         * Role -> array with 'patterns' and optional 'exclude'.
         * Patterns are regexes to match permission names.
         * Exclude can be an array of regexes (or exact strings) to remove permissions.
         */
        $roleDefinitions = [
            // Admin: broad tenant-level administration (but not root)
            config('roles.default_roles.tenant.admin') => [
                'patterns' => ['/.*/'],
                'exclude' => [
                    '/restore/i',
                    '/force[_-]?delete/i',
                    '/empty[_-]?soft[_-]?deleted/i',
                    '/(bin|recycle|soft_deletes?)/i',
                    '/super[_-]?admin/i',
                    '/^central_/i',
                ],
            ],

            // Teacher: exams, assessments, grades, marks, subjects, schedules, timetables, messages, student records, sections, divisions
            config('roles.default_roles.tenant.teacher') => [
                'patterns' => [
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
                'exclude' => [
                    '/^messages\\.user_delete/',
                ],
            ],

            // Parent: primarily view-related permissions for their children's data, payments (view/pay), messages
            config('roles.default_roles.tenant.parent') => [
                'patterns' => [
                    '/^student_records?\\./',
                    '/^payments?\\./',
                    '/^messages?\\./',
                    '/^books?\\./',
                    '/^book_requests?\\./',
                    '/^statistics?\\./',
                    '/^reports?\\./',
                    '/^marks?\\./',
                    '/^schedules?\\./',
                    '/^childrens?\\./',
                ],
                'exclude' => [
                    '/^messages\\.delete/',
                    '/^student_records\\.(delete|update)/',
                ],
            ],

            // Student: view-only for own records, exams, marks, attendance-like info, messaging and library
            config('roles.default_roles.tenant.student') => [
                'patterns' => [
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
                'exclude' => [
                    '/^messages\\.(delete|update)/',
                    '/^student_records\\.(delete|update|create|store)/',
                ],
            ],

            // Librarian: manage books and book requests
            config('roles.default_roles.tenant.librarian') => [
                'patterns' => [
                    '/^books?\\./',
                    '/^book_requests?\\./',
                    '/^messages?\\./',
                    '/^schedules?\\./',
                ],
                'exclude' => [
                    '/^messages\\.delete/',
                ],
            ],

            // Accountant: financial actions - payments, fees-like, reports, transactions
            config('roles.default_roles.tenant.accountant') => [
                'patterns' => [
                    '/^student_records?\\./',
                    '/^payments?\\./',
                    '/^fees?\\./',
                    '/^transactions?\\./',
                    '/^reports?\\./',
                    '/^statistics?\\./',
                    '/^schedules?\\./',
                ],
                'exclude' => [
                    '/^student_records\\.(delete|update|create|store)/',
                ],
            ],
        ];

        // Helper: collect permission names matching any of the patterns, then exclude those matching exclude patterns
        $collectMatching = function (array $patterns, array $excludePatterns = []) use ($allPermissions) {
            $matches = [];
            foreach ($allPermissions as $perm) {
                $matched = false;
                foreach ($patterns as $pat) {
                    try {
                        if (preg_match($pat, $perm)) {
                            $matched = true;
                            break;
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
                if (!$matched) {
                    continue;
                }

                // Check exclusions
                $excluded = false;
                foreach ($excludePatterns as $exPat) {
                    try {
                        if (preg_match($exPat, $perm)) {
                            $excluded = true;
                            break;
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
                if (!$excluded) {
                    $matches[] = $perm;
                }
            }
            return array_values(array_unique($matches));
        };

        // Build a lowercase name -> Role instance map for robust matching
        $rolesMap = [];
        foreach ($createdRoles as $rName => $rObj) {
            $rolesMap[mb_strtolower($rName)] = $rObj;
        }

        // Assign permissions for each defined role
        foreach ($roleDefinitions as $roleName => $def) {
            $key = mb_strtolower($roleName);
            $role = $rolesMap[$key] ?? null;

            if (!$role) {
                // fuzzy match for slight spelling differences
                foreach ($rolesMap as $lowerName => $r) {
                    if (Str::contains($lowerName, $key) || Str::contains($key, $lowerName)) {
                        $role = $r;
                        break;
                    }
                }
            }

            if (!$role) {
                continue; // role not seeded (e.g., it was a root role)
            }

            $patterns = $def['patterns'] ?? [];
            $exclude = $def['exclude'] ?? [];

            if (empty($patterns)) {
                continue;
            }

            $permsForRole = $collectMatching($patterns, $exclude);

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

        $this->command->info('Non‑root roles seeded successfully.');
    }
}
