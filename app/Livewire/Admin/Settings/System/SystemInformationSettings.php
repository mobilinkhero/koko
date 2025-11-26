<?php

namespace App\Livewire\Admin\Settings\System;

use App\Services\SystemManagement;
use Livewire\Component;

class SystemInformationSettings extends Component
{
    public $system;

    public $server;

    public $packages;

    protected $systemManagement;

    public function mount(SystemManagement $systemManagement)
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->systemManagement = $systemManagement;

        $info = $this->systemManagement->getInfo();
        $this->system = $info['system'];
        $this->server = $info['server'];
    }

    public function render()
    {
        return view('livewire.admin.settings.system.system-information-settings');
    }
}
