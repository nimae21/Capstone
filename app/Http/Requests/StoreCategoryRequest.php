<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_name'        => $this->normalizeTitleCase($this->category_name),
            'category_description' => trim((string) $this->category_description),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_name'        => ['required', 'string', 'max:255'],
            'category_description' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function normalizeTitleCase(?string $value): string
    {
        return ucwords(strtolower(trim((string) $value)));
    }
}