<?php

namespace App\Exceptions\Order;

use Exception;

class OrderNotCancellableException extends Exception
{
    public function __construct(public string $currentStatus)
    {
        parent::__construct(
            "Order cannot be cancelled in '{$currentStatus}' state."
        );
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'order' => ['Cannot cancel order in current state'],
                'current_status' => $this->currentStatus,
            ],
        ], 422);
    }
}
