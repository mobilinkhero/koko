<?php

namespace Corbital\LaravelEmails\Database\Seeders;

use Corbital\LaravelEmails\Models\EmailLayout;
use Illuminate\Database\Seeder;

class EmailLayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update the Default Layout
        EmailLayout::updateOrCreate(
            ['slug' => 'default'], // Match by slug only
            [
                'name' => 'Default Layout',
                'header' => $this->getDefaultHeader(),
                'footer' => $this->getDefaultFooter(),
                'master_template' => $this->getDefaultMasterTemplate(),
                'variables' => ['company_name', 'company_logo', 'company_address', 'unsubscribe_url'],
                'is_default' => true,
                'is_system' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Get the default header HTML.
     */
    protected function getDefaultHeader(): string
    {
        return '<tr><td style="background-color: #6366f1; padding: 30px; text-align: center; border-top-left-radius: 12px; border-top-right-radius: 12px;"> <img src="{dark_logo}" alt="{company_name}" style="height: 40px; width: auto; margin-bottom: 10px;"></td></tr>';
    }

    /**
     * Get the default footer HTML.
     */
    protected function getDefaultFooter(): string
    {
        return '<tr> <td style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;"> <p style="color: #6b7280; font-size: 14px; margin: 0 0 10px 0;"> © {current_year} {company_name}.All rights reserved. </p><p style="color: #6b7280; font-size: 14px; margin: 0;">Made with ♥ by {company_name}</p></td></tr>';
    }

    /**
     * Get the default master template HTML.
     */
    protected function getDefaultMasterTemplate(): string
    {
        return '<!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{{laungauge}}</title>
            <style>
                body {
                margin: 0; padding: 0; background-color: #f5f6fa;
                }

            </style>
        </head>
        <body>
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f6fa; padding: 30px;">
                <tr>
                    <td align="center">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                            style="max-width: 800px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            {HEADER}
                            <tr>
                                <td style="padding:40px 50px;">
                                        {CONTENT}
                                </td>
                                {FOOTER}
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }
}
