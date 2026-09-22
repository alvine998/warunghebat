<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    protected function resolveStore(): Store
    {
        return Store::resolveFor(Auth::user());
    }

    public function toggle(): RedirectResponse
    {
        $store = $this->resolveStore();
        $store->update(['is_open' => ! $store->is_open]);

        return back()->with(
            'success',
            $store->is_open ? 'Warung dibuka — pembeli bisa memesan lagi.' : 'Warung ditutup sementara.'
        );
    }

    public function edit(): View
    {
        $store = $this->resolveStore();
        $user = Auth::user();

        return view('seller.store.form', [
            'store' => $store,
            'storeStats' => $user->storeStats(),
            'lowStockProducts' => $user->lowStockProducts(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $store = $this->resolveStore();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('stores', 'slug')->ignore($store->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]*$/'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'is_open' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama warung wajib diisi.',
            'slug.required' => 'Slug warung wajib diisi.',
            'slug.alpha_dash' => 'Slug hanya boleh huruf, angka, strip, dan underscore.',
            'slug.unique' => 'Slug ini sudah dipakai warung lain.',
            'phone.regex' => 'Nomor HP/WA tidak valid.',
            'open_time.date_format' => 'Jam buka harus format JJ:MM.',
            'close_time.date_format' => 'Jam tutup harus format JJ:MM.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $imagePath = $store->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('stores', 'public');
        }

        $store->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'open_time' => $validated['open_time'] ?? null,
            'close_time' => $validated['close_time'] ?? null,
            'is_open' => $request->has('is_open') ? $request->boolean('is_open') : $store->is_open,
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Pengaturan warung disimpan.');
    }
}
