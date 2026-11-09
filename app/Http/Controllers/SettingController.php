<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\SettingUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use App\Services\FeatureModuleService;
use ColorThief\ColorThief;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SettingController extends Controller
{
    public function __construct(protected SettingRepo $setting, protected MyClassRepo $my_class, protected FeatureModuleService $modules)
    {
        $this->permissionMap = config('permissions.names.tenant.settings');
        parent::__construct();
    }

    /**
     * Display the settings page.
     */
    public function index(): View
    {
        $data['class_types'] = $this->my_class->getAllClassTypes();

        return view('pages.settings', $data);
    }

    /**
     * Update system settings.
     */
    public function update(SettingUpdate $req): RedirectResponse
    {
        // Update flat settings (exclude files)
        $settings = $req->except(['_token', '_method', 'logo', 'login_and_related_pages_bg']);
        $settings['lock_exam'] = $settings['lock_exam'] == 1 ? 1 : 0;

        foreach ($settings as $key => $value) {
            $this->setting->update($key, $value);
        }

        // Handle logo upload
        if ($req->hasFile('logo')) {
            $this->uploadAndUpdateSetting($req->file('logo'), 'logo', 'logo');
        }

        // Handle background image and color settings
        if ($req->hasFile('login_and_related_pages_bg')) {
            $this->uploadAndUpdateSetting(
                $req->file('login_and_related_pages_bg'),
                'login_and_related_pages_bg',
                'login_and_related_pages_bg'
            );

            $this->updateColorsFromBackground($req, $req->file('login_and_related_pages_bg'));

            if ($req->has('show_login_and_related_pgs_preview')) {
                session()->flash('show_login_and_related_pgs_preview', true);
            }
        }

        return redirect()->route('settings.index')->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Enable analytics tracking.
     */
    public function enable_analytics(): RedirectResponse
    {
        $this->setting->update('analytics_enabled', true);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Disable analytics tracking.
     */
    public function disable_analytics(): RedirectResponse
    {
        $this->setting->update('analytics_enabled', false);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Preview the login form.
     */
    public function preview_login_form(): View
    {
        return view('auth.login');
    }

    public function toggle_feature_module(Request $request, string $module): JsonResponse
    {
        if (! $this->modules->isToggleable($module)) {
            throw new NotFoundHttpException;
        }

        $request->validate(['enabled' => 'required|boolean']);

        $request->boolean('enabled')
            ? $this->modules->enable($module)
            : $this->modules->disable($module);

        return Qs::jsonUpdateOk();
    }

    // ----------------------------------------------------------------------
    // Protected helper methods
    // ----------------------------------------------------------------------

    /**
     * Upload a file and update the corresponding setting with its storage path.
     */
    protected function uploadAndUpdateSetting(UploadedFile $file, string $setting_key, string $base_filename): void
    {
        $file_meta = Qs::getFileMetaData($file);
        $file_meta['name'] = "$base_filename.".$file_meta['ext'];
        $file_meta['path'] = Qs::getPublicUploadPath().'/'.$file_meta['name'];

        $file->storeAs($file_meta['path']);
        $this->setting->update($setting_key, $file_meta['path']);
    }

    /**
     * Extract dominant color from the uploaded background image and update colors.
     */
    protected function updateColorsFromBackground(SettingUpdate $req, UploadedFile $file): void
    {
        try {
            if ($req->input('texts_and_bg_colors') === 'from_img') {
                $dominant_color = (array) ColorThief::getColor($file->getPathname());
                $bg_color = \sprintf('rgb(%d,%d,%d)', ...$dominant_color);
                $texts_color = Qs::getContrastColor($bg_color);
                $colors = $texts_color.Qs::getDelimiter().$bg_color;
                $this->setting->update('login_and_related_pgs_txts_and_bg_colors', $colors);
            } else {
                $this->setting->update('login_and_related_pgs_txts_and_bg_colors', null);
            }
        } catch (\Throwable $e) {
            logger()->error('Failed to extract dominant color: '.$e->getMessage());
            $this->setting->update('login_and_related_pgs_txts_and_bg_colors', null);
        }
    }
}
