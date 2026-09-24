<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoreController extends Controller
{
    protected function resolveStore(): Store
    {
        return Store::resolveFor(Auth::user());
    }

    public function show(Store $store): View
    {
        $store->load([
            'user:id,name',
            'products' => fn ($query) => $query->where('status', 'approved')->latest('id'),
            'ratings.user:id,name',
        ]);

        // Aggregates for the rating badge — avoids N+1 on ratingAverage().
        $store->loadCount('ratings')->loadAvg('ratings', 'rating');

        return view('store.show', [
            'store' => $store,
            'seoTitle' => $store->name.' — Warung Hebat',
            'seoDescription' => $store->description
                ?: 'Lihat etalase, harga, jam buka, dan lokasi '.$store->name.($store->address ? ' di '.$store->address : '').'. Pesan dari HP, diantar atau ambil sendiri.',
            'seoImage' => $store->image_url,
        ]);
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
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+0-9][0-9\s\-()]*$/'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'is_open' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama warung wajib diisi.',
            'phone.regex' => 'Nomor HP/WA tidak valid.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus di antara -90 dan 90.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus di antara -180 dan 180.',
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
            'slug' => Store::uniqueSlug($validated['name'], $store->id),
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'open_time' => $validated['open_time'] ?? null,
            'close_time' => $validated['close_time'] ?? null,
            'is_open' => $request->has('is_open') ? $request->boolean('is_open') : $store->is_open,
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Pengaturan warung disimpan.');
    }
}
