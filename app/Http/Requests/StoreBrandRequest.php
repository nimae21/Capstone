<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'brand_name' => ucwords(strtolower(trim((string) $this->brand_name))),
        ]);
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'max:255'],
        ];
    }
}