<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Override;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentProvider implements PaymentProviderInterface
{
    private StripeClient $stripe;
    private string $webhookSecret;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->webhookSecret = config('services.stripe.webhook_secret');
    }

    #[Override]
    public function initiate(Order $order): array
    {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => config('app.url') . '/payment-success?order=' . $order->order_number,
            'cancel_url' => config('app.url') . '/payment-cancel?order=' . $order->order_number,
            'customer_email' => $order->user->email,
            'client_reference_id' => $order->id,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            'line_items' => $this->buildLineItems($order),
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        Log::info('Stripe cehckout session created', [
            'order_id' => $order->id,
            'session_id' => $session->id,
        ]);

        return [
            'provider_payment_id' => $session->id,
            'payment_url' => $session->url,
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ];
    }

    /**
     * Verify webhook signature using Stripe's helper
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch(SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Stripe webhook verification error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Parse Stripe webhook payload into normalized format
     */
    public function parseWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        $session = $payload['data']['object'] ?? [];

        return [
            'event_id' => $payload['id'] ?? null,
            'provider_payment_id' => $session['id'] ?? null,
            'status' => $this->mapStripeStatus($eventType, $session),
            'failure_reason' => $this->extractFailureReason($session),
            'amount' => isset($session['amount_total'])
                ? $session['amount_total'] / 100  // Stripe بيرجع amounts بـ cents
                : null,
        ];
    }

     /**
     * Build Stripe line items from order items
     */
    private function buildLineItems(Order $order): array
    {
        $lineItems = [];

        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'EGP',
                    'product_data' => [
                        'name' => $item->product_name,
                        'metadata' => [
                            'sku' => $item->product_sku,
                        ],
                    ],
                    'unit_amount' => (int) $item->unit_amount * 100
                ],
                'quantity' => $item->quantity,
            ];

            // add. shopping as a line item
            if($order->shipping_fee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'egp',
                        'product_data' => [
                            'name' => 'Shipping',
                        ],
                        'unit_amount' => (int) ($order->shipping_fee * 100),
                    ],
                    'quantity' => 1,
                ];
            }


            // add tax as a line item
             if ($order->tax_amount > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'egp',
                        'product_data' => [
                            'name' => 'VAT (14%)',
                        ],
                        'unit_amount' => (int) ($order->tax_amount * 100),
                    ],
                    'quantity' => 1,
                ];
            }
        }

        return $lineItems;
    }


    /**
     * Map Stripe event types to our internal payment statuses
     */
    private function mapStripeStatus(string $eventType, array $session): string
    {
        return match($eventType) {
            'checkout.session.completed' => 'succeeded',
            'checkout.session.expired' => 'failed',
            'checkout.session.async_payment_failed' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Extract failure reason from session data
     */
    private function extractFailureReason(array $session): ?string
    {
        if (isset($session['last_payment_error']['message'])) {
            return $session['last_payment_error']['message'];
        }

        return null;
    }
}
