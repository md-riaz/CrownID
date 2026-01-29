<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'realm' => 'required|string|max:255|unique:realms,name',
            'displayName' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
            'accessTokenLifespan' => 'nullable|integer|min:1',
            'refreshTokenLifespan' => 'nullable|integer|min:1',
            'ssoSessionIdleTimeout' => 'nullable|integer|min:1',
            'ssoSessionMaxLifespan' => 'nullable|integer|min:1',
        ];
    }

    public function preparedForValidation(): void
    {
        if (!$this->has('enabled')) {
            $this->merge(['enabled' => true]);
        }
    }
}
