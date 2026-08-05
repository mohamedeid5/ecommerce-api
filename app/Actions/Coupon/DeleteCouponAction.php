<?php

namespace App\Actions\Coupon;

use App\Models\Coupon;

class DeleteCouponAction
{
    public function execute(Coupon $coupon): bool
    {
        return $coupon->delete();
    }
}
