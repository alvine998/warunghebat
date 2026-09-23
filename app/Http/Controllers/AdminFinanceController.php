<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminFinanceController extends Controller
{
    public function index(): View
    {
        $position = $this->position();
        $inflow = $this->inflow();
        $stores = $this->perStore();

        return view('admin.finance', [
            'position' => $position,
            'lifetime' => $this->lifetime(),
            'queues' => $this->queues(),
            'inflow' => $inflow,
            'stores' => $stores,
            'methods' => $this->perMethod(),
            'charts' => $this->charts($position, $inflow, $stores),
        ]);
    }

    /**
     * Series and labels for the ApexCharts rendered on this page.
     *
     * @param  array<string, int>  $position
     * @param  array{days: list<array{date: Carbon, count: int, total: int}>, total: int}  $inflow
     * @param  Collection<int, array<string, mixed>>  $stores
     * @return array<string, array<string, list<string>|list<int>>>
     */
    private function charts(array $position, array $inflow, Collection $stores): array
    {
        $statusCounts = $this->orderStatusCounts();

        return [
            'inflow' => [
                'categories' => array_map(fn (array $day): string => $day['date']->format('d M'), $inflow['days']),
                'series' => array_values(array_map(fn (array $day): int => $day['total'], $inflow['days'])),
            ],
            'position' => [
                'labels' => ['Dana ditahan', 'Saldo warung', 'Penarikan menunggu'],
                'series' => [$position['escrow'], $position['wallets'], $position['withdrawals_pending']],
            ],
            'stores' => [
                'categories' => $stores->take(6)->pluck('name')->all(),
                'series' => $stores->take(6)->pluck('completed_total')->all(),
            ],
            // Built from Order::STATUSES so labels and counts keep the same order
            // as the colour list in the chart config.
            'statuses' => [
                'labels' => array_map(fn (string $status): string => Order::STATUS_LABELS[$status], Order::STATUSES),
                'series' => array_values($statusCounts),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function orderStatusCounts(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(Order::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    /**
     * Where the money sits right now.
     *
     * @return array<string, int>
     */
    private function position(): array
    {
        $wallets = (int) Wallet::sum('balance');
        $withdrawalsPending = (int) Withdrawal::where('status', Withdrawal::STATUS_PENDING)->sum('amount');

        return [
            // Buyer money the platform holds until the order is finished.
            'escrow' => (int) Order::where('status', Order::STATUS_PAID)->sum('total'),
            'escrow_count' => Order::where('status', Order::STATUS_PAID)->count(),
            // Already released to warungs and not yet paid out.
            'wallets' => $wallets,
            'withdrawals_pending' => $withdrawalsPending,
            'withdrawals_pending_count' => Withdrawal::where('status', Withdrawal::STATUS_PENDING)->count(),
            // Platform revenue, kept from completed orders.
            'commission' => (int) Order::where('status', Order::STATUS_COMPLETED)->sum('commission_amount'),
            'commission_count' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'released' => (int) $this->orderReleaseCredits()->sum('amount'),
            'liability' => $wallets + $withdrawalsPending,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function lifetime(): array
    {
        return [
            'orders_value' => (int) Order::where('status', '!=', Order::STATUS_CANCELLED)->sum('total'),
            'orders_count' => Order::where('status', '!=', Order::STATUS_CANCELLED)->count(),
            'paid_out' => (int) Withdrawal::where('status', Withdrawal::STATUS_PAID)->sum('amount'),
            'cancelled_value' => (int) Order::where('status', Order::STATUS_CANCELLED)->sum('total'),
            'cancelled_count' => Order::where('status', Order::STATUS_CANCELLED)->count(),
        ];
    }

    /**
     * @return array<string, array<string, int>>
     */
    private function queues(): array
    {
        return [
            'payments' => [
                'count' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'total' => (int) Payment::where('status', Payment::STATUS_PENDING)->sum('amount'),
            ],
            'payments_rejected' => [
                'count' => Payment::where('status', Payment::STATUS_REJECTED)->count(),
                'total' => (int) Payment::where('status', Payment::STATUS_REJECTED)->sum('amount'),
            ],
        ];
    }

    /**
     * Money actually received, day by day, from verified transfer proofs.
     *
     * @return array{days: list<array{date: Carbon, count: int, total: int}>, total: int}
     */
    private function inflow(): array
    {
        $rows = Payment::where('status', Payment::STATUS_VERIFIED)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(verified_at) AS day, COUNT(*) AS payments, COALESCE(SUM(amount), 0) AS total')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = collect(range(6, 0))
            ->map(function (int $daysAgo) use ($rows): array {
                $date = now()->subDays($daysAgo)->startOfDay();
                $row = $rows->get($date->toDateString());

                return [
                    'date' => $date,
                    'count' => (int) ($row->payments ?? 0),
                    'total' => (int) ($row->total ?? 0),
                ];
            })
            ->all();

        return [
            'days' => $days,
            'total' => array_sum(array_column($days, 'total')),
        ];
    }

    /**
     * Per-warung money rows, ready for the table.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function perStore(): Collection
    {
        $paidOut = Wallet::query()
            ->withSum(['withdrawals as paid_total' => fn ($query) => $query->where('status', Withdrawal::STATUS_PAID)], 'amount')
            ->pluck('paid_total', 'store_id');

        return Store::with(['user', 'wallet'])
            ->withCount(['orders as completed_count' => fn ($query) => $query->where('status', Order::STATUS_COMPLETED)])
            ->withSum(['orders as completed_total' => fn ($query) => $query->where('status', Order::STATUS_COMPLETED)], 'total')
            ->withSum(['orders as commission_total' => fn ($query) => $query->where('status', Order::STATUS_COMPLETED)], 'commission_amount')
            ->withSum(['orders as held_total' => fn ($query) => $query->where('status', Order::STATUS_PAID)], 'total')
            ->orderByDesc('completed_total')
            ->orderBy('id')
            ->get()
            ->map(fn (Store $store): array => [
                'name' => $store->name,
                'owner' => $store->user?->name,
                'completed_count' => (int) $store->completed_count,
                'completed_total' => (int) $store->completed_total,
                'commission_total' => (int) $store->commission_total,
                'balance' => (int) $store->wallet?->balance,
                'held_total' => (int) $store->held_total,
                'paid_out_total' => (int) ($paidOut[$store->id] ?? 0),
            ]);
    }

    /**
     * How much money arrived through each account buyers transfer into.
     *
     * @return Collection<int, PaymentMethod>
     */
    private function perMethod(): Collection
    {
        return PaymentMethod::query()
            ->withCount([
                'payments as verified_count' => fn ($query) => $query->where('status', Payment::STATUS_VERIFIED),
                'payments as pending_count' => fn ($query) => $query->where('status', Payment::STATUS_PENDING),
            ])
            ->withSum(
                ['payments as verified_total' => fn ($query) => $query->where('status', Payment::STATUS_VERIFIED)],
                'amount',
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** Ledger credits that came from releasing order money, not from refunds. */
    private function orderReleaseCredits(): Builder
    {
        return WalletTransaction::query()
            ->where('type', WalletTransaction::TYPE_CREDIT)
            ->where('reference_type', (new Order)->getMorphClass());
    }
}
