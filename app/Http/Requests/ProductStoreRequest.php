<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Можно добавить проверки, если нужно
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'items'            => 'nullable|array',
            'items.*.id'       => 'exists:items,id',
            'items.*.quantity' => 'integer|min:1',
        ];
    }
}
