<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // если нужна проверка прав — можно потом добавить
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'items' => 'nullable|array',
            'items.*.id' => 'exists:items,id',
            'items.*.quantity' => 'integer|min:1',
        ];
    }
}
