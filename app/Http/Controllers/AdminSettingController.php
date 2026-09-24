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
            'csWhatsapp' => Setting::csWhatsapp(),
            'officialEmail' => Setting::officialEmail(),
            'officeAddress' => Setting::officeAddress(),
            'operationalDays' => Setting::operationalDays(),
            'operationalHours' => Setting::operationalHours(),
            'operationalNote' => Setting::operationalNote(),
            'socialInstagram' => Setting::socialInstagram(),
            'socialTiktok' => Setting::socialTiktok(),
            'socialX' => Setting::socialX(),
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

        // "08xx", "+62...", "wa.me/..." all resolve to digits for the wa.me link.
        if ($request->filled(Setting::CS_WHATSAPP)) {
            $request->merge([Setting::CS_WHATSAPP => preg_replace('/\D/', '', (string) $request->input(Setting::CS_WHATSAPP))]);
        }

        $validated = $request->validate([
            'withdrawal_min' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'withdrawal_max' => ['required', 'integer', 'min:0', 'max:1000000000', 'gte:withdrawal_min'],
            'commission_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'cs_whatsapp' => ['required', 'string', 'regex:/^62\d{8,14}$/'],
            'official_email' => ['required', 'email', 'max:255'],
            'office_address' => ['required', 'string', 'max:500'],
            'operational_days' => ['required', 'string', 'max:100'],
            'operational_hours' => ['required', 'string', 'max:100'],
            'operational_note' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'url:http,https', 'max:255'],
            'social_tiktok' => ['nullable', 'url:http,https', 'max:255'],
            'social_x' => ['nullable', 'url:http,https', 'max:255'],
        ], [
            'withdrawal_min.required' => 'Batas minimal penarikan wajib diisi.',
            'withdrawal_max.required' => 'Batas maksimal penarikan wajib diisi.',
            'withdrawal_max.gte' => 'Batas maksimal harus lebih besar atau sama dengan batas minimal.',
            'commission_percent.required' => 'Komisi platform wajib diisi.',
            'commission_percent.min' => 'Komisi tidak boleh negatif.',
            'commission_percent.max' => 'Komisi maksimal 100%.',
            'cs_whatsapp.required' => 'WhatsApp CS wajib diisi.',
            'cs_whatsapp.regex' => 'Nomor WhatsApp harus diawali 62 dan berisi 10–16 digit angka.',
            'official_email.required' => 'Email resmi wajib diisi.',
            'official_email.email' => 'Format email resmi tidak valid.',
            'office_address.required' => 'Alamat kantor wajib diisi.',
            'operational_days.required' => 'Hari operasional wajib diisi.',
            'operational_hours.required' => 'Jam operasional wajib diisi.',
            'social_instagram.url' => 'Link Instagram harus URL yang valid (http atau https).',
            'social_tiktok.url' => 'Link TikTok harus URL yang valid (http atau https).',
            'social_x.url' => 'Link X harus URL yang valid (http atau https).',
        ]);

        Setting::setValue(Setting::WITHDRAWAL_MIN, $validated['withdrawal_min']);
        Setting::setValue(Setting::WITHDRAWAL_MAX, $validated['withdrawal_max']);
        Setting::setValue(Setting::COMMISSION_PERCENT, $validated['commission_percent']);
        Setting::setValue(Setting::CS_WHATSAPP, $validated['cs_whatsapp']);
        Setting::setValue(Setting::OFFICIAL_EMAIL, $validated['official_email']);
        Setting::setValue(Setting::OFFICE_ADDRESS, $validated['office_address']);
        Setting::setValue(Setting::OPERATIONAL_DAYS, $validated['operational_days']);
        Setting::setValue(Setting::OPERATIONAL_HOURS, $validated['operational_hours']);
        Setting::setValue(Setting::OPERATIONAL_NOTE, $validated['operational_note'] ?? null);
        Setting::setValue(Setting::SOCIAL_INSTAGRAM, $validated['social_instagram'] ?? null);
        Setting::setValue(Setting::SOCIAL_TIKTOK, $validated['social_tiktok'] ?? null);
        Setting::setValue(Setting::SOCIAL_X, $validated['social_x'] ?? null);

        return back()->with('success', 'Pengaturan platform disimpan.');
    }
}
