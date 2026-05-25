<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'can_cancel' => $this->status->isCancellable(),

            'pricing' => [
                'subtotal' => (float) $this->subtotal,
                'shipping_fee' => (float) $this->shipping_fee,
                'tax_amount' => (float) $this->tax_amount,
                'tax_rate' => (float) $this->tax_rate,
                'total' => (float) $this->total,
            ],

            'shipping_address' => [
                'full_name' => $this->shipping_full_name,
                'phone' => $this->shipping_phone,
                'street' => $this->shipping_street,
                'building' => $this->shipping_building,
                'apartment' => $this->shipping_apartment,
                'city' => $this->shipping_city,
                'governorate' => $this->shipping_governorate,
                'postal_code' => $this->shipping_postal_code,
                'notes' => $this->shipping_notes,
            ],

            'customer_notes' => $this->customer_notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->whenLoaded('items', fn() => $this->items->sum('quantity')),

            'status_history' => OrderStatusHistoryResource::collection(
                $this->whenLoaded('statusHistory')
            ),

            'timestamps' => [
                'placed_at' => $this->placed_at?->toIso8601String(),
                'paid_at' => $this->paid_at?->toIso8601String(),
                'shipped_at' => $this->shipped_at?->toIso8601String(),
                'delivered_at' => $this->delivered_at?->toIso8601String(),
                'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            ],

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
