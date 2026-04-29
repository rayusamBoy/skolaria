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

        // Clean existing roles
        Role::query()->delete();

        // Create roles
        $createdRoles = [];
        foreach ($defaultRoles as $roleName) {
            $roleName = (string) $roleName;
            $createdRoles[$roleName] = Role::create([
                'name' => $roleName,
                'guard_name' => $default_guard,
            ]);
        }

        // Remove root roles from the created list to avoid assigning permissions to them
        $createdRoles = array_filter($createdRoles, fn($r) => !\in_array($r->name, $rootRoles));

        // Fetch all permission names present in this tenant DB
        $allPermissions = Permission::all()->pluck('name')->toArray();

        /**
         * Role -> array with 'patterns' and optional 'exclude'.
         * Patterns are regexes to match permission names.
         * Exclude can be an array of regexes (or exact strings) to remove permissions.
         */
        $roleDefinitions = [
            config('roles.default_roles.tenant.admin') => [
                'patterns' => ['/.*/'],
                'exclude' => [
                    '/^messages\.(delete)/',
                    // Admin does not manage roles beyond viewing
                    '/^roles\.(create|update|delete)/',
                ],
            ],

            config('roles.default_roles.tenant.teacher') => [
                'patterns' => [
                    '/^my_classes?\.view/',          // view class list
                    '/^sections?\.view/',            // view sections
                    '/^student_records?\.view/',     // view student details
                    '/^student_records?\.list_by_class/', // see students by class
                    '/^marks?\./',                   // enter / update marks for own subjects
                    '/^assessments?\./',             // enter / update assessments
                    '/^grades?\.view/',              // view grade definitions
                    '/^divisions?\.view/',           // view division definitions
                    '/^subjects?\.view/',            // view subjects (assigned subjects are filtered by controller)
                    '/^exams?\.view/',               // view exams list
                    '/^time_tables?\.(view)/', // manage own timetable
                    '/^schedules?\.view/',           // view calendar events
                    '/^my_accounts?\./',             // own profile & password
                    '/^messages?\.(view|create|store)/', // message students/parents
                    '/^notices?\.view/',             // view notices
                    '/^statistics?\.view/',          // view statistics
                ],
                'exclude' => [
                    '/^messages\.(delete|update|user_delete|remove_participant)/',
                    '/^marks\.(batch_delete)/',
                    '/^assessments\.(print_progressive|progressive_select)/', // no progressive sheet admin
                ],
            ],

            config('roles.default_roles.tenant.student') => [
                'patterns' => [
                    '/^my_accounts?\./',               // profile & password
                    '/^marks?\.year_selector/',        // view own marksheet
                    '/^marks?\.year_selected/',
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
                    '/^assessments?\.year_selected/',
                    '/^assessments?\.show/',
                    '/^subjects?\.view/',              // available subjects
                    '/^books?\.view/',                 // library catalogue
                    '/^book_requests?\.view/', // request books
                    '/^notices?\.view/',               // view notices
                    '/^schedules?\.view/',             // calendar events
                    '/^time_tables?\.view/',           // own timetable
                    '/^statistics?\.view/',            // own performance charts
                ],
                'exclude' => [],
            ],

            config('roles.default_roles.tenant.parent') => [
                'patterns' => [
                    '/^my_accounts?\./',               // profile & password
                    '/^marks?\.year_selector/',
                    '/^marks?\.year_selected/',
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
                    '/^assessments?\.year_selected/',
                    '/^assessments?\.show/',
                    '/^time_tables?\.view/',           // child's timetable
                    '/^notices?\.view/',
                    '/^schedules?\.view/',
                    '/^childrens?\./',                 // my children list
                    '/^statistics?\.view/',
                ],
                'exclude' => [],
            ],

            config('roles.default_roles.tenant.accountant') => [
                'patterns' => [
                    '/^payments?\./',                  // full payment management
                    '/^student_records?\.view/',       // view student details
                    '/^student_records?\.list_by_class/', // list students by class
                    '/^my_accounts?\./',               // profile & password
                    '/^notices?\./',                   // manage own notices
                    '/^statistics?\./',                // financial reports
                    '/^query_builders?\.(view|select|print_users_data)/', // optional reporting
                    '/^schedules?\.view/',
                ],
                'exclude' => [],
            ],

            config('roles.default_roles.tenant.librarian') => [
                'patterns' => [
                    '/^books?\./',
                    '/^book_requests?\./',
                    '/^my_accounts?\./',
                    '/^schedules?\.view/',   // see events
                    '/^notices?\.view/',
                ],
                'exclude' => [],
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
