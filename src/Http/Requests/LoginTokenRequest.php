<?php

namespace Kiwilan\Steward\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
            'device_name' => 'required|string',
        ];
    }
}
