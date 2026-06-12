<?php

namespace App\Listeners;

use App\Events\PaymentFailed;
use App\Mail\PaymentFailedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentFailedEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;
    public int $tries = 3;
    public array $backoff = [60, 180, 600];

    public function handle(PaymentFailed $event): void
    {
        $payment = $event->payment->load(['order.user']);

        Mail::to($payment->order->user->email)->send(new PaymentFailedMail($payment));

        Log::info('Payment failed email sent', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'recipient' => $payment->order->user->email,
        ]);
    }
}
