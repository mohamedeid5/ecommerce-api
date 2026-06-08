<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartResolver
{
    public function resolve()
    {
        $user = $this->authenticatedUser();

        if ($user) {
            return $this->resolveForUser($user);
        }

        return $this->resolveForGuest();
    }

    public function resolveForUser(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['expires_at' => now()->addDays(30)]
        );
    }

    public function resolveForGuest()
    {
        $token = request()->header('X-Cart-Token');

        if ($token) {
            $cart = Cart::where('guest_token', $token)->whereNull('user_id')->first();

            if ($cart && ! $cart->isExpired()) {
                return $cart;
            }
        }

        return Cart::create([
            'guest_token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);
    }

    private function authenticatedUser(): ?User
    {
        return request()->user('api')
            ?? Auth::user();
    }
}
