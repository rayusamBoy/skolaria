<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use ColorThief\ColorThief;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    protected SettingRepo $setting;
    protected MyClassRepo $my_class;

    public function __construct(SettingRepo $setting, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.central.settings_non_tenancy');
        parent::__construct();
        $this->setting = $setting;
        $this->my_class = $my_class;
    }

    /**
     * Display the central settings page.
     */
    public function index(): View
    {
        return view('pages.central.settings');
    }

    /**
     * Update system settings.
     */
    public function update(Request $req): RedirectResponse
    {
        // Update text-based settings in batch
        $this->updateTextSettings($req->except(['_token', '_method', 'logo', 'login_and_related_pages_bg']));

        // Handle background image upload and related color settings
        if ($req->hasFile('login_and_related_pages_bg')) {
            $this->handleBackgroundImageUpload($req);
        }

        return redirect()
            ->route('settings_non_tenancy.index')
            ->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Preview the application's login form.
     */
    public function preview_login_form(): View
    {
        return view('auth.login');
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Batch update simple text settings.
     */
    protected function updateTextSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setting->update($key, $value);
        }
    }

    /**
     * Process background image upload and update related settings.
     */
    protected function handleBackgroundImageUpload(Request $req): void
    {
        $file = $req->file('login_and_related_pages_bg');
        $file_meta = Qs::getFileMetaData($file);
        $file_name = 'login_and_related_pages_bg.' . $file_meta['ext'];

        // Store file in public upload path
        $storage_path = Qs::getPublicUploadPath() . $file_name;
        $file->storeAs($storage_path);

        // Update setting with accessible URL
        $public_path = 'storage/' . Qs::getPublicUploadPath() . '/' . $file_name;
        $this->setting->update('login_and_related_pages_bg', $public_path);

        // Handle text/background colors based on user selection
        $this->updateColorSettings($req, $file);

        // Set session flash for preview if requested
        if ($req->has('show_login_and_related_pgs_preview')) {
            session()->flash('show_login_and_related_pgs_preview', true);
        }
    }

    /**
     * Update text and background color settings based on user choice.
     */
    protected function updateColorSettings(Request $req, $file): void
    {
        $color_mode = $req->input('texts_and_bg_colors');

        if ($color_mode === 'from_img') {
            try {
                $dominant_color = (array) ColorThief::getColor($file->getPathname());
                $bg_color = \sprintf('rgb(%d,%d,%d)', ...$dominant_color);
                $texts_color = Qs::get_color_from_color($bg_color);
                $colors = $texts_color . Qs::getDelimiter() . $bg_color;

                $this->setting->update('login_and_related_pgs_txts_and_bg_colors', $colors);
            } catch (\Exception $e) {
                // Log error but don't break the update process
                logger()->error('Failed to extract dominant color from image: ' . $e->getMessage());
                $this->setting->update('login_and_related_pgs_txts_and_bg_colors', null);
            }
        } else {
            $this->setting->update('login_and_related_pgs_txts_and_bg_colors', null);
        }
    }
}
