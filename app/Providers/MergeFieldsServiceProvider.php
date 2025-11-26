<?php

namespace App\Providers;

use App\MergeFields\Admin\InvoiceMergeFields;
use App\MergeFields\Admin\OtherMergeFields;
use App\MergeFields\Admin\PlanMergeFields;
use App\MergeFields\Admin\SubscriptionMergeFields;
use App\MergeFields\Admin\TenantMergeFields;
use App\MergeFields\Admin\TicketMergeFields;
use App\MergeFields\Admin\TransactionMergeFields;
use App\MergeFields\Admin\UserMergeFields;
use App\MergeFields\Tenant\ContactMergeFields;
use App\MergeFields\Tenant\OtherMergeFields as TenantOtherMergeFields;
use App\MergeFields\Tenant\UserMergeFields as TenantUserMergeFields;
use Corbital\LaravelEmails\Services\MergeFieldsService;
use Illuminate\Support\ServiceProvider;

class MergeFieldsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MergeFieldsService::class, function () {
            return new MergeFieldsService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(MergeFieldsService $mergeFields): void
    {
        $mergeFields->register(OtherMergeFields::class);
        $mergeFields->register(TenantMergeFields::class);
        $mergeFields->register(UserMergeFields::class);
        $mergeFields->register(InvoiceMergeFields::class);
        $mergeFields->register(PlanMergeFields::class);
        $mergeFields->register(SubscriptionMergeFields::class);
        $mergeFields->register(TransactionMergeFields::class);
        $mergeFields->register(TenantOtherMergeFields::class);
        $mergeFields->register(ContactMergeFields::class);
        $mergeFields->register(TenantUserMergeFields::class);
        $mergeFields->register(TicketMergeFields::class);
    }
}
