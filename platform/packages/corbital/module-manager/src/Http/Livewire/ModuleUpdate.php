<?php

namespace Corbital\ModuleManager\Http\Livewire;

use Corbital\ModuleManager\Classes\ModuleUpdateChecker;
use Livewire\Component;

class ModuleUpdate extends Component
{
    public $module;

    public $currentVersion;

    public $latestVersion;

    public $purchase_key;

    public $username;

    public $update_id;

    public $has_sql_update;

    public $releases = [];

    public $update = [];

    public $token;

    public $support = [];

    public $versionLog = [];

    protected $rules = [
        'purchase_key' => 'required|string',
        'username' => 'required|string',
    ];

    public function mount($itemId)
    {
        $this->module = get_module($itemId);
        if (empty($this->module)) {
            $this->notify(['type' => 'danger', 'message' => t('module_not_found')]);

            return redirect(route('admin.modules.index'));
        }
        $settings = explode('|', $this->module['payload']['verification_token']);
        $this->token = $settings[0];
        $this->currentVersion = $this->module['version'];
        $this->loadReleases();
    }

    public function loadReleases()
    {
        try {
            $update_checker = new ModuleUpdateChecker;
            $this->update = $update_checker->checkUpdate($this->token, $this->module, 'update')['data'];
            $this->support = $update_checker->checkSupportExpiryStatus($this->module['payload']['support_until']);
            $this->latestVersion = $this->update['latest_version'];
            $this->update_id = $this->update['update_id'];
            $this->has_sql_update = $this->update['has_sql_update'];
            $this->versionLog = $update_checker->getVersionLog($this->module['item_id']);
        } catch (\Exception $e) {
            // Handle error case
            $this->notify(['type' => 'danger', 'message' => t('failed_to_load_release_data')]);
        }
    }

    public function save()
    {
        $this->validate();

        $update_checker = new ModuleUpdateChecker;
        $response = $update_checker->downloadUpdate($this->update_id, $this->has_sql_update, $this->latestVersion, $this->token, $this->purchase_key, $this->username, 'update', $this->module['item_id']);
        $this->notify(['type' => ($response['success'] == true) ? 'success' : 'danger', 'message' => $response['message']]);

        if ($response['success'] == true) {
            $this->module['version'] = $this->latestVersion;
            $this->module->save();
            clear_view();

            return redirect(route('admin.modules.check.update', ['itemId' => $this->module['item_id']]));
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

        // Use the paginator data for view
        return view('modules::livewire.module-update');
    }
}
