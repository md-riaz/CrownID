<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRealmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'realm' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('realms', 'name')->ignore($this->route('realm'), 'name'),
            ],
            'displayName' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
            'accessTokenLifespan' => 'nullable|integer|min:1',
            'refreshTokenLifespan' => 'nullable|integer|min:1',
            'ssoSessionIdleTimeout' => 'nullable|integer|min:1',
            'ssoSessionMaxLifespan' => 'nullable|integer|min:1',
        ];
    }
}
