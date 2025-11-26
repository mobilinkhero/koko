<?php

namespace App\Livewire\Admin\WhatsApp;

use App\Traits\WhatsApp;
use Livewire\Component;
use Str;

class WebhookConfiguration extends Component
{
    use WhatsApp;

    public $wm_fb_app_id;

    public $wm_fb_app_secret;

    public $is_webhook_connected = false;

    public $wm_fb_config_id;

    protected $rules = [
        'wm_fb_app_id' => 'required',
        'wm_fb_app_secret' => 'required',
    ];

    protected $messages = [
        'wm_fb_app_id.required' => 'The Facebook App ID is required.',
        'wm_fb_app_secret.required' => 'The Facebook App Secret is required.',
    ];

    public function mount()
    {
        if (! auth()->user()->is_admin === true) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.dashboard'));
        }

        $wmSettings = get_batch_settings([
            'whatsapp.wm_fb_app_id',
            'whatsapp.wm_fb_app_secret',
            'whatsapp.is_webhook_connected',
            'whatsapp.wm_fb_config_id',
        ]);
        $this->wm_fb_app_id = $wmSettings['whatsapp.wm_fb_app_id'] ?? '';
        $this->wm_fb_app_secret = $wmSettings['whatsapp.wm_fb_app_secret'] ?? '';
        $this->is_webhook_connected = (bool) $wmSettings['whatsapp.is_webhook_connected'] ?? false;
        $this->wm_fb_config_id = $wmSettings['whatsapp.wm_fb_config_id'] ?? '';
    }

    public function saveConfiguration()
    {
        $this->validate();

        set_setting('whatsapp.wm_fb_app_id', $this->wm_fb_app_id);
        set_setting('whatsapp.wm_fb_app_secret', $this->wm_fb_app_secret);
        set_setting('whatsapp.wm_fb_config_id', $this->wm_fb_config_id);

        $this->notify(['message' => t('configuration_stored_successfully'), 'type' => 'success']);
    }

    public function connectHook()
    {
        $webhook_verify_token = Str::random(16);
        set_setting('whatsapp.webhook_verify_token', $webhook_verify_token);

        $response = $this->connectWebhook();

        if ($response['status']) {
            set_setting('whatsapp.is_webhook_connected', true);
            $this->is_webhook_connected = true;
            $this->notify(['message' => t('webhook_connected_successfully'), 'type' => 'success']);
        } else {
            $this->notify(['message' => $response['message'], 'type' => 'danger']);
        }
    }

    public function disconnectHook()
    {
        $response = $this->disconnectWebhook();
        set_setting('whatsapp.is_webhook_connected', false);
        $this->is_webhook_connected = false;
        $this->notify(['message' => t('whatsapp_webhook_disconnected'), 'type' => 'success']);
    }

    public function verifyWebhook()
    {

        $this->js(<<<'JS'
            this.processing = true;

            axios.post('/whatsapp/webhook', {
                message: 'ctl_whatsmark_saas_ping',
                identifier: Date.now(),
                timestamp: new Date().toISOString()
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                showNotification(response.data.message, response.data.status ? 'success' : 'danger');
            })
            .catch(error => {
                let errorMessage = error.response ? error.response.data.message : error.message;
                showNotification(errorMessage, 'danger');
            });
        JS);
    }

    public function render()
    {
        return view('livewire.admin.whats-app.webhook-configuration');
    }
}
