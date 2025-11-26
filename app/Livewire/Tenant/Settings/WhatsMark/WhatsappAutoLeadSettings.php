<?php

namespace App\Livewire\Tenant\Settings\WhatsMark;

use App\Models\Tenant\Source;
use App\Models\Tenant\Status;
use App\Models\User;
use Livewire\Component;

class WhatsappAutoLeadSettings extends Component
{
    public ?bool $auto_lead_enabled = false;

    public $lead_status = null;

    public $lead_source = null;

    public $lead_assigned_to = null;

    protected function rules()
    {
        return [
            'auto_lead_enabled' => 'nullable|boolean',
            'lead_status' => 'nullable|numeric|exists:statuses,id|required_if:auto_lead_enabled,true',
            'lead_source' => 'nullable|numeric|exists:sources,id|required_if:auto_lead_enabled,true',
            'lead_assigned_to' => 'nullable|numeric|exists:users,id',
        ];
    }

    public function mount()
    {
        if (! checkPermission('tenant.whatsmark_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $settings = tenant_settings_by_group('whats-mark');

        $this->auto_lead_enabled = $settings['auto_lead_enabled'] ?? false;
        $this->lead_status = $settings['lead_status'] ?? null;
        $this->lead_source = $settings['lead_source'] ?? null;
        $this->lead_assigned_to = $settings['lead_assigned_to'] ?? null;
    }

    public function save()
    {
        if (checkPermission('tenant.whatsmark_settings.edit')) {
            $this->validate();

            $settings = tenant_settings_by_group('whats-mark');

            $originalSettings = [
                'auto_lead_enabled' => $settings['auto_lead_enabled'] ?? false,
                'lead_status' => $settings['lead_status'] ?? null,
                'lead_source' => $settings['lead_source'] ?? null,
                'lead_assigned_to' => $settings['lead_assigned_to'] ?? null,
            ];

            $modifiedSettings = [];

            foreach ($originalSettings as $key => $originalValue) {
                $newValue = $this->{$key};

                // If the value is new or changed, mark it for saving
                if (! array_key_exists($key, $settings) || $originalValue !== $newValue) {
                    $modifiedSettings[$key] = ! empty($newValue) ? $newValue : null;
                }
            }

            if (! empty($modifiedSettings)) {
                foreach ($modifiedSettings as $key => $value) {
                    save_tenant_setting('whats-mark', $key, $value);
                }

                $this->notify([
                    'type' => 'success',
                    'message' => t('setting_save_successfully'),
                ]);
            }
        }
    }

    public function render()
    {
        return view('livewire.tenant.settings.whats-mark.whatsapp-auto-lead-settings',
            [
                'statuses' => Status::where('tenant_id', tenant_id())->get(),
                'sources' => Source::where('tenant_id', tenant_id())->get(),
                'users' => User::where('tenant_id', tenant_id())->get(),
            ]);
    }
}
