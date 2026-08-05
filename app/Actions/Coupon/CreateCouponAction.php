<?php

namespace App\Actions\Coupon;

use App\DTOs\Coupon\CouponDTO;
use App\Models\Coupon;

class CreateCouponAction
{
    public function execute(CouponDTO $data): Coupon
    {
        return Coupon::create([
            'code' => strtoupper(trim($data->code)),
            'type' => $data->type,
            'value' => $data->value,
            'min_order_amount' => $data->min_order_amount,
            'usage_limit' => $data->usage_limit,
            'start_date' => $data->start_date,
            'expires_at' => $data->expires_at,
        ]);
    }
}
