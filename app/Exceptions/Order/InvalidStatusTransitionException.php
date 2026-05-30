<?php

namespace App\Exceptions\Order;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(
        public string $from,
        public string $to,
    ) {
        parent::__construct(
            "Cannot transition order from '{$from}' to '{$to}'."
        );
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'from_status' => $this->from,
                'to_status' => $this->to,
            ],
        ], 422);
    }
}
