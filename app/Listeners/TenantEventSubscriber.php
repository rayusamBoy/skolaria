<?php

namespace App\Listeners;

use App\Events\TenantDeletingIDCardsThemeDir;
use App\Events\TenantDeletingStorageDir;
use App\Events\TenantIDCardsThemeDirDeleted;
use App\Events\TenantStorageDirDeleted;
use App\Notifications\TenantEventCompleted;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Notification;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\CreatingDomain;
use Stancl\Tenancy\Events\CreatingTenant;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Events\DatabaseDeleted;
use Stancl\Tenancy\Events\DatabaseMigrated;
use Stancl\Tenancy\Events\DatabaseSeeded;
use Stancl\Tenancy\Events\DeletingDomain;
use Stancl\Tenancy\Events\DeletingTenant;
use Stancl\Tenancy\Events\DomainCreated;
use Stancl\Tenancy\Events\DomainDeleted;
use Stancl\Tenancy\Events\MigratingDatabase;
use Stancl\Tenancy\Events\SeedingDatabase;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Events\TenantDeleted;

class TenantEventSubscriber
{
    /**
     * Event → Notification mapping
     */
    private function map(): array
    {
        $map = [
            // Tenant
            CreatingTenant::class => [
                'title' => 'Tenant creation started',
                'msg' => 'Creating tenant "%s"...',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
            TenantCreated::class => [
                'title' => 'Tenant created',
                'msg' => 'Tenant "%s" created successfully.',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
            DeletingTenant::class => [
                'title' => 'Tenant deletion started',
                'msg' => 'Deleting tenant "%s"...',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
            TenantDeleted::class => [
                'title' => 'Tenant deleted',
                'msg' => 'Tenant "%s" deleted successfully.',
                'tenant' => fn ($e) => $e->tenant->name,
            ],

            // Domain
            CreatingDomain::class => [
                'title' => 'Domain creation started',
                'msg' => 'Creating domain "%s" for tenant "%s"...',
                'params' => fn ($e) => [$e->domain->domain, $e->domain->tenant->name],
            ],
            DomainCreated::class => [
                'title' => 'Domain created',
                'msg' => 'Domain "%s" added to tenant "%s".',
                'params' => fn ($e) => [$e->domain->domain, $e->domain->tenant->name],
            ],
            DeletingDomain::class => [
                'title' => 'Domain deletion started',
                'msg' => 'Deleting domain "%s" for tenant "%s"...',
                'params' => fn ($e) => [$e->domain->domain, $e->domain->tenant->name],
            ],
            DomainDeleted::class => [
                'title' => 'Domain deleted',
                'msg' => 'Domain "%s" removed from tenant "%s".',
                'params' => fn ($e) => [$e->domain->domain, $e->domain->tenant->name],
            ],

            // Database
            CreatingDatabase::class => [
                'title' => 'Database creation started',
                'msg' => 'Creating database "%s" for tenant "%s"...',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name, $e->tenant->name],
            ],
            DatabaseCreated::class => [
                'title' => 'Database created',
                'msg' => 'Database "%s" created for tenant "%s".',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name, $e->tenant->name],
            ],
            MigratingDatabase::class => [
                'title' => 'Database migration started',
                'msg' => 'Migrating database "%s" for tenant "%s"...',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name, $e->tenant->name],
            ],
            DatabaseMigrated::class => [
                'title' => 'Database migrated',
                'msg' => 'Database "%s" migrated successfully.',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name],
            ],
            SeedingDatabase::class => [
                'title' => 'Database seeding started',
                'msg' => 'Seeding database "%s"...',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name],
            ],
            DatabaseSeeded::class => [
                'title' => 'Database seeded',
                'msg' => 'Database "%s" seeded successfully.',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name],
            ],
            DatabaseDeleted::class => [
                'title' => 'Database deleted',
                'msg' => 'Database "%s" deleted for tenant "%s".',
                'params' => fn ($e) => [$e->tenant->tenancy_db_name, $e->tenant->name],
            ],

            // Storage
            TenantDeletingStorageDir::class => [
                'title' => 'Storage cleanup started',
                'msg' => 'Removing storage for tenant "%s"...',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
            TenantStorageDirDeleted::class => [
                'title' => 'Storage cleaned',
                'msg' => 'Storage removed for tenant "%s".',
                'tenant' => fn ($e) => $e->tenant->name,
            ],

            // Theme
            TenantDeletingIDCardsThemeDir::class => [
                'title' => 'Theme directory cleanup started',
                'msg' => 'Removing ID card theme directory for tenant "%s"...',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
            TenantIDCardsThemeDirDeleted::class => [
                'title' => 'Theme directory removed',
                'msg' => 'ID card theme directory removed for tenant "%s".',
                'tenant' => fn ($e) => $e->tenant->name,
            ],
        ];

        return $map;
    }

    /**
     * Register the handle method for the subscriber class.
     */
    public function subscribe(Dispatcher $events): array
    {
        return collect($this->map())
            ->mapWithKeys(fn ($_, $event) => [$event => 'handle'])
            ->toArray();
    }

    /**
     * Handle the event and send a notification.
     */
    public function handle(object $event): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $config = $this->map()[\get_class($event)] ?? null;

        if (! $config) {
            return;
        }

        // Resolve parameters
        if (isset($config['params'])) {
            $params = $config['params']($event);
        } elseif (isset($config['tenant'])) {
            $params = [$config['tenant']($event)];
        } else {
            $params = [];
        }

        $message = vsprintf($config['msg'], $params);

        Notification::sendNow(auth()->user(), new TenantEventCompleted([
            'title' => $config['title'],
            'msg' => $message,
            'url' => route('tenants.index'),
        ]));
    }
}
