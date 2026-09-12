<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\OrderPresenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Read-only order access for the Super Admin. Shipping, completing and
 * cancelling stay on the website where Super Admin order writes have never
 * been allowed, so the phone deliberately exposes no mutation endpoints.
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'shipped', 'completed', 'cancelled'])],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Order::with(['user', 'items'])->where('sale_type', 'online');
        $status = $filters['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (! empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', '%'.$term.'%')
                    ->orWhere('phone_number', 'like', '%'.$term.'%');
                if (ctype_digit($term)) {
                    $q->orWhere('order_id', (int) $term);
                }
                $q->orWhereHas('user', fn ($u) => $u
                    ->where('email', 'like', '%'.$term.'%')
                    ->orWhere('first_name', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%'));
            });
        }

        $orders = $query->orderByDesc('order_id')->paginate(20)
            ->through(fn (Order $order) => OrderPresenter::summary($order))
            ->toArray();

        // Shipped with the page: a second HTTP round trip costs ~6 queries of
        // framework overhead, far more than this one aggregate.
        return response()->json($orders + ['counts' => $this->statusCounts()]);
    }

    public function show(Order $order)
    {
        abort_unless($order->sale_type?->value === 'online', 404);

        return response()->json(OrderPresenter::detail($order));
    }

    public function counts()
    {
        return response()->json($this->statusCounts());
    }

    /** One aggregate instead of one query per status. */
    private function statusCounts(): array
    {
        $row = Order::query()->where('sale_type', 'online')->selectRaw(
            "COUNT(*) as total,
             COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
             COALESCE(SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END), 0) as paid,
             COALESCE(SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END), 0) as shipped,
             COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
             COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled"
        )->first();

        return [
            'all' => (int) $row->total,
            'pending' => (int) $row->pending,
            'paid' => (int) $row->paid,
            'shipped' => (int) $row->shipped,
            'completed' => (int) $row->completed,
            'cancelled' => (int) $row->cancelled,
        ];
    }
}