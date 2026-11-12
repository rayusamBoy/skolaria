<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The table being modified.
     */
    protected string $table = 'permissions';

    /**
     * Add optional `label` and `description` metadata columns to Spatie's
     * `permissions` table.
     *
     * The columns are entirely optional — the package detects their presence
     * at runtime via `Schema::hasColumn()` and only writes to them when they
     * exist. Consumers who already have these columns in their own
     * migrations can safely skip publishing this file.
     *
     * Both additions are guarded so the migration is idempotent — running it
     * on a project that already has the columns will not throw.
     */
    public function up(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table): void {
            if (! Schema::hasColumn($this->table, 'label')) {
                $table->string('label', 255)
                    ->nullable()
                    ->after('guard_name');
            }

            if (! Schema::hasColumn($this->table, 'description')) {
                $table->text('description')
                    ->nullable()
                    ->after('label');
            }
        });
    }

    /**
     * Remove the two metadata columns when rolling back.
     *
     * Guarded so a rollback on a project that never ran the `up()` method
     * (or that removed the columns manually) does not throw.
     */
    public function down(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        Schema::table($this->table, function (Blueprint $table): void {
            if (Schema::hasColumn($this->table, 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn($this->table, 'label')) {
                $table->dropColumn('label');
            }
        });
    }
};
