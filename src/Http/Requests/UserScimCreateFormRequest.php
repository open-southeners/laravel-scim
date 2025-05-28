<?php

namespace OpenSoutheners\LaravelScim\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserScimCreateFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'userName' => ['email:rfc,strict', 'required'],
            'name' => ['string', 'nullable'],
            'emails' => ['array'],
            'emails.*.value' => ['required'],
            'emails.*.primary' => ['boolean'],
            'roles' => ['array'],
            'roles.*.value' => ['required'],
            'roles.*.primary' => ['boolean'],
            'timezone' => ['string', 'nullable'],
            'preferredLocale' => ['string', 'nullable'],
        ];
    }
}
