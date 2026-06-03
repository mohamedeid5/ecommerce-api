<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
{
    public function update(?User $user, CartItem $item, Cart $cart): bool
    {
        return $item->cart_id === $cart->id;
    }

    public function delete(?User $user, CartItem $item, Cart $cart): bool
    {
        return $item->cart_id === $cart->id;
    }
}
