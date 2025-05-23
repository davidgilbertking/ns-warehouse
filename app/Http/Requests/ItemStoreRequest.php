<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'size' => ['nullable', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'storage_place' => ['nullable', 'string', 'max:255'],

            // Новые текстовые поля
            'mechanics' => ['nullable', 'string'],
            'scalability' => ['nullable', 'string'],
            'client_price' => ['nullable', 'numeric'],

            'branding_options' => ['nullable', 'string'],
            'adaptation_options' => ['nullable', 'string'],
            'op_price' => ['nullable', 'string'],

            'construction_description' => ['nullable', 'string'],
            'contractor' => ['nullable', 'string'],
            'production_cost' => ['nullable', 'string'],
            'change_history' => ['nullable', 'string'],
            'consumables' => ['nullable', 'string'],
            'implementation_comments' => ['nullable', 'string'],
            'mounting' => ['nullable', 'string'],
            'storage_features' => ['nullable', 'string'],
            'design_links' => ['nullable', 'string'],
            'event_history' => ['nullable', 'string'],

            // Массивы файлов или ссылок
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10240'],

            'op_media' => ['nullable', 'array'],
            'op_media.*' => ['nullable', 'string'], // ссылки на медиа

            'real_media' => ['nullable', 'array'],
            'real_media.*' => ['nullable', 'string'],

            'event_media' => ['nullable', 'array'],
            'event_media.*' => ['nullable', 'string'],
        ];
    }
}
