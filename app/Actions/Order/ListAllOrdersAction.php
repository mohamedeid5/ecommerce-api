<?php

namespace App\Actions\Order;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAllOrdersAction
{
    public function execute(
        ?string $statusFilter = null,
        ?string $orderNumber = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = Order::query()
            ->with(['user:id,name,email', 'items'])
            ->latest('placed_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($orderNumber) {
            $query->where('order_number', 'like', "%{$orderNumber}%");
        }

        return $query->paginate($perPage);
    }
}
