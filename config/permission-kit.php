<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Composer path
    |--------------------------------------------------------------------------
    | The scanner reads the PSR-4 autoload map from this composer.json to
    | resolve namespaces to directories. Defaults to base_path('composer.json').
    | In tests you can point this at the package's own composer.json.
    */
    'composer_json_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Enforcement
    |--------------------------------------------------------------------------
    | When enabled, controllers using AuthorizesPermissions will check the
    | resolved permission on every public action. Skip with #[SkipPermission].
    */
    'enforce' => true,

    /*
    |--------------------------------------------------------------------------
    | Middleware groups that receive automatic enforcement
    |--------------------------------------------------------------------------
    | The package middleware is appended to these groups so any controller
    | using the trait is enforced automatically. Add 'api' here if you have
    | trait-using controllers on API routes.
    */
    'enforce_middleware_groups' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Separator style between method segments
    |--------------------------------------------------------------------------
    | true    : always use dots       (update_lock_state → update.lock.state)
    | false   : always use underscores (update_lock_state → update_lock_state)
    | 'auto'  : detect from the method name —
    |           camelCase input → dots,   snake_case input → underscores
    |           This gives each app the naming it expects based on its own
    |           coding convention.
    */
    'use_dots' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Method → action mapping
    |--------------------------------------------------------------------------
    | Lowercase method prefixes → permission verbs.
    */
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

    /*
    |--------------------------------------------------------------------------
    | Global skip list (method names)
    |--------------------------------------------------------------------------
    */
    'skip_methods' => [
        '__construct',
        '__call',
        'callAction',
        'middleware',
        'authorize',
        'middlewarePriority',
        'getMiddleware',
    ],

    /*
    |--------------------------------------------------------------------------
    | Contexts
    |--------------------------------------------------------------------------
    | Each context scans one or more namespaces. Optional prefix is prepended
    | to permission names to isolate contexts (e.g. "central.users.view").
    */
    'contexts' => [
        // 'default' => [
        //     'prefix' => null,
        //     'namespaces' => ['App\\Http\\Controllers'],
        //     'skip_names' => ['HomeController', 'AjaxController', 'CookieController'],
        //     'skip_paths' => ['Controllers/Auth'],
        // ],

        // Example multi-context layout:
        'tenant' => [
            'prefix' => null,
            // Scan everything in the root controllers folder...
            'namespaces' => ['App\\Http\\Controllers'],
            // ...but drop anything in the Central subfolder.
            'exclude_namespaces' => ['App\\Http\\Controllers\\Central'],
            'extra_classes' => [],
            'skip_names' => ['HomeController', 'AjaxController', 'CookieController', 'SessionController'],
            'skip_paths' => ['Controllers/Auth'],
        ],

        'central' => [
            // Optional prefix from defined namespaces
            'prefix' => 'central',
            // Scan ONLY the Central folder...
            'namespaces' => ['App\\Http\\Controllers\\Central'],
            'exclude_namespaces' => [],
            // ...plus explicitly pull in these specific shared controllers from the root.
            // Notice we don't include UserController here, because it exists in Central!
            'extra_classes' => [
                'App\\Http\\Controllers\\AnalyticController',
                'App\\Http\\Controllers\\MyAccountController',
                'App\\Http\\Controllers\\TicketController',
                'App\\Http\\Controllers\\UserImpersonationController',
            ],
            'skip_names' => ['HomeController', 'AjaxController', 'CookieController', 'SessionController'],
            'skip_paths' => ['Controllers/Auth'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Context resolver
    |--------------------------------------------------------------------------
    | A class that returns the active context key at runtime.
    | For single-context apps, leave null (uses the first context key).
    | For tenancy, publish your own resolver and point here.
    */
    'context_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Overrides
    |--------------------------------------------------------------------------
    | Force specific permission names. Keys are controller short names or
    | resource names. Values are method => permission or method => array.
    */
    'overrides' => [],

    /*
    |--------------------------------------------------------------------------
    | Sync behaviour
    |--------------------------------------------------------------------------
    | update    : update label/description if changed (default)
    | preserve  : leave existing untouched
    | overwrite : force overwrite
    */
    'sync_mode' => 'update',

    /*
    |--------------------------------------------------------------------------
    | Prune
    |--------------------------------------------------------------------------
    | Allow `permission-kit:sync --prune` to delete permissions not found in
    | the current scan. Requires this to be true; otherwise the flag is a no-op.
    */
    'allow_prune' => false,

    /*
    |--------------------------------------------------------------------------
    | Guard name
    |--------------------------------------------------------------------------
    */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Middleware alias
    |--------------------------------------------------------------------------
    | Register the "permission.kit" alias for EnsurePermission middleware.
    */
    'register_middleware' => true,

    /*
    |--------------------------------------------------------------------------
    | Manifest path
    |--------------------------------------------------------------------------
    | Where `permission-kit:scan --save` writes the editable manifest. If this
    | file exists, `permission-kit:sync` uses it (preserving your label and
    | description edits) instead of re-scanning.
    |
    | Set to null to disable the manifest feature entirely.
    */
    'manifest_path' => storage_path('app/permission-kit/permissions.php'),

    /*
    |--------------------------------------------------------------------------
    | Cache resolved permission names
    |--------------------------------------------------------------------------
    | When true, the (context, controller, method) → name mapping is cached so
    | the enforcement middleware doesn't reflect on every request. Invalidated
    | by `permission-kit:clear-cache` and `permission-kit:scan --save`.
    */
    'cache_resolved_names' => true,
    'cache_ttl' => 86400, // seconds

    /*
    |--------------------------------------------------------------------------
    | Fail-open in debug mode
    |--------------------------------------------------------------------------
    | When a resolved permission is not registered in the database (typically
    | because a new controller method hasn't been synced yet):
    |
    | - in debug environments (config('app.debug') === true) the request is
    |   allowed through with a warning logged, so development is not blocked.
    | - in production the request is denied (403), which is the safe default.
    |--------------------------------------------------------------------------
    */
    'log_unregistered_permissions' => true,
];
