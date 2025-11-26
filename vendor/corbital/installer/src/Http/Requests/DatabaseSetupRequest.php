<?php

namespace Corbital\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseSetupRequest extends FormRequest
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
            'app_url' => ['required', 'url'],
            'app_name' => ['required', 'string', 'max:255'],
            'country' => ['sometimes', 'integer'],
            'database_hostname' => ['required', 'string', 'max:255'],
            'database_port' => ['required', 'numeric'],
            'database_name' => ['required', 'string', 'max:255'],
            'database_username' => ['required', 'string', 'max:255'],
            'database_password' => ['required', 'string'],
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
            'app_url' => 'Application URL',
            'app_name' => 'Application Name',
            'country' => 'Country',
            'database_hostname' => 'Database Hostname',
            'database_port' => 'Database Port',
            'database_name' => 'Database Name',
            'database_username' => 'Database Username',
            'database_password' => 'Database Password',
        ];
    }
}
