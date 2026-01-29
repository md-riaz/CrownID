<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'enabled' => 'nullable|boolean',
            'emailVerified' => 'nullable|boolean',
            'attributes' => 'nullable|array',
            'credentials' => 'nullable|array',
            'credentials.*.type' => 'required_with:credentials|string',
            'credentials.*.value' => 'required_with:credentials|string|min:8',
            'credentials.*.temporary' => 'nullable|boolean',
        ];
    }
    
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $realm = \App\Models\Realm::where('name', $this->route('realm'))->first();
            
            if (!$realm) {
                return;
            }
            
            if ($this->has('username')) {
                $exists = \App\Models\User::where('realm_id', $realm->id)
                    ->where('username', $this->input('username'))
                    ->exists();
                    
                if ($exists) {
                    $validator->errors()->add('username', 'The username has already been taken in this realm.');
                }
            }
            
            if ($this->has('email')) {
                $exists = \App\Models\User::where('realm_id', $realm->id)
                    ->where('email', $this->input('email'))
                    ->exists();
                    
                if ($exists) {
                    $validator->errors()->add('email', 'The email has already been taken in this realm.');
                }
            }
        });
    }
}
