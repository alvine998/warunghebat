<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'withdrawalMin' => Setting::withdrawalMin(),
            'withdrawalMax' => Setting::withdrawalMax(),
            'commissionPercent' => Setting::commissionPercent(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Inputs are shown with thousand separators ("50.000"); keep only digits.
        foreach ([Setting::WITHDRAWAL_MIN, Setting::WITHDRAWAL_MAX] as $key) {
            if ($request->filled($key)) {
                $request->merge([$key => preg_replace('/\D/', '', (string) $request->input($key))]);
            }
        }

        $validated = $request->validate([
            'withdrawal_min' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'withdrawal_max' => ['required', 'integer', 'min:0', 'max:1000000000', 'gte:withdrawal_min'],
            'commission_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'withdrawal_min.required' => 'Batas minimal penarikan wajib diisi.',
            'withdrawal_max.required' => 'Batas maksimal penarikan wajib diisi.',
            'withdrawal_max.gte' => 'Batas maksimal harus lebih besar atau sama dengan batas minimal.',
            'commission_percent.required' => 'Komisi platform wajib diisi.',
            'commission_percent.min' => 'Komisi tidak boleh negatif.',
            'commission_percent.max' => 'Komisi maksimal 100%.',
        ]);

        Setting::setValue(Setting::WITHDRAWAL_MIN, $validated['withdrawal_min']);
        Setting::setValue(Setting::WITHDRAWAL_MAX, $validated['withdrawal_max']);
        Setting::setValue(Setting::COMMISSION_PERCENT, $validated['commission_percent']);

        return back()->with('success', 'Pengaturan platform disimpan.');
    }
}
