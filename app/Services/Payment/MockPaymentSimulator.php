<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;

class MockPaymentSimulator
{
    /**
     * Generate a mock webhook payload for testing
     */
    public function simulateWebhook(
        Payment $payment,
        string $status = 'succeeded',
        ?string $failureReason = null,
    ): array {
        $payload = [
            'event_id' => 'evt_' . Str::random(16),
            'payment_id' => $payment->provider_payment_id,
            'status' => $status,
            'failure_reason' => $failureReason,
            'amount' => (float) $payment->amount,
            'timestamp' => now()->toIso8601String(),
        ];

        $rawPayload = json_encode($payload);
        $signature = hash_hmac(
            'sha256',
            $rawPayload,
            config('services.mock_payment.webhook_secret')
        );

        return [
            'payload' => $payload,
            'raw_payload' => $rawPayload,
            'signature' => $signature,
        ];
    }
}
