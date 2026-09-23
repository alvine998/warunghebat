<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Orders are the root of the money demo: creating them a second time would
        // move stock again, so leave an already-seeded database alone.
        if (Order::exists()) {
            $this->command?->info('Orders already seeded, skipping.');

            return;
        }

        foreach ($this->blueprint() as $data) {
            $buyer = User::where('email', $data['buyer'])->first();
            $store = Store::where('slug', $data['store'])->first();

            if (! $buyer || ! $store) {
                continue;
            }

            $items = $this->resolveItems($store, $data['items']);

            if ($items === null) {
                continue;
            }

            $order = Order::placeFromCart(['store_id' => $store->id, 'items' => $items], $buyer);

            $this->applyState($order, $data['status'], $this->timestampFor($data['days_ago']));
        }
    }

    /** Moves a fresh order to its intended state, then backdates it. */
    private function applyState(Order $order, string $status, Carbon $when): void
    {
        if ($status === Order::STATUS_CANCELLED) {
            $order->cancel();
        } elseif ($status === Order::STATUS_COMPLETED) {
            $order->update([
                'status' => Order::STATUS_COMPLETED,
                'commission_amount' => (int) round($order->total * Setting::commissionPercent() / 100),
                'completed_at' => $when,
            ]);
        } else {
            $order->update(['status' => $status]);
        }

        $order->created_at = $when;
        $order->updated_at = $when;
        $order->save();
    }

    /**
     * @param  list<array{0: string, 1: int}>  $items
     * @return list<array{product_id: int, qty: int}>|null
     */
    private function resolveItems(Store $store, array $items): ?array
    {
        $resolved = [];

        foreach ($items as [$name, $qty]) {
            $product = Product::where('user_id', $store->user_id)->where('name', $name)->first();

            if (! $product || ! $product->isApproved() || $product->stock < $qty) {
                $this->command?->warn("Skipping an order: \"{$name}\" is not available.");

                return null;
            }

            $resolved[] = ['product_id' => $product->id, 'qty' => $qty];
        }

        return $resolved;
    }

    private function timestampFor(int $daysAgo): Carbon
    {
        return $daysAgo === 0
            ? now()->subHour()
            : now()->subDays($daysAgo)->setTime(10, 0);
    }

    /**
     * One order per workflow state, so every admin queue has something to act on.
     *
     * @return list<array{buyer: string, store: string, items: list<array{0: string, 1: int}>, status: string, days_ago: int}>
     */
    private function blueprint(): array
    {
        return [
            [
                'buyer' => 'dewi@example.com',
                'store' => 'warung-bang-jago',
                'items' => [['Nasi Goreng Tek-Tek', 2], ['Es Teh Manis', 2]],
                'status' => Order::STATUS_COMPLETED,
                'days_ago' => 3,
            ],
            [
                'buyer' => 'agus@example.com',
                'store' => 'warung-bu-siti',
                'items' => [['Beras Premium 5kg', 1], ['Minyak Goreng 2L', 1]],
                'status' => Order::STATUS_COMPLETED,
                'days_ago' => 2,
            ],
            [
                'buyer' => 'putri@example.com',
                'store' => 'kedai-kopi-hebat',
                'items' => [['Kopi Susu Gula Aren', 2]],
                'status' => Order::STATUS_PAID,
                'days_ago' => 1,
            ],
            [
                'buyer' => 'dewi@example.com',
                'store' => 'warung-bang-jago',
                'items' => [['Nasi Goreng Tek-Tek', 1]],
                'status' => Order::STATUS_WAITING_VERIFICATION,
                'days_ago' => 0,
            ],
            [
                'buyer' => 'test@example.com',
                'store' => 'warung-bu-siti',
                'items' => [['Gula Pasir 1kg', 2]],
                'status' => Order::STATUS_PENDING_PAYMENT,
                'days_ago' => 0,
            ],
            [
                'buyer' => 'putri@example.com',
                'store' => 'warung-bang-jago',
                'items' => [['Mie Ayam Bangka', 2]],
                'status' => Order::STATUS_CANCELLED,
                'days_ago' => 5,
            ],
        ];
    }
}
