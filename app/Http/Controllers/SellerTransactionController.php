<?php

namespace App\Http\Controllers;

use App\Models\InStoreTransaction;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SellerTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'buyer_type' => ['nullable', 'in:'.InStoreTransaction::BUYER_REGISTERED.','.InStoreTransaction::BUYER_WALK_IN],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'buyer_type.in' => 'Jenis pembeli tidak valid.',
            'from.date' => 'Tanggal awal tidak valid.',
            'until.date' => 'Tanggal akhir tidak valid.',
            'until.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $store = Store::resolveFor($request->user());
        $baseQuery = $store->inStoreTransactions()
            ->when($validated['buyer_type'] ?? null, fn ($query, $buyerType) => $query->where('buyer_type', $buyerType))
            ->when($validated['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($validated['until'] ?? null, fn ($query, $until) => $query->whereDate('created_at', '<=', $until));

        $summary = [
            'count' => (clone $baseQuery)->count(),
            'total' => (int) (clone $baseQuery)->sum('total'),
            'registered_count' => (clone $baseQuery)->where('buyer_type', InStoreTransaction::BUYER_REGISTERED)->count(),
            'registered_total' => (int) (clone $baseQuery)->where('buyer_type', InStoreTransaction::BUYER_REGISTERED)->sum('total'),
            'walk_in_count' => (clone $baseQuery)->where('buyer_type', InStoreTransaction::BUYER_WALK_IN)->count(),
            'walk_in_total' => (int) (clone $baseQuery)->where('buyer_type', InStoreTransaction::BUYER_WALK_IN)->sum('total'),
        ];

        return view('seller.transactions.index', [
            'store' => $store,
            'transactions' => (clone $baseQuery)->with(['buyer', 'items'])->latest()->paginate(15)->withQueryString(),
            'summary' => $summary,
        ]);
    }

    public function create(): View
    {
        $store = Store::resolveFor(Auth::user());

        return view('seller.transactions.create', [
            'store' => $store,
            'products' => $store->products()->where('status', 'approved')->where('stock', '>', 0)->orderBy('name')->get(),
            'transaction' => null,
        ]);
    }

    public function edit(InStoreTransaction $transaction): View
    {
        $store = Store::resolveFor(Auth::user());
        $transaction = $store->inStoreTransactions()->with('items')->findOrFail($transaction->id);
        $existingProductIds = $transaction->items->pluck('product_id')->filter()->all();
        $products = $store->products()
            ->where(function ($query) use ($existingProductIds): void {
                $query->where(function ($query): void {
                    $query->where('status', 'approved')->where('stock', '>', 0);
                });

                if ($existingProductIds !== []) {
                    $query->orWhereIn('id', $existingProductIds);
                }
            })
            ->orderBy('name')
            ->get();

        return view('seller.transactions.create', compact('store', 'products', 'transaction'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTransaction($request);
        $buyer = $this->resolveRegisteredBuyer($validated);

        if ($validated['buyer_type'] === InStoreTransaction::BUYER_REGISTERED && ! $buyer) {
            return back()->withErrors(['buyer_identifier' => 'Akun pembeli tidak ditemukan. Periksa email atau nomor HP, atau pilih pembeli langsung.'])->withInput();
        }

        $buyerDetails = $this->buyerDetails($validated);
        $store = Store::resolveFor($request->user());
        $transaction = InStoreTransaction::recordSale(
            $store,
            $validated['items'],
            $validated['buyer_type'],
            $buyer,
            $buyerDetails,
        );

        return redirect()->route('seller.transactions.index')
            ->with('success', 'Transaksi langsung sebesar Rp '.number_format($transaction->total, 0, ',', '.').' berhasil dicatat.');
    }

    public function update(Request $request, InStoreTransaction $transaction): RedirectResponse
    {
        $store = Store::resolveFor($request->user());
        $transaction = $store->inStoreTransactions()->findOrFail($transaction->id);
        $preserveDeletedItems = ! $transaction->items()->whereNotNull('product_id')->exists()
            && $transaction->items()->whereNull('product_id')->exists();
        $validated = $this->validateTransaction($request, $preserveDeletedItems);
        $buyer = $this->resolveRegisteredBuyer($validated);

        if ($validated['buyer_type'] === InStoreTransaction::BUYER_REGISTERED && ! $buyer) {
            return back()->withErrors(['buyer_identifier' => 'Akun pembeli tidak ditemukan. Periksa email atau nomor HP, atau pilih pembeli langsung.'])->withInput();
        }

        $transaction->updateSale(
            $store,
            $validated['items'] ?? [],
            $validated['buyer_type'],
            $buyer,
            $this->buyerDetails($validated),
        );

        return redirect()->route('seller.transactions.index')
            ->with('success', 'Transaksi langsung berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validateTransaction(Request $request, bool $preserveDeletedItems = false): array
    {
        return $request->validate([
            'buyer_type' => ['required', 'in:'.InStoreTransaction::BUYER_REGISTERED.','.InStoreTransaction::BUYER_WALK_IN],
            'buyer_mode' => ['exclude_unless:buyer_type,'.InStoreTransaction::BUYER_WALK_IN, 'nullable', 'in:identified,anonymous'],
            'buyer_identifier' => ['nullable', 'required_if:buyer_type,'.InStoreTransaction::BUYER_REGISTERED, 'string', 'max:255'],
            'buyer_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::when(
                    $request->input('buyer_type') === InStoreTransaction::BUYER_WALK_IN && $request->input('buyer_mode', 'identified') === 'identified',
                    ['required'],
                ),
            ],
            'buyer_email' => ['nullable', 'email', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'items' => $preserveDeletedItems ? ['nullable', 'array', 'max:50'] : ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct:strict'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'buyer_type.required' => 'Jenis pembeli wajib dipilih.',
            'buyer_type.in' => 'Jenis pembeli tidak valid.',
            'buyer_mode.required_if' => 'Pilih data pembeli langsung atau anonim.',
            'buyer_name.required' => 'Nama pembeli langsung wajib diisi.',
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.min' => 'Tambahkan minimal satu produk.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dimasukkan dua kali.',
            'items.*.qty.required' => 'Jumlah produk wajib diisi.',
            'items.*.qty.min' => 'Jumlah produk minimal 1.',
        ]);
    }

    /** @param array<string, mixed> $validated */
    private function resolveRegisteredBuyer(array $validated): ?User
    {
        if ($validated['buyer_type'] !== InStoreTransaction::BUYER_REGISTERED) {
            return null;
        }

        $identifier = trim((string) ($validated['buyer_identifier'] ?? ''));
        $normalizedPhone = preg_replace('/\D/', '', $identifier);
        $normalizedPhone = str_starts_with($normalizedPhone, '62') ? '0'.substr($normalizedPhone, 2) : $normalizedPhone;

        return User::query()
            ->whereIn('role', ['pembeli', 'penjual'])
            ->where(function ($query) use ($identifier, $normalizedPhone): void {
                $query->where('email', $identifier);

                if ($normalizedPhone !== '') {
                    $query->orWhere('phone', $normalizedPhone);
                }
            })
            ->first();
    }

    /** @param array<string, mixed> $validated
     * @return array{name: ?string, email: ?string, phone: ?string}
     */
    private function buyerDetails(array $validated): array
    {
        if ($validated['buyer_type'] !== InStoreTransaction::BUYER_WALK_IN || ($validated['buyer_mode'] ?? 'identified') === 'anonymous') {
            return ['name' => null, 'email' => null, 'phone' => null];
        }

        return [
            'name' => $validated['buyer_name'] ?? null,
            'email' => $validated['buyer_email'] ?? null,
            'phone' => $validated['buyer_phone'] ?? null,
        ];
    }
}
