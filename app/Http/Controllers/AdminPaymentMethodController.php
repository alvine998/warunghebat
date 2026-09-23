<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminPaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-methods', [
            'methods' => PaymentMethod::orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-method-form', ['method' => new PaymentMethod]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $method = PaymentMethod::create($this->attributes($request, $validated));

        return redirect()
            ->route('admin.payment-methods')
            ->with('success', "Metode \"{$method->name}\" ditambahkan dan bisa dipakai pembeli.");
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-method-form', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $this->validated($request);

        $paymentMethod->update($this->attributes($request, $validated, $paymentMethod));

        return redirect()
            ->route('admin.payment-methods')
            ->with('success', "Metode \"{$paymentMethod->name}\" diperbarui.");
    }

    public function toggle(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back()->with('success', $paymentMethod->is_active
            ? "Metode \"{$paymentMethod->name}\" diaktifkan."
            : "Metode \"{$paymentMethod->name}\" dimatikan.");
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->image_path) {
            Storage::disk('public')->delete($paymentMethod->image_path);
        }

        $paymentMethod->delete();

        return back()->with('success', "Metode \"{$paymentMethod->name}\" dihapus.");
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:'.implode(',', PaymentMethod::TYPES)],
            'name' => ['required', 'string', 'max:80'],
            'account_number' => ['nullable', 'required_unless:type,qris', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:80'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], [
            'type.required' => 'Tipe metode wajib dipilih.',
            'type.in' => 'Tipe metode tidak valid.',
            'name.required' => 'Nama metode wajib diisi.',
            'account_number.required_unless' => 'Nomor rekening wajib diisi untuk transfer bank dan e-wallet.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function attributes(Request $request, array $validated, ?PaymentMethod $method = null): array
    {
        $attributes = [
            'type' => $validated['type'],
            'name' => $validated['name'],
            'account_number' => $validated['account_number'] ?? null,
            'account_name' => $validated['account_name'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            if ($method?->image_path) {
                Storage::disk('public')->delete($method->image_path);
            }

            $attributes['image_path'] = $request->file('image')->store('payment-methods', 'public');
        }

        return $attributes;
    }
}
