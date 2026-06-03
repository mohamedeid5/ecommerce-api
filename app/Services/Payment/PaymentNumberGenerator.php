<?php

namespace App\Services\Payment;

class PaymentNumberGenerator
{
    public function generate(int $paymentId): string
    {
        $year = now()->year;
        $padded = str_pad($paymentId, 6, '0', STR_PAD_LEFT);

        return "PAY-{$year}-{$padded}";
    }
}
