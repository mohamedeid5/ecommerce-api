<?php

namespace App\Contracts\Payment;

use App\Models\Order;

interface PaymentProviderInterface
{
    /**
     * Initiate a payment for an order.
     * Returns array with provider's response data.
     */
    public function initiate(Order $order): array;

    /**
     * Verify a webhook signature to ensure it's authentic.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Parse webhook payload and return normalized data.
     */
    public function parseWebhook(array $payload): array;
}
