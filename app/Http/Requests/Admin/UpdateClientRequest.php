<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clientId' => 'sometimes|string|max:255',
            'name' => 'nullable|string|max:255',
            'secret' => 'nullable|string',
            'enabled' => 'nullable|boolean',
            'publicClient' => 'nullable|boolean',
            'redirectUris' => 'nullable|array',
            'redirectUris.*' => 'url',
            'protocol' => 'nullable|string|in:openid-connect',
        ];
    }
}
