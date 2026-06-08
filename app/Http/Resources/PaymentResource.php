<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payment_reference' => $this->payment_reference,
            'order_id' => $this->order_id,
            'provider' => $this->provider,
            'provider_payment_id' => $this->provider_payment_id,
            'amount' => (float) $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'failure_reason' => $this->failure_reason,
            'initiated_at' => $this->initiated_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
