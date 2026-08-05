<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount,
            'usage_limit' => $this->usage_limit,
            'times_used' => $this->times_used,
            'start_date' => $this->start_date,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
        ];
    }
}
