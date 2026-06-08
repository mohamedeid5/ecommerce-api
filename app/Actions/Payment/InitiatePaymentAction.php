<?php

namespace App\Actions\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Enums\OrderStatus;
use App\Exceptions\Payment\PaymentNotInitiableException;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentNumberGenerator;
use Illuminate\Support\Facades\DB;

class InitiatePaymentAction
{

    public function __construct(
        protected PaymentProviderInterface $paymentProvider,
        protected PaymentNumberGenerator $paymentNumberGenerator
    ) {}

    public function execute(Order $order): array
    {
        $this->validateOrderCanBePaid($order);

        return DB::transaction(function() use ($order) {
            // 1. Create payment record (placeholder reference)
            $payment = Payment::create([
                'payment_reference' => 'PENDING',
                'order_id' => $order->id,
                'provider' => 'mock',
                'amount' => $order->total,
                'currency' => 'EGP',
                'status' => 'pending',
                'initiated_at' => now(),
            ]);

            // 2. Generate real reference
            $payment->update([
                'payment_reference' => $this->paymentNumberGenerator->generate($payment->id),
            ]);

            $providerResponse = $this->paymentProvider->initiate($order);

            $payment->update([
                'provider_payment_id' => $providerResponse['provider_payment_id'],
            ]);

            return [
                'payment' => $payment->fresh(),
                'payment_url' => $providerResponse['payment_url'],
                'expires_at' => $providerResponse['expires_at']
            ];
        });
    }

    private function validateOrderCanBePaid(Order $order)
    {
        // Order must be in pending_payment state
        if ($order->status !== OrderStatus::PENDING_PAYMENT) {
             throw new PaymentNotInitiableException(
                "Order is not awaiting payment (current status: {$order->status->label()})"
            );
        }

       // Don't allow duplicate pending payments
        $hasPendingPayment = $order->payments()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingPayment) {
            throw new PaymentNotInitiableException(
                'Order already has a pending payment'
            );
        }
    }
}
