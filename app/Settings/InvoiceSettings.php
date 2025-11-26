<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InvoiceSettings extends Settings
{
    // Bank Details
    public string $prefix = '';

    public string $bank_name = '';

    public string $account_name = '';

    public string $account_number = '';

    public string $ifsc_code = '';

    public string $footer_text = '';

    public string $default_taxes = '[]';

    public static function group(): string
    {
        return 'invoice';
    }
}
