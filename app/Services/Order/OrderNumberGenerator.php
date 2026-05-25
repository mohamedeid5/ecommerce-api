<?php

namespace App\Services\Order;

class OrderNumberGenerator
{
    public function generate(int $orderId): string
    {
        $year = now()->year;
        $padded = str_pad($orderId, 6, '0', STR_PAD_LEFT);

        return "ORD-{$year}-{$padded}";
    }
}
