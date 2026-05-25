<?php

namespace App\Actions\Order;

use App\Models\Cart;
use App\Services\Order\OrderConstants;

class CalculateOrderTotalsAction
{
    public function execute(Cart $cart): array
    {
        $subtotal = $cart->items->sum(
            fn($item) => $item->unit_price * $item->quantity
        );

        $shippingFee = OrderConstants::SHIPPING_FEE;

        $taxRate = OrderConstants::TAX_RATE;
        $taxAmount = round(($subtotal + $shippingFee) * $taxRate, 2);

        $total = $subtotal + $shippingFee + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'shipping_fee' => $shippingFee,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'total' => round($total, 2),
        ];
    }
}
