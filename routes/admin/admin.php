<?php

use App\Http\Controllers\Admin\TenantLanguageController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SanitizeInputs;
use App\Livewire\Admin\CreditManagement\CreditDetails;
use App\Livewire\Admin\CreditManagement\CreditList;
use App\Livewire\Admin\Currency\CurrencyList;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Department\DepartmentList;
use App\Livewire\Admin\EmailTemplate\EmailTemplateList;
use App\Livewire\Admin\EmailTemplate\EmailTemplateSave;
use App\Livewire\Admin\Faq\ManageFaqs;
use App\Livewire\Admin\Page\ManagePages;
use App\Livewire\Admin\Plan\PlanCreator;
use App\Livewire\Admin\Plan\PlanList;
use App\Livewire\Admin\Profile\AdminProfileManager;
use App\Livewire\Admin\Role\RoleCreator;
use App\Livewire\Admin\Role\RoleList;
use App\Livewire\Admin\Sales\InvoicesList;
use App\Livewire\Admin\Sales\SubscriptionList;
use App\Livewire\Admin\Settings\Language\LanguageManager;
use App\Livewire\Admin\Settings\Language\TenantLanguageManager;
use App\Livewire\Admin\Settings\Language\TenantTranslationManager;
use App\Livewire\Admin\Settings\Language\TranslationManager;
use App\Livewire\Admin\Tax\TaxList;
use App\Livewire\Admin\Tenant\TenantCreator;
use App\Livewire\Admin\Tenant\TenantList;
use App\Livewire\Admin\Tenant\TenantView;
use App\Livewire\Admin\Theme\ThemeManager;
use App\Livewire\Admin\User\UserCreator;
use App\Livewire\Admin\User\UserDetails;
use App\Livewire\Admin\User\UserList;
use App\Livewire\Admin\WhatsApp\WebhookConfiguration;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', SanitizeInputs::class, AdminMiddleware::class, EnsureEmailIsVerified::class])

    ->group(function () {
        // Dashboard
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('/my-profile', AdminProfileManager::class)->name('profile');

        // Whatsapp Webhook
        Route::get('/whatsapp-webhook', WebhookConfiguration::class)->name('whatsapp-webhook');

        // FAQs
        Route::get('/faqs', ManageFaqs::class)->name('faqs');

        // Pages
        Route::get('/pages', ManagePages::class)->name('pages');

        // Plans
        Route::get('/plans', PlanList::class)->name('plans.list');
        Route::get('/plans/plan/{planId?}', PlanCreator::class)->name('plans.create');

        // Sales
        Route::get('/subscription', SubscriptionList::class)->name('subscription.list');
        Route::get('/subscription/{id}', [SubscriptionController::class, 'showAdminSubscription'])->name('subscriptions.show');
        Route::get('/invoices', InvoicesList::class)->name('invoices.list');
        Route::get('/invoices/{id}', [InvoiceController::class, 'showAdminInvoice'])->name('invoices.show');
        Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'showInvoicePdf'])->name('invoices.pdf');
        Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadInvoicePdf'])->name('invoices.download');

        // Theme
        Route::get('/theme', ThemeManager::class)->name('theme');

        // Language
        Route::get('/languages', LanguageManager::class)->name('languages');
        Route::get('/languages/{code}/translations', TranslationManager::class)->name('languages.translations');

        // Tenant Languages
        Route::get('/tenant-languages', TenantLanguageManager::class)->name('tenant-languages');
        Route::get('/tenant-languages/{code}/translations', TenantTranslationManager::class)->name('tenant.languages.translations');

        // Tenant Language Management Routes
        Route::get('/languages/tenant/{tenantId}/{code}/download', [TenantLanguageController::class, 'download'])->name('languages.tenant.download');

        // Users
        Route::get('users', UserList::class)->name('users.list');
        Route::get('users/user/{userId?}', UserCreator::class)->name('users.save');
        Route::get('users/{userId?}', UserDetails::class)->name('users.details');

        // Role
        Route::get('roles', RoleList::class)->name('roles.list');
        Route::get('roles/role/{roleId?}', RoleCreator::class)->name('roles.save');

        // Department
        Route::get('department', DepartmentList::class)->name('department.list');

        // Email Template
        Route::get('email-template', EmailTemplateList::class)->name('email-template.list');
        Route::get('email-template/template/{id?}', EmailTemplateSave::class)->name('email-template.save');

        // Currencies
        Route::get('currencies', CurrencyList::class)->name('currencies.manage');

        // Coupons
        Route::get('coupons', App\Livewire\Admin\Coupon\CouponIndex::class)->name('coupons.list');
        Route::get('coupons/create', App\Livewire\Admin\Coupon\CouponForm::class)->name('coupons.create');
        Route::get('coupons/{id}/edit', App\Livewire\Admin\Coupon\CouponForm::class)->name('coupons.edit');

        // Taxes
        Route::get('taxes', TaxList::class)->name('taxes.manage');

        // Tenants
        Route::get('tenants', TenantList::class)->name('tenants.list');
        Route::get('tenants/tenant/{tenantId?}', TenantCreator::class)->name('tenants.save');
        Route::get('/tenants/{tenantId}', TenantView::class)->name('tenants.view');
        // Credit Management
        Route::get('credit-management', CreditList::class)->name('credit-management.list');
        Route::get('credit-management/{tenantId?}', CreditDetails::class)->name('credit-management.details');

        Route::get('/settings/webhooks', [App\Http\Controllers\Admin\WebhookSettingsController::class, 'index'])->name('settings.webhooks');
        Route::post('/settings/webhooks/configure', [App\Http\Controllers\Admin\WebhookSettingsController::class, 'configure'])->name('settings.webhooks.configure');
        Route::get('/settings/webhooks/{provider}', [App\Http\Controllers\Admin\WebhookSettingsController::class, 'list'])->name('settings.webhooks.list');
        Route::get('/settings/webhooks/{provider}/{webhookId}', [App\Http\Controllers\Admin\WebhookSettingsController::class, 'show'])->name('settings.webhooks.show');
        Route::delete('/settings/webhooks/{provider}/{webhookId}', [App\Http\Controllers\Admin\WebhookSettingsController::class, 'destroy'])->name('settings.webhooks.destroy');

        Route::prefix('transactions')->name('transactions.')->controller(TransactionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/approve', 'approve')->name('approve');
            Route::post('/{id}/reject', 'reject')->name('reject');
        });

        Route::get('login-as/{id}', [AuthenticatedSessionController::class, 'login_as'])->name('login.as');

    });

require __DIR__.'/system-settings.php';
require __DIR__.'/website-settings.php';
require __DIR__.'/modules.php';
