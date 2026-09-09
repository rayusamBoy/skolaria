<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * This command ensures that the SQLite database file exists before running migrations or seeders.
 * It is especially useful in CI environments where the database file may not be present by default.
 */
class EnsureSqliteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:ensure-sqlite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the SQLite database file exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = database_path('sms.sqlite');
        if (! file_exists($path)) {
            touch($path);
            $this->info("Database file created at: $path");
            chmod($path, 0644);
            $this->info("Database file permissions updated at: $path");

            return 0;
        }

        $this->info("Database file already exists at: $path");

        return 0;
    }
}
