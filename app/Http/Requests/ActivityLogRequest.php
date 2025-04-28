<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
        ];
    }
}
