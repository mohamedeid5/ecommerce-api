<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'times_used',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'type' => \App\Enums\CouponType::class,
        'is_active' => 'boolean',
    ];
}
