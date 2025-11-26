<?php

namespace Corbital\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSetupRequest extends FormRequest
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
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'timezone' => ['required', 'string'],
        ];

        // Add first name and last name if they're configured to be used
        if (config('installer.admin_setup.fields.firstname', true)) {
            $rules['firstname'] = ['required', 'string', 'max:255'];
        }

        if (config('installer.admin_setup.fields.lastname', true)) {
            $rules['lastname'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'firstname' => 'First Name',
            'lastname' => 'Last Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'timezone' => 'Timezone',
        ];
    }
}
