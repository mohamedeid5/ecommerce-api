<?php

namespace App\Exceptions\Order;

use Exception;

class EmptyCartException extends Exception
{
    public function __construct()
    {
        parent::__construct('Your cart is empty. Add items before placing an order.');
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
