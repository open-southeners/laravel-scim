<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenSoutheners\LaravelScim\ScimPatchOperation;

class UserScimUpdateFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'schemas' => ['array'],
            'Operations' => ['array', 'required', 'min:1'],
            'Operations.*.op' => ['required', Rule::enum(ScimPatchOperation::class)],
            'Operations.*.path' => ['string'],
            'Operations.*.value' => ['nullable'],
            'userName' => ['string', 'nullable'],
            'displayName' => ['string', 'nullable'],
            'roles' => ['array', 'nullable'],
            'roles.*.value' => ['nullable'],
            'roles.*.primary' => ['boolean', 'nullable'],
            'password' => [
                'string',
                'nullable',
            ],
            'timezone' => ['string', 'nullable'],
            'preferredLocale' => ['string', 'nullable'],
        ];
    }
}
