<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenancyRolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $rootRoles = array_values(
            config('roles.root_roles.tenant', [])
        );

        $defaultRoles = array_values(
            config('roles.default_roles.tenant', [])
        );

        // Create or retrieve configured tenant roles without deleting existing roles.
        $createdRoles = [];

        foreach ($defaultRoles as $roleName) {
            $roleName = (string) $roleName;

            $createdRoles[$roleName] = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);
        }

        // Only permissions registered for this guard are eligible.
        $allPermissions = Permission::query()
            ->where('guard_name', $guard)
            ->get();

        /*
         * Role permission definitions.
         *
         * Root roles are deliberately excluded from these definitions.
         * They receive their privileges through the root-role Gate bypass.
         */
        $roleDefinitions = [
            config('roles.default_roles.tenant.admin') => [
                'patterns' => ['/.*/'],
                'exclude' => [
                    // Sensitive Super Admin-only functionality.
                    '/^soft_deletes\./',
                    // Restrict role management.
                    '/^roles\.(create|update|delete)$/',
                    // Restrict destructive messaging actions.
                    '/^messages\.(delete)$/',
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
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
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
                    '/^marks?\.show/',
                    '/^assessments?\.year_selector/',
                    '/^assessments?\.show/',
                    '/^time_tables?\.view/',
                    '/^time_tables?\.print_record/',
                    '/^schedules?\.view/',
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

        // Match permission names against the configured patterns.
        $matchesPatterns = static function (
            string $permission,
            array $patterns
        ): bool {
            foreach ($patterns as $pattern) {
                if (@preg_match($pattern, $permission) === 1) {
                    return true;
                }
            }

            return false;
        };

        /** @var Role $role */
        foreach ($createdRoles as $roleName => $role) {
            // Root roles deliberately have no directly assigned permissions.
            if (\in_array($roleName, $rootRoles, true)) {
                $role->syncPermissions([]);

                continue;
            }

            $definition = $roleDefinitions[$roleName] ?? null;

            if (! $definition) {
                $role->syncPermissions([]);

                continue;
            }

            $patterns = $definition['patterns'] ?? [];
            $exclusions = $definition['exclude'] ?? [];

            /** @var Collection $permission */
            $permissions = $allPermissions->filter(
                function (Permission $permission) use (
                    $patterns,
                    $exclusions,
                    $matchesPatterns
                ): bool {
                    $name = $permission->name;

                    if (! $matchesPatterns($name, $patterns)) {
                        return false;
                    }

                    return ! $matchesPatterns($name, $exclusions);
                }
            );

            // Replace existing assignments rather than accumulating stale permissions.
            $role->syncPermissions($permissions->modelKeys());

            $this->command?->info(
                "Role: {$roleName} assigned {$permissions->count()} permissions."
            );
        }

        $registrar->forgetCachedPermissions();

        $this->command?->info('Tenant roles synchronized successfully.');
    }
}
