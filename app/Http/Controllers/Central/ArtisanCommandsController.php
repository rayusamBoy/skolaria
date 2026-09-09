<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class ArtisanCommandsController extends Controller
{
    /**
     * Command ID mapping for run_command action.
     */
    protected const array TENANT_COMMANDS = [
        '0' => 'tenants:migrate',
        '1' => 'tenants:migrate-fresh',
        '2' => 'tenants:seed',
    ];

    public function __construct()
    {
        $this->permissionMap = config('permissions.names.central.artisan_commands');
        parent::__construct();
    }

    /**
     * Display the Artisan commands dashboard.
     */
    public function index(): View
    {
        return view('pages.central.artisan_commands.index');
    }

    /**
     * Run optimize command.
     */
    public function optimize(): JsonResponse
    {
        return $this->executeArtisan('optimize');
    }

    /**
     * Run optimize:clear command.
     */
    public function optimize_clear(): JsonResponse
    {
        return $this->executeArtisan('optimize:clear');
    }

    /**
     * Run route:cache command.
     */
    public function route_cache(): JsonResponse
    {
        return $this->executeArtisan('route:cache');
    }

    /**
     * Run route:clear command.
     */
    public function route_clear(): JsonResponse
    {
        return $this->executeArtisan('route:clear');
    }

    /**
     * Run config:cache command.
     */
    public function config_cache(): JsonResponse
    {
        return $this->executeArtisan('config:cache');
    }

    /**
     * Run config:clear command.
     */
    public function config_clear(): JsonResponse
    {
        return $this->executeArtisan('config:clear');
    }

    /**
     * Run event:cache command.
     */
    public function event_cache(): JsonResponse
    {
        return $this->executeArtisan('event:cache');
    }

    /**
     * Run event:clear command.
     */
    public function event_clear(): JsonResponse
    {
        return $this->executeArtisan('event:clear');
    }

    /**
     * Run view:cache command.
     */
    public function view_cache(): JsonResponse
    {
        return $this->executeArtisan('view:cache');
    }

    /**
     * Run view:clear command.
     */
    public function view_clear(): JsonResponse
    {
        return $this->executeArtisan('view:clear');
    }

    /**
     * Publish log viewer assets.
     */
    public function publish_log_viewer(): JsonResponse
    {
        return $this->executeArtisan('vendor:publish --tag=log-viewer-assets --force');
    }

    /**
     * Clean activity logs.
     */
    public function clean_activity_logs(): JsonResponse
    {
        return $this->executeArtisan('activitylog:clean --force');
    }

    /**
     * Create storage symlink.
     */
    public function storage_link(): JsonResponse
    {
        return $this->executeArtisan('storage:link');
    }

    /**
     * Run a tenant-specific command.
     */
    public function run_command(string $command_id, $tenant): RedirectResponse
    {
        $command_name = self::TENANT_COMMANDS[$command_id] ?? null;

        if ($command_name === null) {
            return back()->with('pop_error', 'Invalid command selected.')->with('pop_timer', 0);
        }

        $response_name = str_replace(':', ' ', $command_name);

        try {
            tenancy()->initialize($tenant);

            $status = Artisan::call($command_name, [
                '--force' => true, // This needs to be true to run migrations in production.
                '--tenants' => [$tenant],
            ]);
            $output = Artisan::output();

            if ($status !== 0) {
                throw new \RuntimeException($output ?: 'Command failed silently.');
            }

            $message = "The $response_name command run successfully for the specified tenant.";

            return back()->with('pop_success', $message)->with('pop_timer', 0);
        } catch (Throwable $e) {
            Log::error("Error running $response_name for tenant ID $tenant: ".$e->getMessage(), [
                'exception' => $e,
                'tenant_id' => $tenant,
            ]);

            return back()->with('pop_error', $e->getMessage())->with('pop_timer', 0);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Execute an Artisan command and return a standardized JSON response.
     */
    protected function executeArtisan(string $command): JsonResponse
    {
        $output_buffer = new BufferedOutput;
        $response_name = str_replace([':', '--'], [' ', ' '], $command);

        try {
            $exit_code = Artisan::call($command, [], $output_buffer);
            $output = $output_buffer->fetch();

            if ($exit_code === 0) {
                return Qs::json("The $response_name command run successfully.", true);
            }

            $error_message = "The $response_name command failed.";
            if (! empty(trim($output))) {
                $error_message .= " Details: $output";
            }

            return Qs::json($error_message, false);
        } catch (Throwable $e) {
            Log::error("Error running $response_name command: ".$e->getMessage(), [
                'exception' => $e,
                'command' => $command,
            ]);

            return Qs::json("The $response_name command threw an exception: ".$e->getMessage(), false);
        }
    }
}
