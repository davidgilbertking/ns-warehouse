<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemVideoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'videos' => ['required', 'array'],
            'videos.*' => ['file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime', 'max:51200'], // 50MB
        ];
    }
}
