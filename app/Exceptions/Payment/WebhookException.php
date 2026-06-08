<?php

namespace App\Exceptions\Payment;

use Exception;

class WebhookException extends Exception
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
