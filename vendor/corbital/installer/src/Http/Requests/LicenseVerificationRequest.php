<?php

namespace Corbital\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseVerificationRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:255'],
            'purchase_code' => [
                'required',
                'string',
                'regex:/^([a-f0-9]{8})-(([a-f0-9]{4})-){3}([a-f0-9]{12})$/i',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'purchase_code.regex' => 'Invalid purchase code format. Expected format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'username' => 'Envato Username',
            'purchase_code' => 'Purchase Code',
        ];
    }
}
