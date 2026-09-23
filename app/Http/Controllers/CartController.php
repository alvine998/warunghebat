<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = $this->currentCart();

        $products = Product::whereIn('id', array_keys($cart['items'] ?? []))->get()->keyBy('id');

        $cart = $this->pruneCart($cart, $products);

        return view('cart.index', [
            'cart' => $cart,
            'items' => $cart['items'] ?? [],
            'products' => $products,
            'total' => $this->cartTotal($cart),
        ]);
    }

    /**
     * Add a product to the session cart.
     *
     * @param  array{product_id?: int|string, force?: bool}  $validated
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'force' => ['sometimes', 'boolean'],
        ], [
            'product_id.required' => 'Produk tidak valid.',
            'product_id.exists' => 'Produk tidak ditemukan.',
        ]);

        $product = Product::query()
            ->whereKey($validated['product_id'])
            ->where('status', 'approved')
            ->first();

        if (! $product) {
            return back()->with('error', 'Produk ini belum tayang.');
        }

        if ($product->stock < 1) {
            return back()->with('error', 'Produk sedang habis.');
        }

        $store = Store::where('user_id', $product->user_id)->first();
        if (! $store) {
            return back()->with('error', 'Warung produk ini belum siap menerima pesanan.');
        }

        $cart = session('cart');
        $cartIsArray = is_array($cart) && ! empty($cart['store_id']);
        $hasOtherStore = $cartIsArray && (int) $cart['store_id'] !== (int) $store->id;
        $force = $request->boolean('force');

        if ($hasOtherStore && ! $force) {
            return back()->with('cart_conflict', [
                'product_id' => $product->id,
                'current_store_name' => (string) $cart['store_name'],
                'new_store_name' => $store->name,
            ]);
        }

        if (! $cartIsArray || $hasOtherStore) {
            $cart = [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'store_slug' => $store->slug,
                'items' => [],
            ];
        }

        $items = $cart['items'];
        $currentQty = (int) ($items[$product->id]['qty'] ?? 0);

        if ($currentQty >= $product->stock) {
            return back()->with('error', 'Jumlah di keranjang sudah mentok stok.');
        }

        $items[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'qty' => $currentQty + 1,
        ];

        $cart['items'] = $items;
        session(['cart' => $cart]);

        return back()->with('success', "\"{$product->name}\" ditambahkan ke keranjang.");
    }

    /** Change the quantity of one cart line, clamped to the live stock. */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:1000000'],
        ], [
            'qty.required' => 'Jumlah tidak valid.',
            'qty.min' => 'Jumlah minimal 1. Pakai tombol hapus untuk membuang produk.',
        ]);

        $cart = $this->currentCart();

        if (! isset($cart['items'][$product->id])) {
            return back()->with('error', 'Produk ini tidak ada di keranjang.');
        }

        $unavailable = $this->removeUnavailable($cart, $product);
        if ($unavailable !== null) {
            return $unavailable;
        }

        $qty = min($validated['qty'], $product->stock);

        $cart['items'][$product->id]['qty'] = $qty;
        $cart['items'][$product->id]['price'] = $product->price;
        $this->putCart($cart);

        return back()->with(
            $qty < $validated['qty'] ? 'error' : 'success',
            $qty < $validated['qty']
                ? "Jumlah disesuaikan dengan stok tersedia ({$qty} pcs)."
                : 'Jumlah di keranjang diperbarui.',
        );
    }

    public function destroy(Product $product): RedirectResponse
    {
        $cart = $this->currentCart();

        if (! isset($cart['items'][$product->id])) {
            return back()->with('error', 'Produk ini tidak ada di keranjang.');
        }

        unset($cart['items'][$product->id]);
        $this->putCart($cart);

        return back()->with('success', "\"{$product->name}\" dihapus dari keranjang.");
    }

    /**
     * Drops cart lines whose product is gone, unapproved or out of stock, and
     * re-checks quantities against live stock.
     *
     * @param  array{items?: array<int, array{product_id: int, name: string, price: int, qty: int}>}  $cart
     * @param  Collection<int, Product>  $products
     * @return array{items?: array<int, array{product_id: int, name: string, price: int, qty: int}>}
     */
    protected function pruneCart(array $cart, Collection $products): array
    {
        if (empty($cart['items'])) {
            return $cart;
        }

        $removed = false;

        foreach ($cart['items'] as $id => $item) {
            $product = $products[$id] ?? null;

            if (! $product || ! $product->isApproved() || $product->stock < 1) {
                unset($cart['items'][$id]);
                $removed = true;

                continue;
            }

            $cart['items'][$id]['qty'] = min($item['qty'], $product->stock);
            $cart['items'][$id]['price'] = $product->price;
        }

        if ($removed) {
            $this->putCart($cart);
            session()->flash('error', 'Produk yang sudah tidak tersedia dihapus dari keranjang.');
        }

        return $cart;
    }

    /** @param  array{items?: array<int, array{product_id: int, name: string, price: int, qty: int}>}  $cart */
    protected function removeUnavailable(array $cart, Product $product): ?RedirectResponse
    {
        if ($product->isApproved() && $product->stock > 0) {
            return null;
        }

        unset($cart['items'][$product->id]);
        $this->putCart($cart);

        $reason = $product->isApproved() ? 'sudah habis' : 'sudah tidak tayang';

        return back()->with('error', "\"{$product->name}\" {$reason} dan dihapus dari keranjang.");
    }

    /** @return array{store_id?: int|string, store_name?: string, store_slug?: string, items?: array<int, array{product_id: int, name: string, price: int, qty: int}>} */
    protected function currentCart(): array
    {
        $cart = session('cart');

        return is_array($cart) && ! empty($cart['store_id']) ? $cart : [];
    }

    /** @param  array{items?: array<int, array{price: int, qty: int}>}  $cart */
    protected function cartTotal(array $cart): int
    {
        return (int) collect($cart['items'] ?? [])
            ->sum(fn (array $item): int => $item['price'] * $item['qty']);
    }

    /** @param  array{items?: array<int, mixed>}  $cart */
    protected function putCart(array $cart): void
    {
        if (empty($cart['items'])) {
            session()->forget('cart');

            return;
        }

        session(['cart' => $cart]);
    }
}
