<?php

declare(strict_types=1);

namespace PermissionKit\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'permission-kit:install {--force : Overwrite existing files}';

    protected $description = 'Publish config, migrations, and print next steps.';

    public function handle(): int
    {
        $this->info('Installing Laravel Permission Kit...');
        $this->newLine();

        // Config Publishing (Optional)
        if ($this->confirm('Do you want to publish the configuration file?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'permission-kit-config',
                '--force' => (bool) $this->option('force'),
            ]);
        }

        // Migrations Publishing (Optional)
        $this->line('The package includes an optional migration to add label and description columns to Spatie permissions.');
        if ($this->confirm('Do you want to publish the optional migration?', true)) {
            $this->call('vendor:publish', [
                '--tag' => 'permission-kit-migrations',
                '--force' => (bool) $this->option('force'),
            ]);
            $this->info('Migration published. You can copy it to your other context directories if needed. Run `php artisan migrate` to apply the migration.');
        }

        $this->newLine();
        $this->info('Installed successfully.');
        $this->newLine();

        $this->info('Next steps:');
        $this->line('  1. Add the AuthorizesPermissions trait to your controllers.');
        $this->line('  2. Sync your permissions manually. Add this to your DatabaseSeeder or ContextDatabaseSeeder:');
        $this->line('     Artisan::call(\'permission-kit:sync\');');
        $this->line('     // Or for a specific context:');
        $this->line('     // Import Artisan (use Illuminate\Support\Facades\Artisan;) then:');
        $this->line('     Artisan::call(\'permission-kit:sync\', [\'--context\' => \'tenant\']);');
        $this->line('  3. Run: php artisan db:seed or your context seeder command to seed the permissions into your database.');

        return self::SUCCESS;
    }
}
