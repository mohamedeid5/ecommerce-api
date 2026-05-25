<?php

namespace App\Actions\Order;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUserOrdersAction
{
    public function execute(
        User $user,
        int $perPage = 10,
        ?string $statusFilter = null,
    ): LengthAwarePaginator {
        $query = $user->orders()
            ->with(['items'])
            ->latest('placed_at');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return $query->paginate($perPage);
    }
}
