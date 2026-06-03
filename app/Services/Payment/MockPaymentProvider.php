<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Models\Order;
use Illuminate\Support\Str;

class MockPaymentProvider implements PaymentProviderInterface
{
    /**
     * Initiate a mock payment.
     * In production, this would call Paymob/Fawry/Stripe API.
     */
    public function initiate(Order $order): array
    {
        $providerId = 'mock_pay_' . Str::random(16);

        return [
            'provider_payment_id' => $providerId,
            'payment_url' => url("/api/v1/payments/mock/{$providerId}/process"),
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ];
    }

    /**
     * Verify webhook signature using HMAC.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.mock_payment.webhook_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse mock webhook payload into normalized format.
     */
    public function parseWebhook(array $payload): array
    {
        return [
            'event_id' => $payload['event_id'] ?? null,
            'provider_payment_id' => $payload['payment_id'] ?? null,
            'status' => $payload['status'] ?? null,
            'failure_reason' => $payload['failure_reason'] ?? null,
            'amount' => $payload['amount'] ?? null,
        ];
    }
}
