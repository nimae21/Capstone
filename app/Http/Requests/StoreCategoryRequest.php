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
            'brand_name'        => $this->normalizeTitleCase($this->brand_name),
            'brand_description' => trim((string) $this->brand_description),
        ]);
    }

    public function rules(): array
    {
        return [
            'brand_name'        => ['required', 'string', 'max:255'],
            'brand_description' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function normalizeTitleCase(?string $value): string
    {
        return ucwords(strtolower(trim((string) $value)));
    }
}