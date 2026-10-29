<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function index()
    {
        $allSettings = \App\Models\Setting::all();

        $grouped = $allSettings->groupBy(function ($setting) {
            return $setting->group_name ?? 'general';
        });

        return view('admin.settings.index', [
            'settings' => $grouped,
        ]);
    }

    public function update(Request $request)
    {
        $this->settingService->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
        ]);
    }
}
