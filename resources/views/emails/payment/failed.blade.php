<x-mail::message>
# Payment failed, {{ $customerName }}

We could not complete the payment for order **{{ $order->order_number }}**.

## Payment Details

- **Reference:** {{ $payment->payment_reference }}
- **Amount:** {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
- **Status:** Failed
@if($payment->failure_reason)
- **Reason:** {{ $payment->failure_reason }}
@endif

You can try paying again from your order page.

<x-mail::button :url="config('app.url')">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
