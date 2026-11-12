<?php

declare(strict_types=1);

namespace PermissionKit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PermissionKit\Concerns\AuthorizesPermissions;
use PermissionKit\PermissionKit;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Explicit route-level permission guard.
 *
 * Attach to routes when you want a literal permission check without
 * relying on the trait-based implicit enforcement.
 *
 * ### Single permission
 *
 *     Route::get('/users', [UserController::class, 'index'])
 *         ->middleware('permission.kit:users.view');
 *
 * ### Any-of within one argument
 *
 * Permissions may be separated by a pipe (preferred) or a comma.
 * The user must hold at least one of the listed permissions.
 *
 *     ->middleware('permission.kit:users.update|users.delete');
 *     ->middleware('permission.kit:users.update,users.delete');  // same meaning
 *
 * ### All-of across arguments
 *
 * Each extra middleware argument is a separate group. Groups are AND'd,
 * so the user must satisfy every group:
 *
 *     ->middleware([
 *         'permission.kit:users.update',
 *         'permission.kit:users.delete',
 *     ]);
 *
 * ### Failure
 *
 * Throws {@see HttpException}
 * with status 403 when the authenticated user does not hold at least
 * one permission in any group. Laravel converts the exception into a
 * standard 403 response, so callers see `assertForbidden()` in tests
 * and a 403 error page in the browser.
 *
 * ### Coexistence with the trait
 *
 * This middleware is independent of {@see AuthorizesPermissions}.
 * The trait drives {@see EnforceControllerPermissions},
 * which resolves the permission automatically from the controller method.
 * This middleware is purely route-driven and always uses the permission
 * string you provide.
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        foreach ($permissions as $group) {
            $any_of = [];

            foreach (explode(',', $group) as $piped) {
                foreach (explode('|', $piped) as $permission) {
                    $permission = trim($permission);
                    if ($permission !== '') {
                        $any_of[] = $permission;
                    }
                }
            }

            if (! empty($any_of) && ! app(PermissionKit::class)->canAny($any_of)) {
                throw new HttpException(403, 'Missing any of: '.implode(', ', $any_of));
            }
        }

        return $next($request);
    }
}
