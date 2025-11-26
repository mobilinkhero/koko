<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'format' => 'before_amount',
                'exchange_rate' => 1.000000,
                'is_default' => true,
            ],
        ];

        // Get the current default currency from the database
        $defaultCurrency = DB::table('currencies')->where('is_default', true)->first();

        foreach ($currencies as $currency) {
            if ($defaultCurrency && $currency['code'] !== $defaultCurrency->code) {
                $currency['is_default'] = false;
            }
            DB::table('currencies')->updateOrInsert(
                ['code' => $currency['code']],
                array_merge($currency, [
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                ])
            );
        }
    }
}
