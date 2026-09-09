<?php

namespace App\Console\Commands;

use App\Repositories\TenantRepo;
use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;

/**
 * This command generates screenshots for the PWA dashboard across various roles and pages.
 * It uses the Browsershot package to automate a headless Chrome instance, performing logins and navigating to specified routes before capturing screenshots.
 * The configuration for which pages to capture, the credentials to use, and the output paths are all defined in the config/pwa-screenshots.php file.
 *
 * This is intended as a developer tool to keep the PWA screenshots up to date, ensuring they reflect the current state of the application.
 */
class GeneratePwaScreenshots extends Command
{
    protected $signature = 'pwa:generate-screenshots';

    protected $description = 'Automatically capture PWA screenshots for all configured roles and pages';

    public function handle(): int
    {
        $config = config('pwa-screenshots');
        $tenantRepo = app(TenantRepo::class);

        foreach ($config['jobs'] as $job) {
            $this->captureJob($job, $config, $tenantRepo);
        }

        $this->info('All screenshots generated successfully.');

        return 0;
    }

    /**
     * Process a single job: log in once, then capture all its pages.
     */
    protected function captureJob(array $job, array $config, TenantRepo $tenantRepo): void
    {
        $this->info("Processing job: {$job['name']} ({$job['context']})");

        // --- Resolve base URL ---
        $baseUrl = $job['context'] === 'central'
            ? rtrim(config('app.url'), '/')
            : $this->resolveTenantBaseUrl($job['tenant_id'], $tenantRepo);

        $loginPath = $job['context'] === 'central'
            ? $config['central_login_url']
            : $config['tenant_login_url'];
        $loginUrl = "$baseUrl$loginPath";

        // --- Public pages (no login) ---
        if (! empty($job['skip_auth'])) {
            $this->capturePages($job, $config, $baseUrl, null);

            return;
        }

        // --- Fetch credentials ---
        $credsKey = $job['credentials'] ?? '';
        $creds = $config['credentials'][$credsKey] ?? null;
        if (! $creds) {
            $this->error("Credentials key '{$credsKey}' not found.");

            return;
        }

        $identitySel = 'input[name="identity"]';
        $passwordSel = 'input[name="password"]';
        $submitSel = 'button[type="submit"]';

        // --- Perform login ---
        $browser = Browsershot::url($loginUrl)
            ->windowSize(1280, 720)
            ->waitUntilNetworkIdle()
            ->waitForSelector($identitySel, ['timeout' => 8000]) // ← wait for the email field
            ->type($identitySel, $creds['identity'])
            ->type($passwordSel, $creds['password'])
            ->click($submitSel)
            ->waitForSelector('.navbar', ['timeout' => 10000]); // ← wait for dashboard navbar

        // Give the dashboard a moment to settle after login redirect
        usleep($config['post_login_wait_ms'] * 1000);

        // --- Capture each page ---
        $this->capturePages($job, $config, $baseUrl, $browser);
    }

    /**
     * Loop through the job's pages and capture each one.
     */
    protected function capturePages(array $job, array $config, string $baseUrl, ?Browsershot $browser): void
    {
        foreach ($job['pages'] as $page) {
            $viewport = $page['viewport'];
            $size = $config['viewports'][$viewport];
            $route = route($page['route'], [], false);
            $url = "$baseUrl$route";
            $output = $config['output_directory'].'/'.$page['output'];

            $this->info("  → Capturing {$page['output']} ({$viewport})"
                .(isset($page['theme']) ? " theme={$page['theme']}" : '')
                .(isset($page['skin']) ? " skin={$page['skin']}" : ''));

            if ($browser === null) {
                // Public page – no session
                Browsershot::url($url)
                    ->windowSize($size['width'], $size['height'])
                    ->setScreenshotType('png')
                    ->save($output);

                continue;
            }

            // --- Handle theme/skin via the helper page ---
            $theme = $page['theme'] ?? null;
            $skin = $page['skin'] ?? null;
            if ($theme || $skin) {
                // Build the URL of our theme‑preferences helper
                $setThemeUrl = "$baseUrl/pwa-set-preferences?"
                    .($theme ? 'theme='.urlencode($theme).'&' : '')
                    .($skin ? 'skin='.urlencode($skin).'&' : '')
                    .'url='.urlencode($url);

                $browser->setUrl($setThemeUrl);
                // The helper will redirect – wait for the real page’s navbar
                $browser->waitForSelector('.navbar', ['timeout' => 15000]);
                // Give the page a moment to fully render after redirect
                $browser->setDelay(2000);
            } else {
                // No theme change – go directly to the page
                $browser
                    ->setUrl($url)
                    ->waitUntilNetworkIdle()
                    ->setDelay($config['post_navigate_wait_ms']);
            }

            // Wait for a stable content element
            $browser->waitForSelector('.navbar', ['timeout' => 10000]);

            // Final settle
            $browser->setDelay(10000);

            // Capture screenshot
            $browser->fullPage()
                ->windowSize($size['width'], $size['height'])
                ->setScreenshotType('png')
                ->save($output);
        }
    }

    /**
     * Build the tenant base URL depending on domain identification.
     */
    protected function resolveTenantBaseUrl(int $tenantId, TenantRepo $tenantRepo): string
    {
        $tenant = $tenantRepo->findOrFailTenant($tenantId);
        if (! $tenant) {
            throw new \RuntimeException("Tenant {$tenantId} not found.");
        }

        $domain = \is_string($tenant->domain) ? $tenant->domain : $tenant->domain->domain;

        return rtrim(tenant_route($domain, 'index'), '/');
    }
}
