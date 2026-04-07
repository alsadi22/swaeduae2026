<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingUpdateRequest;
use App\Models\SiteSetting;
use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $setting = SiteSetting::current();
        $this->authorize('update', $setting);

        return view('admin.site-settings.edit', [
            'setting' => $setting,
        ]);
    }

    public function update(SiteSettingUpdateRequest $request): RedirectResponse
    {
        $setting = SiteSetting::current();
        $this->authorize('update', $setting);

        $data = [];
        foreach (['hero_mission_en', 'hero_mission_ar', 'hero_subline_en', 'hero_subline_ar'] as $key) {
            $val = $request->input($key);
            $data[$key] = is_string($val) && trim($val) === '' ? null : $val;
        }

        if ($request->boolean('remove_header_logo')) {
            if (filled($setting->header_logo_path)) {
                Storage::disk('public')->delete($setting->header_logo_path);
            }
            $data['header_logo_path'] = null;
        }

        if ($request->hasFile('header_logo')) {
            $old = $setting->header_logo_path;
            $path = $request->file('header_logo')->store('site', 'public');
            $data['header_logo_path'] = $path;
            if (filled($old) && $old !== $path) {
                Storage::disk('public')->delete($old);
            }
        }

        $setting->update($data);

        return redirect()
            ->route('admin.site-settings.edit', PublicLocale::queryFromRequestOrUser($request->user()))
            ->with('status', __('Site settings saved.'));
    }
}
