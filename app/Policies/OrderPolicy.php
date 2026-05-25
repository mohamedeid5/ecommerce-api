<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view the order
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Determine if the user can cancel the order
     */
    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
