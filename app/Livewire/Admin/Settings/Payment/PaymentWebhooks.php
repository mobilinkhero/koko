<?php

namespace App\Livewire\Admin\Settings\Payment;

use Illuminate\Support\Str;
use Livewire\Component;

class PaymentWebhooks extends Component
{
    public $webhooks = [
        'stripe' => [
            'webhook_url' => null,
            'webhook_secret' => null,
            'events' => ['payment_succeeded', 'payment_failed', 'subscription_created', 'subscription_updated', 'subscription_canceled'],
            'is_configured' => false,
        ],
    ];

    // Remove the activeTab property
    // public $activeTab = 'stripe';

    public $regeneratingSecret = false;

    public $newSecret = '';

    private $currentGateway = null; // New property to track current action gateway

    public function mount()
    {
        $baseUrl = url('/');
        $settings = get_settings_by_group('payment');

        foreach ($this->webhooks as $gateway => &$config) {
            $config['webhook_url'] = "{$baseUrl}/webhooks/{$gateway}";

            // Use PaymentSettings class
            $config['webhook_secret'] = $settings->{$gateway.'_webhook_secret'} ?? null;
            $config['webhook_id'] = $settings->{$gateway.'_webhook_id'} ?? null;
            $config['is_configured'] = ! empty($config['webhook_secret']) || ! empty($config['webhook_id']);
        }
    }
    // No need for setActiveTab method anymore

    public function regenerateWebhookSecret($gateway)
    {
        $this->currentGateway = $gateway;
        $this->regeneratingSecret = true;
        $this->newSecret = Str::random(32);
    }

    public function saveWebhookSecret($gateway)
    {
        if (empty($this->newSecret)) {
            return;
        }

        $settings = get_settings_by_group('payment');
        $settings->{$gateway.'_webhook_secret'} = $this->newSecret;
        $settings->save();

        $this->webhooks[$gateway]['webhook_secret'] = $this->newSecret;
        $this->webhooks[$gateway]['is_configured'] = true;

        $this->newSecret = '';
        $this->regeneratingSecret = false;
        $this->currentGateway = null;

        $this->notify([
            'type' => 'success',
            'message' => t('webhook_secret_updated_successfully'),
        ]);
    }

    public function cancelRegenerateSecret()
    {
        $this->regeneratingSecret = false;
        $this->newSecret = '';
        $this->currentGateway = null;
    }

    public function updateWebhookId($gateway, $webhookId)
    {
        $settings = get_settings_by_group('payment');
        $settings->{$gateway.'_webhook_id'} = $webhookId;
        $settings->save();

        $this->webhooks[$gateway]['webhook_id'] = $webhookId;
        $this->webhooks[$gateway]['is_configured'] = ! empty($webhookId);

        $this->notify([
            'type' => 'success',
            'message' => t('webhook_id_updated_successfully'),
        ]);
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('clipboard-copy', text: $text);

        $this->notify([
            'type' => 'success',
            'message' => t('copied_to_clipboard'),
        ]);
    }

    public function notify($options)
    {
        // This would link with your notification system
        $this->dispatch('notify', $options);
    }

    public function render()
    {
        return view('livewire.admin.settings.payment.payment-webhooks');
    }
}
