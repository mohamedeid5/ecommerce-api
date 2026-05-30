<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'total' => (float) $this->total,
            'items_count' => (int) $this->items_count,
            'placed_at' => $this->placed_at?->toIso8601String(),
            'can_cancel' => $this->status->isCancellable(),
        ];
    }
}
