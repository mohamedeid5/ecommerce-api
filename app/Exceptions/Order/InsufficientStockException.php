<?php

namespace App\Exceptions\Order;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public string $productName,
        public int $available,
        public int $requested,
    ) {
        parent::__construct(
            "Insufficient stock for '{$productName}'. Available: {$available}, Requested: {$requested}"
        );
    }

    public function render()
    {
        return response()->json([
            'message' => 'Insufficient stock',
            'errors' => [
                'product' => [$this->productName],
                'available' => $this->available,
                'requested' => $this->requested,
            ],
        ], 422);
    }
}
