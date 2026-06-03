<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'street' => ['sometimes', 'required', 'string', 'max:255'],
            'building' => ['sometimes', 'nullable', 'string', 'max:255'],
            'apartment' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'governorate' => ['sometimes', 'required', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:10'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
