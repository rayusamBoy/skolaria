# Laravel Permission Kit

Generate, sync, and enforce [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) permissions by scanning your controller methods.

**Zero boilerplate. Zero schema changes.**

- **Auto-discover:** Discover permissions from your controllers using reflection.
- **Enforce:** Protect controller methods using a trait and middleware — no policy files required.
- **Sync:** Synchronize permissions into Spatie's `permissions` table with a single Artisan command.
- **Multi-Context Ready:** Seamlessly handle shared and isolated controllers in multi-tenant applications.
- **Extensible:** Customize naming, scanning, context resolution, and synchronization through your own implementations.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration Reference](#configuration-reference)
- [Attributes](#attributes)
- [Native Blade Authorization and Facade](#native-blade-authorization-and-facade)
- [Middleware](#middleware)
- [Artisan Commands](#artisan-commands)
- [Multi-Context (Tenancy)](#multi-context-tenancy)
- [Extending the Package](#extending-the-package)
- [Testing](#testing)
- [License](#license)

---

## Requirements

### Runtime Requirements

| Component                 | Version   |
| ------------------------- | --------- |
| PHP                       | `^8.3`  |
| Laravel                   | `^13.0` |
| spatie/laravel-permission | `^8.0`  |

### Development Requirements

- Pest 5
- Orchestra Testbench 11
- Laravel Pint

---

## Installation

Install the package through Composer:

```bash
composer require skolaria/laravel-permission-kit
```

Run the installation command:

```bash
php artisan permission-kit:install
```

The interactive installation command will:

1. Ask whether you want to publish the configuration file.
2. Ask whether you want to publish the optional metadata migration (label and description columns).
3. Print the next steps for wiring up your database seeders.

### Wiring Up the Seeder

Because multi-tenant applications use different database seeding strategies, the package does not automatically inject a seeder class.

You should manually add the synchronization command to your seeding process.

#### Single-Database Applications

In your `DatabaseSeeder.php`:

```php
use Illuminate\Support\Facades\Artisan;

public function run(): void
{
    // Sync permissions before seeding roles
    Artisan::call('permission-kit:sync');

    $this->call(RolesTableSeeder::class);

    // ...
}
```

#### Multi-Database Applications (e.g. Stancl Tenancy)

Run the central synchronization in your central `DatabaseSeeder.php`:

```php
Artisan::call('permission-kit:sync', [
    '--context' => 'central',
]);
```

Run the tenant synchronization in your `TenantDatabaseSeeder.php`:

```php
Artisan::call('permission-kit:sync', [
    '--context' => 'tenant',
]);
```

---

## Quick Start

### 1. Add the Trait to a Controller

Use the `AuthorizesPermissions` trait in your controllers:

```php
use Illuminate\Routing\Controller;
use PermissionKit\Concerns\AuthorizesPermissions;

class UserController extends Controller
{
    use AuthorizesPermissions;

    public function index()
    {
        // Requires users.view
    }

    public function store()
    {
        // Requires users.create
    }
}
```

The `EnforceControllerPermissions` middleware is automatically appended to the `web` middleware group when the package boots.

It inspects each routed controller and enforces permissions on controllers using the trait.

### 2. Preview and Synchronize Permissions

Preview discovered permissions without modifying the database:

```bash
php artisan permission-kit:scan
```

Synchronize discovered permissions into Spatie's permissions table:

```bash
php artisan permission-kit:sync
```

### 3. Assign Permissions

Use Spatie's standard API to assign permissions to roles:

```php
use Spatie\Permission\Models\Role;

$role = Role::create([
    'name' => 'Admin',
]);

$role->givePermissionTo([
    'users.view',
    'users.create',
]);
```

---

## Configuration Reference

### Contexts (Core Functionality)

The package supports scanning multiple namespaces and applying prefixes based on the active context.

For example, a multi-tenant application can have separate `tenant` and `central` contexts.

```php
'contexts' => [

    'tenant' => [
        'prefix' => null,

        'namespaces' => [
            'App\\Http\\Controllers',
        ],

        'exclude_namespaces' => [
            'App\\Http\\Controllers\\Central',
        ],

        'extra_classes' => [],

        'skip_names' => [
            'HomeController',
            'AjaxController',
        ],

        'skip_paths' => [
            'Controllers/Auth',
        ],
    ],

    'central' => [
        'prefix' => 'central',

        'namespaces' => [
            'App\\Http\\Controllers\\Central',
        ],

        'exclude_namespaces' => [],

        // Shared root controllers.
        // These will NOT receive the "central." prefix.
        'extra_classes' => [
            'App\\Http\\Controllers\\AnalyticController',
            'App\\Http\\Controllers\\TicketController',
        ],

        'skip_names' => [
            'HomeController',
            'AjaxController',
        ],

        'skip_paths' => [
            'Controllers/Auth',
        ],
    ],

],
```

### Context Configuration Options

| Option                 | Purpose                                                                                                                                        |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| `prefix`             | String prepended to permissions discovered in the namespace (e.g. `central.users.view`).                                                     |
| `namespaces`         | PSR-4 namespaces scanned recursively. Permissions discovered here receive the context prefix.                                                  |
| `exclude_namespaces` | Sub-namespaces to exclude entirely (e.g. `Tenant` excludes `Central\*`).                                                                   |
| `extra_classes`      | Fully qualified class names to include manually. Permissions discovered here do not receive the context prefix. Useful for shared controllers. |
| `skip_names`         | Controller names to ignore entirely.                                                                                                           |
| `skip_paths`         | Substrings of file paths to ignore.                                                                                                            |

### Other Configuration Options

| Option           | Description                                                                                                |
| ---------------- | ---------------------------------------------------------------------------------------------------------- |
| `enforce`      | Boolean. Enables or disables trait-based permission enforcement.                                           |
| `use_dots`     | Boolean or `'auto'`. Controls permission-name separators. Auto detects camelCase versus snake_case.      |
| `method_map`   | Maps controller method names to permission actions (e.g. `store` → `create`, `edit` → `update`). |
| `skip_methods` | Methods that should never be scanned (e.g. `__construct`).                                               |
| `sync_mode`    | Synchronization behavior: `update`, `preserve`, or `overwrite`.                                      |
| `allow_prune`  | Boolean. Enables the `--prune` option.                                                                   |
| `guard`        | Spatie guard name. Defaults to `web`.                                                                    |

---

## Attributes

The package provides PHP attributes to customize permission discovery and enforcement.

### `#[Permission]` — Override the Automatically Generated Name

Use the `Permission` attribute when you want to specify a custom permission name, label, and description.

```php
use PermissionKit\Attributes\Permission;

#[Permission(
    'reports.export',
    'Export Reports',
    'Download a CSV of reports'
)]
public function export()
{
    // ...
}
```

**Note:** Permission names defined through this attribute are absolute and will never receive the context prefix.

### `#[SkipPermission]` — Bypass Enforcement

Use the `SkipPermission` attribute to exclude a controller or method from permission enforcement.

```php
use PermissionKit\Attributes\SkipPermission;

#[SkipPermission]
public function ping()
{
    // Public endpoint
}
```

---

## Native Blade Authorization and Facade

### Native Laravel Blade Directives

Use Laravel's standard `@can` and `@canany` directives.

Because this package integrates directly with Spatie, which in turn integrates with Laravel's native Gate, you do not need custom Blade directives.

Standard Laravel authorization works out of the box and supports IDE autocompletion and static analysis.

**Check a single permission:**

```blade
@can('users.update')
    <a href="{{ route('users.edit', $user) }}">
        Edit User
    </a>
@endcan
```

**Check multiple permissions (any-of):**

```blade
@canany([
    'central.users.update',
    'central.users.delete'
])
    <div>
        Admin actions available
    </div>
@endcanany
```

### The PermissionKit Facade

The facade provides package-specific management operations, such as checking active contexts and retrieving permissions.

```php
use PermissionKit\Facades\PermissionKit;

// Get all permissions for the central context
PermissionKit::all('central');

// Get the active context key
PermissionKit::currentContext();

// Scan permission definitions without saving
PermissionKit::scan('tenant');
```

---

## Middleware

The `AuthorizesPermissions` trait automatically enforces permissions on controller methods.

If you need to protect a specific route closure or prefer not to use the trait, you can use the `permission.kit` middleware directly.

### Single Permission

```php
Route::delete('/users/{user}', [
    UserController::class,
    'destroy',
])->middleware('permission.kit:users.delete');
```

### Multiple Permissions (Any-of)

Use the pipe (`|`) separator when any one of multiple permissions should grant access.

```php
Route::post('/users/{user}/toggle', [
    UserController::class,
    'toggle',
])->middleware('permission.kit:users.update|users.delete');
```

---

## Artisan Commands

The package provides the following Artisan commands.

### `permission-kit:install`

Publishes optional configuration and migration files and prints installation instructions.

```bash
php artisan permission-kit:install
```

### `permission-kit:scan`

Scans controllers and previews discovered permissions without modifying the database.

Supported options:

- `--context` — Scan a specific context.
- `--format=json` — Output results as JSON.
- `--format=php` — Generate a PHP configuration snippet.

Examples:

```bash
# Scan all configured contexts
php artisan permission-kit:scan

# Scan a specific context
php artisan permission-kit:scan --context=tenant

# Output JSON
php artisan permission-kit:scan --format=json

# Generate a PHP configuration snippet
php artisan permission-kit:scan --format=php
```

### `permission-kit:sync`

Synchronizes discovered permissions into Spatie's permissions table.

```bash
# Synchronize all configured permissions
php artisan permission-kit:sync

# Synchronize tenant permissions
php artisan permission-kit:sync --context=tenant

# Preview changes without writing to the database
php artisan permission-kit:sync --dry-run

# Prune orphaned permissions
php artisan permission-kit:sync --prune --dry-run
```

### `permission-kit:clear-cache`

Clears Spatie's permission cache.

```bash
php artisan permission-kit:clear-cache
```

---

## Multi-Context (Tenancy)

The package supports multi-context applications, including multi-database tenancy setups.

A common scenario is having controllers in the root application namespace that are shared between Central and Tenant contexts.

### Shared Controllers

Consider the following configuration:

```php
'extra_classes' => [
    'App\\Http\\Controllers\\AnalyticController',
    'App\\Http\\Controllers\\TicketController',
],
```

Because `extra_classes` ignores the context prefix, a shared `AnalyticController` generates:

```text
analytics.view
```

This permission is generated in both the central and tenant databases.

### Isolated Controllers

Controllers isolated within specific namespaces receive their respective context prefixes.

For example:

| Controller                 | Context | Generated Permission   |
| -------------------------- | ------- | ---------------------- |
| `Central\UserController` | Central | `central.users.view` |
| `UserController`         | Tenant  | `users.view`         |

This allows the same application to maintain separate central and tenant permission sets.

### The Sync Service (Safety Features)

When running seeders, the package's `PermissionSyncService` provides multi-database safety features:

1. **Duplicate protection:** Checks whether permissions already exist before attempting to create them.
2. **Cache-safe creation:** Bypasses Spatie's internal cache using direct model queries to prevent `Permission Already Exists` exceptions during tenant creation.
3. **Schema compatibility:** Dynamically checks whether the `label` and `description` columns exist, preventing schema errors when the optional metadata migration has not been applied to a database.

### Context Resolver

Configure a custom resolver to determine the active context based on your tenancy state.

In `config/permission-kit.php`:

```php
'context_resolver' => \App\Support\TenantContextResolver::class,
```

Create the resolver:

```php
namespace App\Support;

use PermissionKit\Contracts\ContextResolver;

class TenantContextResolver implements ContextResolver
{
    public function resolve(): string
    {
        return tenancy()->tenant ? 'tenant' : 'central';
    }
}
```

This allows Permission Kit to automatically determine whether the application is currently operating in the central or tenant context.

---

## Extending the Package

Every major component is bound to an interface, allowing you to replace the default implementation with your own.

You can register custom implementations in your application's `AppServiceProvider`.

### Extension Points

| Interface / Class                                | Purpose                                   |
| ------------------------------------------------ | ----------------------------------------- |
| `PermissionKit\Contracts\Scanner`              | Discovers controllers and methods.        |
| `PermissionKit\Contracts\NamingStrategy`       | Generates permission names.               |
| `PermissionKit\Contracts\ContextResolver`      | Determines the active context.            |
| `PermissionKit\Services\PermissionSyncService` | Handles permission persistence in Spatie. |

### Example: Custom Naming Strategy

Register your custom naming strategy:

```php
use PermissionKit\Contracts\NamingStrategy;
use App\Permissions\LegacyNamingStrategy;

$this->app->bind(
    NamingStrategy::class,
    LegacyNamingStrategy::class,
);
```

This allows applications to retain their existing permission naming conventions while benefiting from automatic discovery and synchronization.

---

## Testing

The package uses:

- Pest 5
- Orchestra Testbench 11

Run the package tests using Composer.

```bash
# Install development dependencies
composer install

# Run all tests
composer test
```

---

## License

MIT License.

See [LICENSE.md](LICENSE.md) for details.
