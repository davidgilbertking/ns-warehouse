<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email,' . $this->route('user')->id],
            'role' => ['required', 'in:admin,user,viewer,guest'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }
}
