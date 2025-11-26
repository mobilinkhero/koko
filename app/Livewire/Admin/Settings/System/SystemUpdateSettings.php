<?php

/**
 * FINAL by STATIXCODE - FULLY UNLOCKED
 * SystemUpdateSettings Component
 * 
 * This is the FINAL unlocked version with permanent bypass functionality.
 * All validation, licensing, and update restrictions have been permanently disabled.
 * Username and Purchase Key are hardcoded for seamless update operations.
 */

namespace App\Livewire\Admin\Settings\System;

use Corbital\Installer\Classes\UpdateChecker;
use Livewire\Component;

class SystemUpdateSettings extends Component
{
    public $currentVersion;

    public $latestVersion;

    public $purchase_key = 'STATIXCODE-FINAL-2025-UNLOCKED';

    public $username = 'STATIXCODE-ADMIN';

    public $update_id;

    public $has_sql_update;

    public $releases = [];

    public $update = [];

    public $token;

    public $versionLog = [];

    public $wmSettings = [];

    protected $rules = [
        // PERMANENT BYPASS - No validation needed for FINAL mode
        // 'purchase_key' => 'required|string',
        // 'username' => 'required|string',
    ];

    public function mount()
    {
        if (! checkPermission('admin.system_settings.view')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }
        $this->wmSettings = get_batch_settings([
            'whats-mark.wm_verification_token',
            'whats-mark.wm_version',
        ]);
        $settings = explode('|', $this->wmSettings['whats-mark.wm_verification_token']);
        $this->token = $settings[0];
        $this->currentVersion = $this->wmSettings['whats-mark.wm_version'];
        $this->loadReleases();
    }

    public function loadReleases()
    {
        try {
            $update_checker = new UpdateChecker;
            $this->update = $update_checker->checkUpdate($this->token, 'update')['data'];
            $this->latestVersion = $this->update['latest_version'];
            $this->update_id = $this->update['update_id'];
            $this->has_sql_update = $this->update['has_sql_update'];
            $this->versionLog = $update_checker->getVersionLog();
        } catch (\Exception $e) {
            // Handle error case
            $this->notify(['type' => 'danger', 'message' => t('failed_to_load_release_data')]);
        }
    }

    public function save()
    {
        // PERMANENT BYPASS - Use FINAL values, no validation
        $update_checker = new UpdateChecker;
        $response = $update_checker->downloadUpdate($this->update_id, $this->has_sql_update, $this->latestVersion, $this->token, $this->purchase_key, $this->username, 'update');
        $this->notify(['type' => ($response['success'] == true) ? 'success' : 'danger', 'message' => $response['message']]);

        if ($response['success'] == true) {
            set_setting('whats-mark.wm_version', $this->latestVersion);
            clear_view();

            return redirect(route('admin.system-update.settings.view'));
        }
    }

    public function clearCache()
    {
        clear_cache();
        clear_route();
        clear_config();

        $this->notify(['type' => 'success', 'message' => t('cache_cleared_successfully')], true);

        return redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.admin.settings.system.system-update-settings');
    }
}
