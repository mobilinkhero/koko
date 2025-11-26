<?php

namespace App\Livewire\Tenant\Waba;

use App\Models\Tenant\WhatsappTemplate;
use App\Services\SubscriptionCache;
use App\Traits\WhatsApp;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\CacheManager\Services\AdminCacheService;

class DisconnectWaba extends Component
{
    use WhatsApp;

    public $token_info = [];

    public $phone_numbers = [];

    public $message_details = [];

    public $wm_test_message;

    public $confirmingDeletion = false;

    public $limit = [];

    public function mount()
    {
        if (! checkPermission(['tenant.connect_account.view', 'tenant.connect_account.disconnect'])) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(tenant_route('tenant.dashboard'));
        }

        $phone_numbers = $this->getPhoneNumbers();
        $this->phone_numbers = $phone_numbers['data'] ?? [];
        $tenantWpSettings = tenant_settings_by_group('whatsapp');

        if ($phone_numbers['status']) {
            $webhook_configuration_url = array_column(array_column($phone_numbers['data'], 'webhook_configuration'), 'application');
            if (in_array(route('whatsapp.webhook'), $webhook_configuration_url)) {
                save_tenant_setting('whatsapp', 'is_webhook_connected', 1);
            } else {
                save_tenant_setting('whatsapp', 'is_webhook_connected', 0);
            }

            if (empty($tenantWpSettings['wm_default_phone_number_id']) || empty($tenantWpSettings['wm_default_phone_number'])) {
                $default_number = preg_replace('/\D/', '', $this->phone_numbers[array_key_first($this->phone_numbers)]['display_phone_number']);
                $default_number_id = preg_replace('/\D/', '', $this->phone_numbers[array_key_first($this->phone_numbers)]['id']);
                save_tenant_setting('whatsapp', 'wm_default_phone_number', $default_number);
                save_tenant_setting('whatsapp', 'wm_default_phone_number_id', $default_number_id);
                $this->registerPhoneNumber($default_number_id);
            }

            if (empty($tenantWpSettings['wm_health_data']) || empty($tenantWpSettings['wm_health_check_time'])) {
                $helthStatus = $this->getHealthStatus();
                save_tenant_setting('whatsapp', 'wm_health_check_time', date('l jS F Y g:i:s a'));
                save_tenant_setting('whatsapp', 'wm_health_data', json_encode($helthStatus['data']));
            }

            $this->limit = $this->getMessagingLimit();

            $data = $this->getProfile();
            $profile_data = collect($data['data'])->firstWhere('messaging_product', 'whatsapp');
            save_tenant_setting('whatsapp', 'wm_profile_picture_url', $profile_data['profile_picture_url'] ?? '');

            if (! empty($tenantWpSettings['wm_default_phone_number_id']) && ! empty($tenantWpSettings['wm_default_phone_number']) && ! file_exists(public_path('storage/tenant/'.tenant_id().'/images/qrcode.png'))) {
                @unlink(public_path('storage/tenant/'.tenant_id().'images/qrcode.png'));
                $this->generateUrlQR('https://wa.me/'.$tenantWpSettings['wm_default_phone_number'], true);
            }
        } else {
            save_tenant_setting('whatsapp', 'is_whatsmark_connected', 0);
        }

        if ($tenantWpSettings['is_whatsmark_connected'] == 0 || $tenantWpSettings['is_webhook_connected'] == 0) {
            return redirect()->to(tenant_route('tenant.connect'));
        }

        $this->message_details = $this->getMessageLimit()['data'];
        $this->message_details['limit_value'] = $this->limit['data']['limit_value'] ?? 1000;

        $tenantGeneralSettings = tenant_settings_by_group('general');
        $token_info = $this->debugToken($tenantWpSettings['wm_fb_app_id'] ?? '', $tenantWpSettings['wm_fb_app_secret'] ?? '');
        if ($token_info['status']) {
            $this->token_info = $token_info['data'];
            if (isset($this->token_info['issued_at']) && ! empty($this->token_info['issued_at'])) {
                $epoch_time = $this->token_info['issued_at'];
                $dt = new DateTime("@$epoch_time");
                $dt->setTimezone(new DateTimeZone((! empty($tenantGeneralSettings['timezone'])) ? $tenantGeneralSettings['timezone'] : 'Asia/kolkata'));
                $this->token_info['issued_at'] = $dt->format('l jS F Y g:i:s a');
            }
            $this->token_info['issued_at'] = $this->token_info['issued_at'] ?? '-';

            if (isset($this->token_info['expires_at']) && ! empty($this->token_info['expires_at'])) {
                $epoch_time = $this->token_info['expires_at'];
                $dt = new DateTime("@$epoch_time");
                $dt->setTimezone(new DateTimeZone((! empty($tenantGeneralSettings['timezone'])) ? $tenantGeneralSettings['timezone'] : 'Asia/kolkata'));
                $this->token_info['expires_at'] = $dt->format('l jS F Y g:i:s a');
            }
            $this->token_info['expires_at'] = $this->token_info['expires_at'] ?? 'NA';
        }
    }

    public function refreshHealth()
    {
        // get profile url
        $data = $this->getProfile();
        $profile_data = collect($data['data'])->firstWhere('messaging_product', 'whatsapp');
        save_tenant_setting('whatsapp', 'wm_profile_picture_url', $profile_data['profile_picture_url'] ?? '');

        // subscribe to webhook in case of note subscribe
        $this->subscribeWebhook();
        $tenantWpSettings = tenant_settings_by_group('whatsapp');
        if (! empty($tenantWpSettings['wm_default_phone_number_id']) && ! empty($tenantWpSettings['wm_default_phone_number']) && ! file_exists(public_path('storage/tenant/'.tenant_id().'/images/qrcode.png'))) {
            @unlink(public_path('storage/tenant/'.tenant_id().'images/qrcode.png'));
            $this->generateUrlQR('https://wa.me/'.$tenantWpSettings['wm_default_phone_number'], true);
        }

        // health data
        $response = $this->getHealthStatus();
        save_tenant_setting('whatsapp', 'wm_health_check_time', date('l jS F Y g:i:s a'));
        save_tenant_setting('whatsapp', 'wm_health_data', json_encode($response['data']));
        $this->notify(['message' => $response['message'] ?? t('health_status_updated'), 'type' => $response['status'] ? 'success' : 'danger']);
    }

    public function setDefaultNumber($wm_phone_number_id, $wm_default_phone_number)
    {
        save_tenant_setting('whatsapp', 'wm_default_phone_number_id', $wm_phone_number_id);
        $phone_number = preg_replace('/\D/', '', $wm_default_phone_number);
        save_tenant_setting('whatsapp', 'wm_default_phone_number', $phone_number);

        @unlink(public_path('storage/tenant/'.tenant_id().'images/qrcode.png'));
        $this->generateUrlQR("https://wa.me/$phone_number", true);

        $this->notify(['message' => t('default_number_updated'), 'type' => 'success']);
    }

    public function sendTestMessage()
    {
        $this->validate([
            'wm_test_message' => [
                'required',
                'regex:/^\+[1-9]\d{9,14}$/',
            ],
        ], [
            'wm_test_message.required' => t('mobile_number_field_required'),
            'wm_test_message.regex' => t('mobile_number_validation'),
        ]);
        $res = $this->sendTestMessages($this->wm_test_message);
        $this->notify(['status' => $res['status'] ?? false, 'message' => $res['message'] ?? t('something_went_wrong')]);
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

    public function disconnectAccount()
    {
        if (! checkPermission('tenant.connect_account.disconnect')) {
            $this->notify(['type' => 'danger', 'message' => t('access_denied')]);
            whatsapp_log('Unauthorized User trying to Disconnect Account', 'info', ['time' => time(), 'user' => Auth::user()->firstname.' '.Auth::user()->lastname, 'user-id' => Auth::id()]);

            return redirect(tenant_route('tenant.dashboard'));
        }
        whatsapp_log('Account Disconnected', 'info', ['time' => time(), 'user' => Auth::user()->firstname.' '.Auth::user()->lastname, 'user-id' => Auth::id()]);

        @unlink(public_path('storage/tenant/'.tenant_id().'images/qrcode.png'));

        $whatsappSettings = [
            'wm_business_account_id' => '',
            'wm_access_token' => '',
            'is_whatsmark_connected' => '0',
            'wm_default_phone_number' => '',
            'wm_default_phone_number_id' => '',
            'wm_health_check_time' => '',
            'wm_health_data' => '',
            'wm_profile_picture_url' => '',
        ];

        save_batch_tenant_setting('whatsapp', $whatsappSettings);

        SubscriptionCache::clearCache(tenant_id());

        $adminCacheService = new AdminCacheService;
        $adminCacheService->clearApplicationCache();

        WhatsappTemplate::where('tenant_id', tenant_id())->delete();

        $this->confirmingDeletion = false;

        $this->notify(['type' => 'danger', 'message' => t('account_disconnected')], true);

        return redirect()->to(tenant_route('tenant.connect'));
    }

    public function registerNumber($phone_number_id)
    {
        $response = $this->registerPhoneNumber($phone_number_id);
        if ($response['status']) {
            $this->notify(['type' => 'success', 'message' => t('phone_number_registered')]);
        } else {
            $this->notify(['type' => 'danger', 'message' => $response['message'] ?? t('something_went_wrong')]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.waba.disconnect-waba');
    }
}
