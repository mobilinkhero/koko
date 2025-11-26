<?php

namespace App\Livewire\Admin\Tables;

class Tables
{
    public static function register()
    {
        return [
            'coupon-table' => CouponTable::class,
        ];
    }
}
