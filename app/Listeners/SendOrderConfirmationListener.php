<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Mail\OrderConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;
    public int $tries = 3;
    public array $backoff = [60, 180, 600];

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order->loadMissing(['items', 'user']);

        Mail::to($order->user->email)->send(new OrderConfirmation($order));

        Log::info('Order confirmation email sent', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'recipient' => $order->user->email,
        ]);
    }

    public function failed(OrderPlaced $event, \Throwable $exception): void
    {
        Log::error('Failed to send order confirmation email', [
            'order_id' => $event->order->id,
            'error' => $exception->getMessage(),
            'order_number' => $event->order->order_number,
            'user_id' => $event->order->user_id,
        ]);
    }
}
