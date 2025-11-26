<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant\WmActivityLog;
use Livewire\Component;

class ActivityLogDetails extends Component
{
    public $data;

    public function mount($logId)
    {
        $this->data = WmActivityLog::find($logId);
    }

    public function render()
    {
        return view('livewire.tenant.activity-log-details');
    }
}
