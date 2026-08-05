<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CouponService
{
    public function getAll(int $perPage = 10)
    {
        return QueryBuilder::for(Coupon::class)
            ->allowedFilters(
                AllowedFilter::exact('type'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::partial('code')
            )
            ->allowedSorts(
                'id',
                'code',
                'value',
                'created_at',
                'expires_at'
            )
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends(request()->query());

    }
}

