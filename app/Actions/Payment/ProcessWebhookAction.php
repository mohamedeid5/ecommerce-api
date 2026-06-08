<?php

namespace App\Actions\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Enums\OrderStatus;
use App\Exceptions\Payment\WebhookException;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWebhookAction
{
    public function __construct(
        private PaymentProviderInterface $provider,
    ) {}

    public function execute(string $rawPayload, string $signature): void
    {
         // 1. Verify signature
        if (!$this->provider->verifyWebhookSignature($rawPayload, $signature)) {
            throw new WebhookException('Invalid signature', 401);
        }

        $payload = json_decode($rawPayload, true);

        if(!$payload) {
            throw new WebhookException('Invalid JSON payload');
        }

        $data = $this->provider->parseWebhook($payload);

        // 3. Validate required fields
        if (empty($data['event_id']) || empty($data['provider_payment_id'])) {
            throw new WebhookException('Missing required fields');
        }

        // 4. Idempotency check
        $existing = Payment::where('webhook_event_id', $data['event_id'])->first();
        if ($existing) {
            Log::info('Webhook already processed', [
                'event_id' => $data['event_id'],
                'payment_id' => $existing->id,
            ]);
            return;  // Already processed, safe to ignore
        }

        // 5. Find the payment
        $payment = Payment::where('provider_payment_id', $data['provider_payment_id'])
            ->first();

        if (!$payment) {
            throw new WebhookException('Payment not found');
        }

        DB::transaction((function() use ($payment, $data) {
            $this->updatePayment($payment, $data);

            if ($data['status'] === 'succeeded') {
                $this->markOrderAsPaid($payment);
            } elseif ($data['status'] === 'failed') {
                $this->markOrderAsFailed($payment);
            }
        }));

    }

    public function updatePayment(Payment $payment, array $data): void
    {
        $payment->update([
            'webhook_event_id' => $data['event_id'],
            'status' => $data['status'],
            'failure_reason' => $data['failure_reason'] ?? null,
            'completed_at' => now(),
        ]);
    }

    private function markOrderAsPaid(Payment $payment): void
    {
        $order = $payment->order;

        // Validate state transition
        if (!$order->status->canTransitionTo(OrderStatus::PAID)) {
            Log::warning('Cannot transition order to paid', [
                'order_id' => $order->id,
                'current_status' => $order->status->value,
            ]);
            return;
        }

        $previousStatus = $order->status;

        $order->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);

        $order->statusHistory()->create([
            'from_status' => $previousStatus,
            'to_status' => OrderStatus::PAID,
            'changed_by' => null,  // ← system, not a user
            'reason' => "Payment confirmed via webhook (event: {$payment->webhook_event_id})",
        ]);
    }

    private function markOrderAsFailed(Payment $payment): void
    {
        $order = $payment->order;

        if (!$order->status->canTransitionTo(OrderStatus::FAILED)) {
            return;
        }

        $previousStatus = $order->status;

        $order->update([
            'status' => OrderStatus::FAILED,
        ]);

        $order->statusHistory()->create([
            'from_status' => $previousStatus,
            'to_status' => OrderStatus::FAILED,
            'changed_by' => null,
            'reason' => "Payment failed: {$payment->failure_reason}",
        ]);
    }
}
