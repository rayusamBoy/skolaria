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
        $createdRoles = array_filter($createdRoles, fn ($r) => ! \in_array($r->name, $rootRoles));

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
                    '/^roles\.(store|update|delete)/',
                    '/^bin\.(empty_soft_deleted_model|user_force_delete)/',
                    '/^childrens?\./',
                ],
            ],

            config('roles.default_roles.tenant.teacher') => [
                'patterns' => [
                    '/^my_classes?\.view/',
                    '/^sections?\.view/',
                    '/^student_records?\.view/',
                    '/^student_records?\.list_by_class/',
                    '/^marks?\./',
                    '/^assessments?\./',
                    '/^grades?\.view/',
                    '/^divisions?\.view/',
                    '/^subjects?\.view/',
                    '/^exams?\.view/',
                    '/^time_tables?\.(view)/',
                    '/^time_tables?\.(print_record)/',
                    '/^schedules?\.view/',
                    '/^my_accounts?\./',
                    '/^messages?\.(view|create|store)/',
                    '/^statistics?\.view/',
                    '/^dashboard\.stats\.active_students/',
                    '/^dashboard\.stats\.overall_students/',
                    '/^dashboard\.stats\.assessments_declared/',
                    '/^dashboard\.stats\.subjects/',
                    '/^dashboard\.stats\.classes/',
                    '/^dashboard\.stats\.results_declared/',
                    '/^dashboard\.charts\.students_per_class/',
                    '/^dashboard\.modal\.student_breakdown_modal/',
                    '/^pins\.enter_pin/',
                ],
                'exclude' => [
                    '/^messages\.(delete|update|user_delete|remove_participant)/',
                    '/^marks\.(batch_delete|batch_update)/',
                    '/^assessments\.(print_progressive|progressive_select)/',
                    '/^marks\.(tabulation|tabulation_select)/',
                ],
            ],

            config('roles.default_roles.tenant.student') => [
                'patterns' => [
                    '/^my_accounts?\./',
                    '/^marks?\.year_selector/',
                    '/^marks?\.year_selected/',
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
                    '/^assessments?\.year_selected/',
                    '/^assessments?\.show/',
                    '/^subjects?\.view/',
                    '/^books?\.view/',
                    '/^book_requests?\.view/',
                    '/^schedules?\.view/',
                    '/^time_tables?\.view/',
                    '/^time_tables?\.print_record/',
                    '/^statistics?\.view/',
                    '/^pins\.enter_pin/',
                ],
                'exclude' => [],
            ],

            config('roles.default_roles.tenant.parent') => [
                'patterns' => [
                    '/^my_accounts?\./',
                    '/^marks?\.year_selector/',
                    '/^marks?\.year_selected/',
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
                    '/^assessments?\.year_selected/',
                    '/^assessments?\.show/',
                    '/^time_tables?\.view/',
                    '/^time_tables?\.print_record/',
                    '/^schedules?\.view/',
                    '/^childrens?\./',
                    '/^statistics?\.view/',
                    '/^pins\.enter_pin/',
                ],
                'exclude' => [],
            ],

            config('roles.default_roles.tenant.accountant') => [
                'patterns' => [
                    '/^payments?\./',
                    '/^student_records?\.view/',
                    '/^student_records?\.list_by_class/',
                    '/^my_accounts?\./',
                    '/^notices?\./',
                    '/^query_builders?\.(view|select)/',
                    '/^schedules?\.view/',
                    '/^dashboard\.stats\.active_students/',
                    '/^dashboard\.stats\.payments_done/',
                    '/^dashboard\.charts\.payments/',
                    '/^pins\.enter_pin/',
                ],
                'exclude' => [
                    '/^query_builders?\.(print_users_data)/',
                ],
            ],

            config('roles.default_roles.tenant.librarian') => [
                'patterns' => [
                    '/^books?\./',
                    '/^book_requests?\./',
                    '/^student_records?\.view/',
                    '/^student_records?\.list_by_class/',
                    '/^my_accounts?\./',
                    '/^schedules?\.view/',
                    '/^notices?\.view/',
                    '/^dashboard\.stats\.books/',
                    '/^dashboard\.stats\.book_requests/',
                    '/^pins\.enter_pin/',
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
                if (! $matched) {
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
                if (! $excluded) {
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

            if (! $role) {
                // fuzzy match for slight spelling differences
                foreach ($rolesMap as $lowerName => $r) {
                    if (Str::contains($lowerName, $key) || Str::contains($key, $lowerName)) {
                        $role = $r;
                        break;
                    }
                }
            }

            if (! $role) {
                continue; // role not seeded (e.g., it was a root role)
            }

            $patterns = $def['patterns'] ?? [];
            $exclude = $def['exclude'] ?? [];

            if (empty($patterns)) {
                continue;
            }

            $permsForRole = $collectMatching($patterns, $exclude);

            if (property_exists($this, 'command') && $this->command) {
                $this->command->info("Role: {$role->name} will get ".\count($permsForRole).' permissions:');
                foreach ($permsForRole as $p) {
                    $this->command->line(" - {$p}");
                }
            }

            if (! empty($permsForRole)) {
                $role->givePermissionTo($permsForRole);
            }
        }

        $this->command->info('Non‑root roles seeded successfully.');
    }
}
