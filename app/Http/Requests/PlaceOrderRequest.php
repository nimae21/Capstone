<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id'     => ['required', 'exists:user_addresses,address_id'],
            'payment_method' => ['required', 'in:credit_card,debit_card,gcash,paypal,cash_on_delivery'],
        ];
    }
}