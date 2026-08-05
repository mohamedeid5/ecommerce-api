<?php

namespace App\DTOs\Coupon;

class CouponDTO
{
    public function __construct(
        public string $code,
        public string $type,
        public float $value,
        public ?float $min_order_amount,
        public ?int $usage_limit,
        public ?string $start_date,
        public ?string $expires_at,
        public bool $is_active,
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            code: $request->validated('code'),
            type: $request->validated('type'),
            value: $request->validated('value'),
            min_order_amount: $request->validated('min_order_amount'),
            usage_limit: $request->validated('usage_limit'),
            start_date: $request->validated('start_date'),
            is_active: $request->validated('is_active', true),
            expires_at: $request->validated('expires_at'),
        );
    }
}
