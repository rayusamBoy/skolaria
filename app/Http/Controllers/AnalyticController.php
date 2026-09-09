<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Analytic\GoogleSetup;
use App\Http\Requests\Analytic\Request as AnalyticRequest;
use App\Repositories\SettingRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;

class AnalyticController extends Controller
{
    protected SettingRepo $setting;

    public function __construct(SettingRepo $setting)
    {
        $this->permissionMap = config('permissions.names.tenant.analytics');
        parent::__construct();
        $this->setting = $setting;
    }

    /**
     * Display the analytics dashboard, optionally filtered by period.
     */
    public function index(array $data = []): View|RedirectResponse
    {
        $settings = Qs::getSettings();

        $property_id = $settings->firstWhere('key', 'google_analytic_property_id')->value;
        $tag_id = $settings->firstWhere('key', 'google_analytic_tag_id')->value;
        $credential_rel = $settings->firstWhere('key', 'google_analytic_service_account_credential_file')->value;
        $analytics_enabled = (bool) $settings->firstWhere('key', 'analytics_enabled')->value;

        $credential_path = storage_path("app/public/$credential_rel");
        $gtag_code_path = app_path('../../storage/app/private/analytics/google-tag-manager-code-structure.php');

        config(['analytics.property_id' => $property_id]);
        config(['analytics.service_account_credentials_json' => $credential_path]);

        $view_data = [
            'google_analytic_property_id' => $property_id,
            'google_analytic_tag_id' => $tag_id,
            'analytics_enabled' => $analytics_enabled,
        ];

        if (file_exists($credential_path)) {
            $view_data['credential_file'] = $credential_path;
        }
        if (file_exists($gtag_code_path)) {
            $view_data['gtag_code_structure_file'] = $gtag_code_path;
        }

        if (Qs::googleAnalyticsSetUpOkAndEnabled()) {
            $view_data['google_analytics_setup_ok'] = true;

            if (empty($data)) {
                $data = ['period_number' => 6, 'period_name' => 'months'];
            }

            $period = $this->resolvePeriod($data['period_name'], $data['period_number']);

            try {
                $view_data = [...$view_data, ...$this->fetchAnalyticsData($period)];
            } catch (\Exception $e) {
                unset($view_data['google_analytics_setup_ok']);
                session()->flash('pop_error', $e->getMessage());

                return view('pages.analytics', $view_data);
            }
        }

        return view('pages.analytics', $view_data);
    }

    /**
     * Fetch analytics data for a different time period.
     */
    public function fetch_data(AnalyticRequest $req): View|RedirectResponse
    {
        $params = $req->only(['period_number', 'period_name']);
        $allowed = array_keys(Qs::getSpatieAnalyticsPackagePeriods());

        if (! \in_array($params['period_name'], $allowed)) {
            return redirect()->route('analytics.index')
                ->with('flash_warning', __('msg.invalid_period_name'));
        }

        return $this->index($params);
    }

    /**
     * Save Google Analytics property, tag, and credentials.
     */
    public function google_setup(GoogleSetup $req): RedirectResponse
    {
        $property_id = $req->google_analytic_property_id;
        $tag_id = rtrim($req->google_analytic_tag_id, '_');

        if ($property_id !== null) {
            $this->setting->update('google_analytic_property_id', $property_id);
        }
        if ($tag_id !== '') {
            $this->setting->update('google_analytic_tag_id', $tag_id);
        }

        if ($req->hasFile('service_account_credential_file')) {
            $file = $req->file('service_account_credential_file');
            $meta = Qs::getFileMetaData($file);
            $store_path = 'analytics/service-acc-credential-file.'.$meta['ext'];
            $file->storeAs($store_path);
            $this->setting->update('google_analytic_service_account_credential_file', $store_path);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Convert period name and count to a Spatie Period object.
     */
    protected function resolvePeriod(string $name, int $count): Period
    {
        return match ($name) {
            'days' => Period::days($count),
            'months' => Period::months($count),
            'years' => Period::years($count),
        };
    }

    /**
     * Fetch all Google Analytics metrics and dimensions for the given period.
     */
    protected function fetchAnalyticsData(Period $period): array
    {
        return [
            'vstrsAndPgVw' => Analytics::fetchVisitorsAndPageViews($period),
            'vstrsAndPgVwByDt' => Analytics::fetchVisitorsAndPageViewsByDate($period),
            'ttlVstrsAndPgVw' => Analytics::fetchTotalVisitorsAndPageViews($period),
            'mostVstdPgs' => Analytics::fetchMostVisitedPages($period),
            'topReferrers' => Analytics::fetchTopReferrers($period),
            'userTypes' => Analytics::fetchUserTypes($period),
            'topBrowsers' => Analytics::fetchTopBrowsers($period),
            'topCountries' => Analytics::fetchTopCountries($period),
            'topOses' => Analytics::fetchTopOperatingSystems($period),
        ];
    }
}
