<x-mail::message>
# Payment confirmed, {{ $customerName }}!

Your payment for order **{{ $order->order_number }}** was completed successfully.

## Payment Details

- **Reference:** {{ $payment->payment_reference }}
- **Amount:** {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
- **Status:** Succeeded

Your order is now paid and will move to processing.

<x-mail::button :url="config('app.url')">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
