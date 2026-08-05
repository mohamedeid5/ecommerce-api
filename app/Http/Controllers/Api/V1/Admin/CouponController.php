<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Coupon\CreateCouponAction;
use App\Actions\Coupon\DeleteCouponAction;
use App\Actions\Coupon\GetCouponsAction;
use App\Actions\Coupon\UpdateCouponAction;
use App\DTOs\Coupon\CouponDTO;
use App\Http\Controllers\Api\V1\BaseApiController;
use App\Http\Requests\Coupon\StoreCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;

class CouponController extends BaseApiController
{

    public function __construct(
        private readonly CreateCouponAction $createCouponAction,
        private readonly GetCouponsAction $getCouponsAction,
        private readonly UpdateCouponAction $updateCouponAction,
        private readonly DeleteCouponAction $deleteCouponAction
        )
    {}

    public function index()
    {
        $coupons = $this->getCouponsAction->execute();
        return $this->successResponse(
            CouponResource::collection($coupons),
            'Coupons retrieved successfully'
        );
    }

    public function store(StoreCouponRequest $request)
    {
        $couponDTO = CouponDTO::fromRequest($request);
        $coupon = $this->createCouponAction->execute($couponDTO);

        return $this->createdResponse(
            new CouponResource($coupon),
            'Coupon created successfully'
        );
    }

    public function show(Coupon $coupon)
    {
        return $this->successResponse(
            new CouponResource($coupon),
            'Coupon retrieved successfully'
        );
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $couponDTO = CouponDTO::fromRequest($request);
        $coupon = $this->updateCouponAction->execute($coupon, $couponDTO);

        return $this->successResponse(
            new CouponResource($coupon),
            'Coupon updated successfully'
        );
    }

    public function destroy(Coupon $coupon)
    {
        $this->deleteCouponAction->execute($coupon);
        return $this->successResponse(null, 'Coupon deleted successfully');
    }
}
