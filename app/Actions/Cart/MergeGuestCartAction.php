<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MergeGuestCartAction
{
    public function execute(string $guestToken, User $user): ?Cart
    {
        return DB::transaction(function() use ($guestToken, $user) {

            $guestCart = Cart::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->with('items')
                ->first();

            if(!$guestCart || $guestCart->items->isEmpty()) {
                $guestCart?->delete();
                return null;
            }

            $userCart = Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['expires_at' => now()->addDays(30)]
            );

            foreach ($guestCart->items as $guestItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if($existingItem) {
                    $newQuantity = $existingItem->quantity + $guestItem->quantity;
                    $availableStock = $guestItem->product->stock;

                    $existingItem->update([
                        'quantity' => min($newQuantity, $availableStock),
                    ]);
                } else {
                    $guestItem->update([
                        'cart_id' => $userCart->id
                    ]);
                }
            }

            $guestCart->delete();

            return $userCart->fresh('items');
        });
    }
}
