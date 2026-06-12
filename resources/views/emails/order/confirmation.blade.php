<x-mail::message>
# Thank you for your order, {{ $customerName }}!

Your order **{{ $orderNumber }}** has been received and is being processed.

## Order Details

<x-mail::table>
| Product | Quantity | Price |
|:--------|:--------:|------:|
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | {{ number_format($item->subtotal, 2) }} EGP |
@endforeach
</x-mail::table>

## Summary

- **Subtotal:** {{ number_format($order->subtotal, 2) }} EGP
- **Shipping:** {{ number_format($order->shipping_fee, 2) }} EGP
- **Tax (14%):** {{ number_format($order->tax_amount, 2) }} EGP
- **Total:** **{{ number_format($order->total, 2) }} EGP**

## Shipping Address

{{ $order->shipping_full_name }}
{{ $order->shipping_street }}, {{ $order->shipping_city }}
{{ $order->shipping_governorate }}
Phone: {{ $order->shipping_phone }}

<x-mail::button :url="config('app.url')">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
