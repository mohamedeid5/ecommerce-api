<?php

namespace App\Actions\Coupon;

use App\Models\Coupon;

class GetCouponsAction
{
    public function execute()
    {
        return Coupon::all();
    }
}
