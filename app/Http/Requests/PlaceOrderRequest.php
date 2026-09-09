<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', Rule::exists('user_addresses', 'address_id')
                ->where('user_id', $this->user()->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Please add or select a delivery address before completing your order.',
            'address_id.integer' => 'Please select a valid delivery address.',
            'address_id.exists' => 'Please select one of your saved delivery addresses or add a new address.',
        ];
    }
}