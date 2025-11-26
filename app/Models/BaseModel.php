<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function getCreatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getUpdatedAtAttribute($value)
    {
        $timezone = $this->getTimezone();

        return \Carbon\Carbon::parse($value)->setTimezone($timezone);
    }

    public function getTimezone()
    {
        if (Tenant::checkCurrent()) {
            $systemSettings = tenant_settings_by_group('system');

            return $systemSettings['timezone'] ?? config('app.timezone');
        } else {
            $systemSettings = get_batch_settings(['system.timezone']);

            return $systemSettings['system.timezone'] ?? config('app.timezone');
        }
    }
}
