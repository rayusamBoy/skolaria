# Laravel Permission Kit

[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20.svg)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE.md)

**Laravel Permission Kit** automatically discovers, synchronizes, and enforces [Spatie Laravel Permission](https://github.com/spatie/laravel-permission) permissions from controller methods.

It reduces repetitive permission declarations while keeping Spatie responsible for storing permissions and managing role assignments.

- **Automatic discovery:** Scan controller methods using PHP reflection.
- **Automatic enforcement:** Protect controller actions using a trait and middleware.
- **Consistent naming:** Generate permission names from controller and method names, with configurable mappings.
- **Custom definitions:** Override generated names and metadata using PHP attributes or configuration.
- **Permission synchronization:** Create and update permissions in Spatie's permissions table.
- **Editable manifest:** Save discovered permissions to a PHP file and customize their labels and descriptions.
- **Multi-context support:** Separate permission namespaces for central, tenant, or other application contexts.
- **Extensible:** Replace the scanner, naming strategy, and context resolver with custom implementations.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Permission Naming](#permission-naming)
- [Configuration](#configuration)
- [Attributes](#attributes)
- [Controller Enforcement](#controller-enforcement)
- [Route Middleware](#route-middleware)
- [Blade Authorization](#blade-authorization)
- [Facade API](#facade-api)
- [Scanning and Synchronization](#scanning-and-synchronization)
- [Permission Manifest](#permission-manifest)
- [Multi-Context Applications](#multi-context-applications)
- [Extending the Package](#extending-the-package)
- [Testing](#testing)
- [License](#license)

---

## Requirements

### Runtime

| Dependency | Supported version |
| --- | --- |
| PHP | `^8.3` |
| Laravel / Illuminate | `^13.0` |
| Spatie Laravel Permission | `^7.0` or `^8.0` |

### Development

- Pest 5
- Orchestra Testbench 11
- Orchestra Pest Plugin Testbench 5
- Laravel Pint

---

## Installation

Install the package using Composer:

```bash
composer require skolaria/laravel-permission-kit
```

The package registers its service provider and facade through Laravel package auto-discovery.

Run the installation command:

```bash
php artisan permission-kit:install
```

The command optionally publishes:

1. The package configuration file to `config/permission-kit.php`.
2. The optional migration that adds `label` and `description` columns to Spatie's `permissions` table.

You can also publish these individually:

```bash
php artisan vendor:publish --tag=permission-kit-config

php artisan vendor:publish --tag=permission-kit-migrations
```

If you publish the optional migration, run it against the relevant database:

```bash
php artisan migrate
```

For multi-database applications, make sure the migration is also applied to the databases where you want to store permission metadata.

The metadata migration is optional. Permission Kit can synchronize permission names without the additional columns.

---

## Quick Start

### 1. Configure controller scanning

Publish the configuration if you have not already:

```bash
php artisan vendor:publish --tag=permission-kit-config
```

The default configuration scans:

```php
'contexts' => [
    'default' => [
        'prefix' => null,
        'namespaces' => [
            'App\\Http\\Controllers',
        ],
        'skip_names' => [
            'HomeController',
            'AjaxController',
            'CookieController',
        ],
        'skip_paths' => [
            'Controllers/Auth',
        ],
    ],
],
```

The scanner discovers concrete controller classes and their public methods within the configured PSR-4 namespaces.

### 2. Add the authorization trait

Add `AuthorizesPermissions` to a controller:

```php
namespace App\Http\Controllers;

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

    public function update($id)
    {
        // Requires users.update
    }

    public function destroy($id)
    {
        // Requires users.delete
    }
}
```

When the package enforcement is enabled, the `EnforceControllerPermissions` middleware is automatically appended to the configured middleware groups.

The middleware checks whether the routed controller uses `AuthorizesPermissions`. Controllers without the trait are not subject to this automatic enforcement.

### 3. Discover permissions

Run:

```bash
php artisan permission-kit:scan
```

This displays the permissions discovered from the configured controllers without modifying the database.

### 4. Synchronize permissions

Run:

```bash
php artisan permission-kit:sync
```

The discovered permissions are created or updated in Spatie's permissions table.

### 5. Assign permissions to roles

Use Spatie's normal API:

```php
use Spatie\Permission\Models\Role;

$role = Role::create([
    'name' => 'Admin',
    'guard_name' => 'web',
]);

$role->givePermissionTo([
    'users.view',
    'users.create',
    'users.update',
]);
```

Permission Kit handles discovery and synchronization. Spatie continues to handle roles, permission assignments, and authorization checks.

---

## Permission Naming

Permission names are generated from the controller resource name and its action method.

For example:

| Controller | Method | Generated permission |
| --- | --- | --- |
| `UserController` | `index()` | `users.view` |
| `UserController` | `show()` | `users.show` |
| `UserController` | `create()` | `users.create` |
| `UserController` | `store()` | `users.create` |
| `UserController` | `edit()` | `users.update` |
| `UserController` | `update()` | `users.update` |
| `UserController` | `destroy()` | `users.delete` |
| `UserController` | `delete()` | `users.delete` |

The controller name is converted into a plural snake-case resource name.

For example:

```text
UserController       → users
StudentController    → students
MyClassController    → my_classes
CategoryController   → categories
```

### Method mapping

The default method map is:

```php
'method_map' => [
    'index' => 'view',
    'show' => 'show',
    'list' => 'view',
    'create' => 'create',
    'store' => 'create',
    'edit' => 'update',
    'update' => 'update',
    'destroy' => 'delete',
    'delete' => 'delete',
],
```

Methods not found in the map use their original method name as the permission action.

For example:

```php
public function export()
{
    // Requires users.export
}
```

### Snake case and camel case

Permission Kit supports both naming conventions.

The `use_dots` configuration controls how multiple method-name segments are separated:

```php
'use_dots' => 'auto',
```

Available values:

| Value | Behavior |
| --- | --- |
| `true` | Always use dots between method segments. |
| `false` | Always use underscores between method segments. |
| `'auto'` | Detect the method naming convention. |

Examples with automatic detection:

| Method | Generated permission |
| --- | --- |
| `updateLockState()` | `users.update.lock.state` |
| `update_lock_state()` | `users.update_lock_state` |
| `getHTMLContent()` | `users.get.html.content` |

The method map is applied to the first method-name segment. Remaining segments are retained.

---

## Configuration

The main configuration file is `config/permission-kit.php`.

### Enforcement

```php
'enforce' => true,

'enforce_middleware_groups' => [
    'web',
],
```

- `enforce` enables or disables automatic trait-based enforcement.
- `enforce_middleware_groups` defines which Laravel middleware groups receive the enforcement middleware.

For example, to enforce trait-based permissions on both web and API routes:

```php
'enforce_middleware_groups' => [
    'web',
    'api',
],
```

Only controllers using `AuthorizesPermissions` are checked.

### Contexts

Contexts allow an application to maintain separate permission namespaces.

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
        'extra_classes' => [
            'App\\Http\\Controllers\\AnalyticController',
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

Context options:

| Option | Description |
| --- | --- |
| `prefix` | Prefix applied to permissions discovered in the configured namespaces. |
| `namespaces` | PSR-4 namespaces scanned for controllers. |
| `exclude_namespaces` | Namespaces excluded from scanning. |
| `extra_classes` | Explicit controller classes included without applying the context prefix. |
| `skip_names` | Controller short names or fully qualified names excluded from scanning. |
| `skip_paths` | File-path substrings excluded from scanning. |

The scanner reads PSR-4 mappings from Composer's `autoload` and `autoload-dev` sections.

If the package needs to scan a different Composer file, configure:

```php
'composer_json_path' => base_path('composer.json'),
```

### Context resolver

The resolver determines which context is active when permissions are enforced.

For a single-context application, leave it as `null`:

```php
'context_resolver' => null,
```

For a multi-context application, provide a class implementing:

```php
PermissionKit\Contracts\ContextResolver
```

Example:

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

Register it in the configuration:

```php
'context_resolver' => App\Support\TenantContextResolver::class,
```

The returned context key must exist in `permission-kit.contexts`.

### Permission overrides

Use `overrides` when particular controller methods need explicit permission names or metadata:

```php
'overrides' => [
    'UserController' => [
        'index' => [
            'permission' => 'users.view.list',
            'label' => 'View Users',
            'description' => 'Display registered users.',
        ],

        'export' => [
            'permission' => 'users.export',
            'label' => 'Export Users',
            'description' => 'Export user records.',
        ],
    ],
],
```

Overrides can be keyed by:

- Fully qualified controller class name.
- Controller short name.
- Resource name.

A string override is also supported:

```php
'overrides' => [
    'UserController' => [
        'export' => 'users.export',
    ],
],
```

An explicit permission name supplied by an override is used as written.

### Synchronization options

```php
'sync_mode' => 'update',

'allow_prune' => false,

'guard' => 'web',
```

| Option | Description |
| --- | --- |
| `sync_mode` | Default synchronization behavior: `update`, `preserve`, or `overwrite`. |
| `allow_prune` | Enables deletion of orphaned permissions through pruning. |
| `guard` | Spatie guard name used when creating and checking permissions. |

### Other options

```php
'register_middleware' => true,

'manifest_path' => storage_path('app/permission-kit/permissions.php'),

'cache_resolved_names' => true,

'cache_ttl' => 86400,

'log_unregistered_permissions' => true,
```

- `register_middleware`: Registers the `permission.kit` route middleware alias.
- `manifest_path`: Location of the editable permission manifest. Set to `null` or an empty value to disable manifest usage.
- `cache_resolved_names`: Caches resolved controller-method permission names.
- `cache_ttl`: Cache lifetime in seconds.
- `log_unregistered_permissions`: Logs warnings when a resolved permission has not yet been registered.

In debug mode, an unregistered permission can fail open with a warning to help development continue. In production, the request is denied.

---

## Attributes

Permission Kit provides PHP attributes for customizing discovery and enforcement.

### `#[Permission]`

Use this attribute to assign a specific permission name, label, and description to a controller method.

```php
use PermissionKit\Attributes\Permission;

class ReportController
{
    #[Permission(
        'reports.export',
        'Export Reports',
        'Download report data.'
    )]
    public function export()
    {
        // ...
    }
}
```

The attribute's permission name is absolute: it does not receive the context prefix.

### `#[SkipPermission]`

Use this attribute to exclude an entire controller or an individual method from permission discovery and automatic enforcement.

For a method:

```php
use PermissionKit\Attributes\SkipPermission;

class HealthController
{
    #[SkipPermission]
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
```

For a controller:

```php
#[SkipPermission]
class PublicController
{
    // All methods are excluded.
}
```

---

## Controller Enforcement

The `AuthorizesPermissions` trait enables automatic permission enforcement.

```php
use PermissionKit\Concerns\AuthorizesPermissions;

class StudentController extends Controller
{
    use AuthorizesPermissions;

    public function index()
    {
        // students.view
    }

    public function store()
    {
        // students.create
    }
}
```

The package appends `EnforceControllerPermissions` to the configured middleware groups. It inspects the routed controller and resolves the required permission without instantiating the controller itself.

The permission is resolved in this order:

1. A permission explicitly attached to the route action.
2. A `#[Permission]` method attribute.
3. The permission manifest, when available.
4. The configured naming strategy.

The resolved permission is checked through the authenticated user's authorization system.

If the user does not have the required permission, the request receives a `403` response.

### Skip enforcement

Use `#[SkipPermission]` on a method or controller when the action should not require a permission.

You can also disable automatic enforcement globally:

```php
'enforce' => false,
```

The trait retains its `bootAuthorizesPermissions()` method for applications that prefer manual invocation. Automatic middleware enforcement is the default approach.

---

## Route Middleware

For routes that do not use the trait, or where you want to specify permissions explicitly, use the `permission.kit` middleware.

### Single permission

```php
Route::delete('/users/{user}', [
    UserController::class,
    'destroy',
])->middleware('permission.kit:users.delete');
```

### Check any-of permissions in Blade

Separate permissions with `|` or `,` when any one permission should grant access:

```php
Route::post('/users/{user}/toggle', [
    UserController::class,
    'toggle',
])->middleware('permission.kit:users.update|users.delete');
```

Comma-separated permissions are also supported:

```php
->middleware('permission.kit:users.update,users.delete');
```

### All-of permissions

Pass multiple middleware arguments when every permission group must be satisfied:

```php
Route::post('/users/{user}/manage', [
    UserController::class,
    'manage',
])->middleware([
    'permission.kit:users.update',
    'permission.kit:users.delete',
]);
```

Each middleware argument is checked independently. Therefore, the user must satisfy every group.

The middleware returns a `403` response when authorization fails.

---

## Blade Authorization

Permission Kit provides Blade directives for checking permissions.

### Check a single permission

```blade
@permissionKit('users.view')
    <a href="{{ route('users.index') }}">
        View Users
    </a>
@endpermissionKit
```

### Any-of permissions

```blade
@permissionKitAny(['users.update', 'users.delete'])
    <div>
        User management actions
    </div>
@endpermissionKitAny
```

### Require all Blade permissions

```blade
@permissionKitAll(['users.view', 'users.update'])
    <div>
        Full user management access
    </div>
@endpermissionKitAll
```

Laravel's native `@can` and `@canany` directives are also available because Permission Kit delegates authorization to Spatie and Laravel's Gate system:

```blade
@can('users.update')
    <a href="{{ route('users.edit', $user) }}">
        Edit
    </a>
@endcan
```

---

## Facade API

The package provides the `PermissionKit` facade:

```php
use PermissionKit\Facades\PermissionKit;
```

### Check a permission

```php
PermissionKit::can('users.view');
```

An optional user can be supplied:

```php
PermissionKit::can('users.view', $user);
```

### Check any permission

Returns `true` if the user has at least one of the supplied permissions:

```php
PermissionKit::canAny([
    'users.view',
    'users.create',
]);
```

### Check all permissions

Returns `true` only if the user has every supplied permission:

```php
PermissionKit::canAll([
    'users.view',
    'users.update',
]);
```

### Alias for `can`

```php
PermissionKit::isGranted('users.view');
```

### Get all permissions

```php
$permissions = PermissionKit::all();
```

Filter by context prefix:

```php
$permissions = PermissionKit::all('central');
```

### Get a user's permissions

```php
$permissions = PermissionKit::forUser();
```

Or specify a user:

```php
$permissions = PermissionKit::forUser($user);
```

### Get the active context

```php
$context = PermissionKit::currentContext();
```

### Scan permissions programmatically

```php
$definitions = PermissionKit::scan();

$tenantDefinitions = PermissionKit::scan('tenant');
```

These methods provide a convenient API while keeping Spatie as the underlying permission system.

---

## Scanning and Synchronization

Permission Kit provides Artisan commands for discovering, synchronizing, and maintaining permissions.

### `permission-kit:scan`

Scans configured controller namespaces and displays discovered permissions without modifying the database.

```bash
php artisan permission-kit:scan
```

Available options:

| Option | Description |
| --- | --- |
| `--context=` | Scan only the specified context. |
| `--format=` | Output format: `console`, `json`, or `php`. |
| `--pretty` | Pretty-print JSON output. |
| `--save` | Save discovered permissions to the manifest. |
| `--force` | With `--save`, replace the manifest instead of merging. |

Examples:

```bash
# Scan all contexts
php artisan permission-kit:scan

# Scan one context
php artisan permission-kit:scan --context=tenant

# JSON output
php artisan permission-kit:scan --format=json

# Pretty JSON output
php artisan permission-kit:scan --format=json --pretty

# Generate a PHP configuration snippet
php artisan permission-kit:scan --format=php

# Save discovered permissions to the manifest
php artisan permission-kit:scan --save

# Rebuild the manifest from the current scan
php artisan permission-kit:scan --save --force
```

### `permission-kit:sync`

Synchronizes discovered permissions into Spatie's permissions table.

```bash
php artisan permission-kit:sync
```

Available options:

| Option | Description |
| --- | --- |
| `--context=` | Restrict synchronization to a context when scanning. |
| `--dry-run` | Preview changes without writing to the database. |
| `--prune` | Remove eligible orphaned permissions when pruning is enabled. |
| `--mode=` | Override the configured synchronization mode. |

Examples:

```bash
# Synchronize permissions
php artisan permission-kit:sync

# Synchronize a specific context
php artisan permission-kit:sync --context=tenant

# Preview changes
php artisan permission-kit:sync --dry-run

# Use preserve mode
php artisan permission-kit:sync --mode=preserve

# Use overwrite mode
php artisan permission-kit:sync --mode=overwrite

# Preview pruning
php artisan permission-kit:sync --prune --dry-run
```

Synchronization modes:

| Mode | Behavior |
| --- | --- |
| `update` | Create missing permissions and update existing metadata when it differs. |
| `preserve` | Create missing permissions but leave existing permissions unchanged. |
| `overwrite` | Create missing permissions and overwrite existing metadata. |

When the optional metadata columns are not installed, synchronization still works with permission names and guard names.

The synchronization service clears Spatie's permission cache before and after database writes.

### `permission-kit:prune`

Deletes eligible orphaned permissions that are no longer discovered by the current scan.

Enable pruning first:

```php
'allow_prune' => true,
```

Then run:

```bash
php artisan permission-kit:prune
```

Skip the confirmation prompt:

```bash
php artisan permission-kit:prune --force
```

Permissions still assigned to roles or users are not deleted.

### `permission-kit:clear-cache`

Clears the package's resolved permission-name cache:

```bash
php artisan permission-kit:clear-cache
```

---

## Permission Manifest

The permission manifest provides an editable PHP file containing discovered permission definitions.

The default location is:

```text
storage/app/permission-kit/permissions.php
```

Generate or update it:

```bash
php artisan permission-kit:scan --save
```

The manifest contains definitions grouped by context, resource, and method.

Example:

```php
<?php

return [
    'tenant' => [
        'users' => [
            'index' => [
                'permission' => 'users.view',
                'label' => 'View Users',
                'description' => 'Display registered users.',
                'controller' => 'UserController',
                'method' => 'index',
            ],
        ],
    ],
];
```

You can edit labels, descriptions, and permission names directly.

Then synchronize:

```bash
php artisan permission-kit:sync
```

### Manifest synchronization

When a permission manifest exists, the synchronization command uses its definitions as the source of permissions instead of performing a live controller scan.

```bash
php artisan permission-kit:sync
```

#### Known limitation — Context filtering

Currently, the `--context` option does not filter entries when synchronization uses an existing manifest.

For example:

```bash
php artisan permission-kit:sync --context=tenant
```

If the manifest contains both `tenant` and `central` definitions, **both contexts will be synchronized**. The command does not currently restrict synchronization to the requested context.

The command should display a warning when this situation occurs:

```text
WARNING: A permission manifest is being used and contains multiple contexts.
The --context option does not filter manifest entries.
All contexts in the manifest will be synchronized.
```

This is a known limitation of the current implementation. Context-aware manifest filtering is planned for a future update.

**Recommendation:** When synchronizing separate central and tenant databases, be aware that a multi-context manifest may synchronize definitions from both contexts. Ensure the target database and permission configuration are appropriate before running the command.

### Manifest merge behavior

By default:

```bash
php artisan permission-kit:scan --save
```

Merges newly discovered definitions while preserving existing labels and descriptions. Entries no longer discovered are dropped from the regenerated manifest.

To completely rebuild it:

```bash
php artisan permission-kit:scan --save --force
```

The manifest can be disabled by setting `manifest_path` to `null` or an empty value.

---

## Multi-Context Applications

Permission Kit supports applications with multiple permission contexts, including multi-tenant applications with separate central and tenant databases.

Each context can define its own:

- Controller namespaces.
- Permission prefix.
- Excluded namespaces.
- Shared controller classes.
- Skip rules.

### Tenant and central example

A tenant context might use:

```php
'tenant' => [
    'prefix' => null,
    'namespaces' => [
        'App\\Http\\Controllers',
    ],
    'exclude_namespaces' => [
        'App\\Http\\Controllers\\Central',
    ],
    'extra_classes' => [],
],
```

A central context might use:

```php
'central' => [
    'prefix' => 'central',
    'namespaces' => [
        'App\\Http\\Controllers\\Central',
    ],
    'exclude_namespaces' => [],
    'extra_classes' => [
        'App\\Http\\Controllers\\AnalyticController',
    ],
],
```

The resulting permissions can be separated as follows:

| Controller | Context | Permission |
| --- | --- | --- |
| `UserController` | Tenant | `users.view` |
| `Central\UserController` | Central | `central.users.view` |
| `AnalyticController` | Central extra class | `analytics.view` |

Classes configured under `extra_classes` are included without applying the context prefix. This is useful for shared controllers whose permission names should remain the same across contexts.

### Synchronizing different databases

Permission Kit does not automatically switch tenant databases or decide when your application should seed permissions.

Call the synchronization command from the appropriate database seeder or tenant lifecycle process.

For example, a central seeder:

```php
use Illuminate\Support\Facades\Artisan;

public function run(): void
{
    Artisan::call('permission-kit:sync', [
        '--context' => 'central',
    ]);
}
```

A tenant seeder:

```php
use Illuminate\Support\Facades\Artisan;

public function run(): void
{
    Artisan::call('permission-kit:sync', [
        '--context' => 'tenant',
    ]);
}
```

Make sure the active database connection points to the intended database before running synchronization.

This keeps database lifecycle management under the control of the host application.

---

## Extending the Package

The package uses contracts for its main extension points.

| Contract | Default implementation | Responsibility |
| --- | --- | --- |
| `PermissionKit\Contracts\Scanner` | `ControllerScanner` | Discovers controller methods. |
| `PermissionKit\Contracts\NamingStrategy` | `PermissionNameBuilder` | Generates permission names and metadata. |
| `PermissionKit\Contracts\ContextResolver` | `DefaultContextResolver` | Determines the active context. |

You can replace these implementations through Laravel's service container.

### Custom naming strategy

Create a class implementing `NamingStrategy`:

```php
namespace App\Permissions;

use PermissionKit\Contracts\NamingStrategy;
use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;
use ReflectionClass;
use ReflectionMethod;

class CustomNamingStrategy implements NamingStrategy
{
    public function build(
        ReflectionClass $class,
        ReflectionMethod $method,
        Context $context,
        bool $apply_prefix = true
    ): PermissionDefinition {
        // Implement your naming convention.

        return new PermissionDefinition(
            name: 'custom.permission',
            label: 'Custom Permission',
            description: null,
            context: $context->key,
            controller: $class->getShortName(),
            method: $method->getName(),
        );
    }
}
```

Register it in your application service provider:

```php
use App\Permissions\CustomNamingStrategy;
use PermissionKit\Contracts\NamingStrategy;

public function register(): void
{
    $this->app->bind(
        NamingStrategy::class,
        CustomNamingStrategy::class,
    );
}
```

The same approach can be used to replace the scanner or context resolver.

---

## Testing

The package uses Pest 5 and Orchestra Testbench 11.

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test
```

The package tests cover the main functionality, including:

- Permission name generation and method mapping.
- Permission attributes and skip behavior.
- Context configuration.
- Controller enforcement.
- Explicit route middleware.
- Blade authorization directives.
- Facade methods.
- Permission scanning.
- Permission synchronization.
- Dry-run behavior and pruning.

---

## License

Laravel Permission Kit is open-sourced software licensed under the [MIT license](LICENSE.md).
