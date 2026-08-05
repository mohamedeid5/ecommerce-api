<?php

namespace App\Http\Requests\Coupon;

use App\Enums\CouponType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('coupons', 'code')->ignore($this->coupon),
            ],
            'type' => ['sometimes', 'required', Rule::enum(CouponType::class)],
            'value' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
                Rule::when($this->input('type') === CouponType::Percentage->value, ['max:100']),
            ],
            'min_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'usage_limit' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
