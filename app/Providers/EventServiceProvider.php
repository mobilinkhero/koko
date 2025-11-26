<?php

namespace App\Providers;

use App\Events\InvoicePaid;
use App\Events\NewRegistered;
use App\Events\PaymentApproved;
use App\Events\PaymentRejected;
use App\Events\SubscriptionActivated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionRenewed;
use App\Events\TransactionCreated;
use App\Events\TransactionFailed;
use App\Events\TransactionPending;
use App\Events\TransactionSuccessful;
use App\Listeners\ClearSpatieSettingsCache;
use App\Listeners\Language\ClearLanguageCaches;
use App\Listeners\SendInvoiceReceipt;
use App\Listeners\SendPaymentApprovedMail;
use App\Listeners\SendPaymentRejectedMail;
use App\Listeners\SendSubscriptionActivatedEmail;
use App\Listeners\SendSubscriptionCancelledEmail;
use App\Listeners\SendSubscriptionCreatedEmail;
use App\Listeners\SendSubscriptionRenewedEmail;
use App\Listeners\SendTransactionCreatedEmail;
use App\Listeners\SendTransactionFailedEmail;
use App\Listeners\SendTransactionPendingEmail;
use App\Listeners\SendTransactionSuccessfulEmail;
use App\Listeners\SendWelcomeEmailToNewTenant;
use App\Listeners\TenantCacheManager;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Keep the standard Registered handler too if needed
        NewRegistered::class => [
            SendWelcomeEmailToNewTenant::class,  // Add this line
        ],
        // Subscription Events
        SubscriptionActivated::class => [
            SendSubscriptionActivatedEmail::class,
        ],
        SubscriptionCreated::class => [
            SendSubscriptionCreatedEmail::class,
        ],
        SubscriptionCancelled::class => [
            SendSubscriptionCancelledEmail::class,
        ],
        SubscriptionRenewed::class => [
            SendSubscriptionRenewedEmail::class,
        ],
        TransactionCreated::class => [
            SendTransactionCreatedEmail::class,
        ],
        TransactionSuccessful::class => [
            SendTransactionSuccessfulEmail::class,
        ],
        TransactionPending::class => [
            SendTransactionPendingEmail::class,
        ],

        TransactionFailed::class => [
            SendTransactionFailedEmail::class,
        ],

        // Invoice Events when downgrading plans
        InvoicePaid::class => [
            SendInvoiceReceipt::class,
        ],

        PaymentApproved::class => [
            SendPaymentApprovedMail::class,
        ],

        PaymentRejected::class => [
            SendPaymentRejectedMail::class,
        ],

        // Corbital Settings Events
        'Corbital\\Settings\\Events\\SettingCreated' => [
            'Corbital\\Settings\\Listeners\\ClearSettingsCache',
        ],
        'Corbital\\Settings\\Events\\SettingUpdated' => [
            'Corbital\\Settings\\Listeners\\ClearSettingsCache',
        ],
        'Corbital\\Settings\\Events\\SettingDeleted' => [
            'Corbital\\Settings\\Listeners\\ClearSettingsCache',
        ],

        // Spatie Settings Events
        SettingsSaved::class => [
            ClearSpatieSettingsCache::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        TenantCacheManager::class,
        ClearLanguageCaches::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    public function shouldDiscoverEvents()
    {
        return false;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            TaxServiceProvider::class,
        ];
    }
}
