<?php

namespace App\Exceptions\Order;

use Exception;

class ProductUnavailableException extends Exception
{
    public function __construct(public string $productName)
    {
        parent::__construct("Product '{$productName}' is no longer available.");
    }

    public function render()
    {
        return response()->json([
            'message' => 'Product unavailable',
            'product' => $this->productName,
        ], 422);
    }
}
