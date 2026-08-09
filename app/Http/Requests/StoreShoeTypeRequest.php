<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShoeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'shoe_type_name' => ucwords(strtolower(trim((string) $this->shoe_type_name))),
            'description'    => trim((string) $this->description),
        ]);
    }

    public function rules(): array
    {
        return [
            'shoe_type_name' => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
        ];
    }
}