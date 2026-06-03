<?php

namespace App\Exceptions\Payment;

use Exception;

class PaymentNotInitiableException extends Exception
{
    public function __construct(public string $reason)
    {
        parent::__construct("Payment cannot be initiated: {$reason}");
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['reason' => $this->reason],
        ], 422);
    }
}
