
<?php

use App\Jobs\DeleteIDCardsThemeDir;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\Support\CreatesTestTenant;

uses(CreatesTestTenant::class);

beforeEach(function () {
    Artisan::call('migrate', [
        '--database' => config('tenancy.database.central_connection'),
        '--path' => database_path('migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);
});

it('deletes the correct tenant ID card theme directory after tenancy has ended', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    $tenantId = $tenant->getTenantKey();

    $themeDir = resource_path(
        'views/pages/students/id_cards/themes/'
        .config('tenancy.database.prefix')
        .$tenantId
        .'/'
    );

    try {
        File::ensureDirectoryExists($themeDir);
        File::put($themeDir.'test-theme.php', '<?php // test');

        expect(File::isDirectory($themeDir))->toBeTrue();

        // Simulate the context after tenant deletion.
        tenancy()->end();

        (new DeleteIDCardsThemeDir($tenant))->handle();

        expect(File::exists($themeDir))->toBeFalse();
    } finally {
        File::deleteDirectory($themeDir);

        call_user_func([$this, 'destroyTestTenant']);
    }
});

it('does not delete another tenant ID card theme directory', function () {
    $tenantA = call_user_func([$this, 'createTestTenant']);
    $tenantB = call_user_func([$this, 'createAdditionalTestTenant']);

    $themeDirA = resource_path(
        'views/pages/students/id_cards/themes/'
        .config('tenancy.database.prefix')
        .$tenantA->getTenantKey()
        .'/'
    );

    $themeDirB = resource_path(
        'views/pages/students/id_cards/themes/'
        .config('tenancy.database.prefix')
        .$tenantB->getTenantKey()
        .'/'
    );

    try {
        File::ensureDirectoryExists($themeDirA);
        File::ensureDirectoryExists($themeDirB);

        File::put($themeDirA.'test-theme.php', '<?php // tenant A');
        File::put($themeDirB.'test-theme.php', '<?php // tenant B');

        tenancy()->end();

        (new DeleteIDCardsThemeDir($tenantA))->handle();

        expect(File::isDirectory($themeDirA))->toBeFalse()
            ->and(File::isDirectory($themeDirB))->toBeTrue();
    } finally {
        File::deleteDirectory($themeDirA);
        File::deleteDirectory($themeDirB);

        call_user_func([$this, 'destroyTestTenant']);
    }
});
