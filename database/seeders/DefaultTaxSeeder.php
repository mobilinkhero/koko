<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class DefaultTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if we already have any taxes
        if (Tax::count() > 0) {
            return;
        }

        // Create default taxes with various rates
        $taxes = [
            [
                'name' => 'CGST',
                'rate' => 9.00,
                'description' => 'Standard CGST rate (9%)',
            ],
            [
                'name' => 'SGST',
                'rate' => 9.00,
                'description' => 'Standard SGST rate (9%)',
            ],

        ];

        foreach ($taxes as $tax) {
            Tax::create($tax);
        }
    }
}
