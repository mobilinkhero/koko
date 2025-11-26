<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentGateways\OfflinePaymentController;
use App\Http\Controllers\PaymentGateways\PayPalController;
use App\Http\Controllers\PaymentGateways\PaystackPaymentController;
use App\Http\Controllers\PaymentGateways\RazorpayController;
use App\Http\Controllers\PaymentGateways\StripeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Tenant\BotFlowController;
use App\Http\Controllers\Tenant\ManageCampaigns;
use App\Http\Controllers\Tenant\ManageChat;
use App\Http\Controllers\Tenant\WhatsappDynamicTemplateController;
use App\Http\Controllers\Whatsapp\WhatsAppWebhookController;
use App\Http\Middleware\CheckTenantDeleted;
use App\Http\Middleware\TenantMiddleware;
use App\Livewire\Tenant\ActivityLogDetails;
use App\Livewire\Tenant\ActivityLogList;
use App\Livewire\Tenant\Bot\MessageBotCreator;
use App\Livewire\Tenant\Bot\MessageBotList;
use App\Livewire\Tenant\Bot\TemplateBotCreator;
use App\Livewire\Tenant\Bot\TemplateBotList;
use App\Livewire\Tenant\Campaign\CampaignDetails;
use App\Livewire\Tenant\Campaign\CampaignList;
use App\Livewire\Tenant\Campaign\CsvCampaign;
use App\Livewire\Tenant\Chat\ManageAiPrompt;
use App\Livewire\Tenant\Chat\ManageCannedReply;
use App\Livewire\Tenant\Contact\ContactCreator;
use App\Livewire\Tenant\Contact\ContactList;
use App\Livewire\Tenant\Contact\ImportContact;
use App\Livewire\Tenant\Contact\ImportLogs;
use App\Livewire\Tenant\Contact\ManageSource;
use App\Livewire\Tenant\Contact\ManageStatus;
use App\Livewire\Tenant\CustomField\CustomFieldCreator;
use App\Livewire\Tenant\CustomField\CustomFieldList;
use App\Livewire\Tenant\Dashboard;
use App\Livewire\Tenant\EmailTemplate\EmailTemplateList;
use App\Livewire\Tenant\EmailTemplate\EmailTemplateSave;
use App\Livewire\Tenant\FlowBot\FlowList;
use App\Livewire\Tenant\Group\GroupList;
use App\Livewire\Tenant\Profile\ProfileManager;
use App\Livewire\Tenant\Profile\SubscriptionDetails;
use App\Livewire\Tenant\Role\TenantRoleCreator;
use App\Livewire\Tenant\Role\TenantRoleList;
use App\Livewire\Tenant\Settings\Language\TenantLanguageManager;
use App\Livewire\Tenant\Settings\Language\TenantTranslationManager;
use App\Livewire\Tenant\Staff\StaffCreator;
use App\Livewire\Tenant\Staff\StaffDetails;
use App\Livewire\Tenant\Staff\StaffList;
use App\Livewire\Tenant\Subscription\Dashboard as subscription_dashboard;
use App\Livewire\Tenant\Template\TemplateList;
use App\Livewire\Tenant\TenantSubscription\BillingDetails;
use App\Livewire\Tenant\TenantSubscription\MySubscription;
use App\Livewire\Tenant\TenantSubscription\SubscriptionPending;
use App\Livewire\Tenant\Waba\ConnectWaba;
use App\Livewire\Tenant\Waba\DisconnectWaba;
use App\Models\Tenant;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are automatically scoped to the current tenant
| and are protected with tenant middleware.
|
*/

Route::middleware(['auth', 'senitize.inputs', TenantMiddleware::class, CheckTenantDeleted::class, EnsureEmailIsVerified::class])->group(function () {
    Route::prefix('/{subdomain}')->as('tenant.')->group(function () {});
});

Route::middleware(['auth', TenantMiddleware::class, CheckTenantDeleted::class, EnsureEmailIsVerified::class])
    ->prefix('/{subdomain}')
    ->as('tenant.')
    ->group(function () {

        // Route with additional 'sanitize.inputs'
        Route::middleware('senitize.inputs')->group(function () {
            Route::get('/', Dashboard::class)->name('dashboard');
            // Contacts
            Route::get('/contacts', ContactList::class)->name('contacts.list');
            Route::get('/contacts/contact/{contactId?}', ContactCreator::class)->name('contacts.save');

            Route::get('/status', ManageStatus::class)->name('status');
            Route::get('/source', ManageSource::class)->name('source');
            Route::get('/importcontact', ImportContact::class)->name('contacts.imports');
            Route::get('/contacts/imports', ImportLogs::class)->name('contacts.import_log');

            // WhatsApp API
            Route::get('/connect', ConnectWaba::class)->name('connect');
            Route::get('/waba', DisconnectWaba::class)->name('waba');

            // Templates & Bots
            Route::get('/template', TemplateList::class)->name('template.list');

            Route::get('/message-bot', MessageBotList::class)->name('messagebot.list');
            Route::get('/message-bot/bot/{messagebotId?}', MessageBotCreator::class)->name('messagebot.create');

            Route::get('/template-bot', action: TemplateBotList::class)->name('templatebot.list');
            Route::get('/template-bot/bot/{templatebotId?}', TemplateBotCreator::class)->name('templatebot.create');

            // Campaigns
            Route::get('/campaigns', CampaignList::class)->name('campaigns.list');
            Route::get('/campaigns/campaign/details/{campaignId?}', CampaignDetails::class)->name('campaigns.details');

            // CSV Campaign Feature
            Route::get('/csvcampaign', CsvCampaign::class)->name('csvcampaign');

            Route::get('/activity-log', ActivityLogList::class)->name('activity-log.list');
            Route::get('/activity-log/{logId?}', ActivityLogDetails::class)->name('activity-log.details');

            // Chat
            Route::get('ai-prompt', ManageAiPrompt::class)->name('ai-prompt');
            Route::get('canned-reply', ManageCannedReply::class)->name('canned-reply');

            // Personal Assistant
            Route::get('/ai-assistant', \App\Livewire\Tenant\AI\PersonalAssistantManager::class)->name('ai-assistant');

            // Staff & Profile
            Route::get('/staff', StaffList::class)->name('staff.list');
            Route::get('/staff/member/{staffId?}', StaffCreator::class)->name('staff.save');
            Route::get('/staff/{staffId?}', StaffDetails::class)->name('staff.details');
            Route::get('/profile', ProfileManager::class)->name('profile');

            Route::get('roles', TenantRoleList::class)->name('roles.list');
            Route::get('roles/role/{roleId?}', TenantRoleCreator::class)->name('roles.save');

            Route::get('/subscription', SubscriptionDetails::class)->name('subscription.details');

            Route::get('/subscription', subscription_dashboard::class)->name('subscription-list');
            Route::get('/subscriptions/plans', [SubscriptionController::class, 'publicPlans'])->name('subscriptions.public');

            Route::get('emails', EmailTemplateList::class)->name('emails');
            Route::get('emails/{id?}', EmailTemplateSave::class)->name('emails.save');

            Route::get('groups', GroupList::class)->name('groups.list');
            // Route::get('groups/{id?}', GroupCreator::class)->name('groups.create');

            // Language
            Route::get('/languages', TenantLanguageManager::class)->name('languages');
            Route::get('/languages/{code}/translations', TenantTranslationManager::class)->name('languages.translations');

            // tenant-subscription
            Route::get('/billing', BillingDetails::class)->name('billing');
            Route::get('available-plans', MySubscription::class)->name('subscription');
            Route::get('pending', SubscriptionPending::class)->name('subscription.pending');

            // Payment routes
            Route::post('/checkout/process', [SubscriptionController::class, 'processCheckout'])->name('checkout.process');

            // Subscriptions
            Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
            Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

            // Subscription Management Actions
            Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::post('/subscriptions/{id}/toggle-recurring', [SubscriptionController::class, 'toggleRecurring'])->name('subscriptions.toggle-recurring');

            // Plan Upgrade/Downgrade
            Route::get('/subscriptions/{id}/upgrade', [SubscriptionController::class, 'upgradeForm'])->name('subscriptions.upgrade');
            Route::post('/subscriptions/{id}/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade.process');
            Route::get('/subscriptions/{id}/downgrade', [SubscriptionController::class, 'downgradeForm'])->name('subscriptions.downgrade');
            Route::post('/subscriptions/{id}/downgrade', [SubscriptionController::class, 'downgrade'])->name('subscriptions.downgrade.process');

            // Thank You
            Route::get('/thank-you/{invoice}', [SubscriptionController::class, 'thankYou'])->name('subscription.thank-you');

            Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
            Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
            Route::get('/invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');
            Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'showPdf'])->name('invoices.pdf');
            Route::get('/checkout/resume/{id}', [InvoiceController::class, 'resumeCheckout'])->name('checkout.resume');
            Route::get('/invoices/{id}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');

            // Offline Payment Gateway
            Route::prefix('payment/offline')->name('payment.offline.')->group(function () {
                Route::get('/checkout/{invoice}', [OfflinePaymentController::class, 'checkout'])->name('checkout');
                Route::post('/process/{invoice}', [OfflinePaymentController::class, 'process'])->name('process');
            });

            Route::prefix('payment/stripe')->name('payment.stripe.')->group(function () {
                Route::get('/checkout/{invoice}', [StripeController::class, 'checkout'])->name('checkout');
                Route::post('/confirm', [StripeController::class, 'confirm'])->name('confirm');
                Route::get('/auto-billing-data', [StripeController::class, 'autoBillingData'])->name('auto_billing_data');
                Route::post('/process/{invoice}', [StripeController::class, 'process'])->name('process');
                Route::get('/return/{invoice}', [StripeController::class, 'handleReturn'])->name('return');
            });

            Route::prefix('payment/razorpay')->name('payment.razorpay.')->group(function () {
                Route::get('/checkout/{invoice}', [RazorpayController::class, 'checkout'])->name('checkout');
                Route::post('/confirm', [RazorpayController::class, 'confirm'])->name('confirm');
                Route::get('/auto-billing-data', [RazorpayController::class, 'autoBillingData'])->name('auto_billing_data');
                // Webhook endpoint - exempted from CSRF protection
                Route::post('/webhook', [RazorpayController::class, 'webhook'])
                    ->withoutMiddleware('web');

                // Admin recovery route for handling payments that weren't properly marked as paid
                Route::post('/reprocess/{invoice}', [RazorpayController::class, 'reprocessPayment'])
                    ->middleware('auth.admin')
                    ->name('reprocess');
                // Authentication route for recurring payments (required by RBI regulations)
                Route::get('/authenticate', [RazorpayController::class, 'authenticatePayment'])->name('authenticate');
            });

            // PayPal Payment Gateway
            Route::prefix('payment/paypal')->name('payment.paypal.')->group(function () {
                Route::get('/checkout/{invoice}', [PayPalController::class, 'checkout'])->name('checkout');
                Route::post('/process/{invoice}', [PayPalController::class, 'process'])->name('process');
                Route::post('/retry/{invoice}', [PayPalController::class, 'handlePendingTransaction'])->name('retry');
                Route::get('/capture/{invoice}', [PayPalController::class, 'capture'])->name('capture');

                // Subscription management routes
                Route::post('/subscription/verify/{subscription}', [PayPalController::class, 'verifySubscriptionStatus'])->name('subscription.verify');
            });

            // Paystack Payment Gateway
            Route::prefix('payment/paystack')->name('payment.paystack.')->group(function () {
                Route::get('/checkout/{invoice}', [PaystackPaymentController::class, 'checkout'])->name('checkout');
                Route::post('/process/{invoice}', [PaystackPaymentController::class, 'process'])->name('process');
                Route::get('/callback', [PaystackPaymentController::class, 'callback'])->name('callback');
            });

            // manage campaign
            Route::get('/create', [ManageCampaigns::class, 'create'])->name('create');
            Route::post('campaign/store', [ManageCampaigns::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ManageCampaigns::class, 'edit'])->name('edit');
            Route::put('/{id}', [ManageCampaigns::class, 'update'])->name('update');
            Route::delete('/{id}', [ManageCampaigns::class, 'destroy'])->name('destroy');

            Route::get('campaign', [ManageCampaigns::class, 'index'])->name('campaign');
            Route::post('/campaign/ajax/count-contacts', [ManageCampaigns::class, 'countContacts'])->name('campaign.contacts-counts');
            Route::post('/campaign/ajax/template', [ManageCampaigns::class, 'getTemplate'])->name('campaign.template');
            Route::get('/create', [ManageCampaigns::class, 'create'])->name('create');
            Route::post('campaign/store', [ManageCampaigns::class, 'store'])->name('store');
            Route::get('campaign/edit/{id}', [ManageCampaigns::class, 'edit'])->name('campaign.edit');
            Route::put('campaign/{id}', [ManageCampaigns::class, 'update'])->name('campaign.update');
            Route::delete('campaign/delete/{id}', [ManageCampaigns::class, 'destroy'])->name('campaign.destroy');
            Route::post('/campaign/contacts-paginated', [ManageCampaigns::class, 'getContactsPaginated'])->name('campaign.contacts-paginated');
            Route::post('/campaign/search-contacts', [ManageCampaigns::class, 'searchContacts'])->name('campaign.search-contacts');
            // Manage Chat
            Route::get('chat', [ManageChat::class, 'index'])->name('chat');
            Route::post('initiate_chat/{chatId?}', [ManageChat::class, 'save'])->name('initiate_chat');
            Route::get('chat_messages/{chatId?}/{lastMessageId?}', [ManageChat::class, 'messagesGet'])->name('chat_messages');
            Route::post('chat_data/{lastChatId?}', [ManageChat::class, 'getChats'])->name('chats');
            Route::post('remove-message/{messageId?}', [ManageChat::class, 'removeMessage'])->name('remove_message');
            Route::post('remove-chat/{chatId?}', [ManageChat::class, 'removeChat'])->name('remove_chat');
            Route::post('assign-agent/{chatId?}', [ManageChat::class, 'assignSupportAgent'])->name('assign-agent');
            Route::get('assign-agent-layout/{chatId?}', [ManageChat::class, 'getSupportAgentView'])->name('assign-agent-layout');
            Route::post('ai-response', [ManageChat::class, 'processAiResponse'])->name('ai_response');
            Route::post('user-information', [ManageChat::class, 'userInformation'])->name('user_information');
            Route::post('load-mergefields/{chatType}', [ManageChat::class, 'loadMergeFields'])->name('load_mergefields');
            Route::post('update-contact-status', [ManageChat::class, 'updateContactStatus'])->name('update_contact_status');
            Route::post('update-contact-groups', [ManageChat::class, 'updateContactGroups'])->name('update_contact_groups');
            Route::post('update-contact-source', [ManageChat::class, 'updateContactSource'])->name('update_contact_source');

            // Vue Flow
            Route::get('/bot-flow-list', FlowList::class)->name('bot-flow_list');
            Route::get('bot-flows/edit/{id}', [BotFlowController::class, 'edit'])->name('bot-flows.edit');
            Route::post('bot-flows/save', [BotFlowController::class, 'saveBotFlow'])->name('bot-flows.save');
            Route::get('/get-bot-flow/{id}', [BotFlowController::class, 'get']);
            Route::post('/save-bot-flow', [BotFlowController::class, 'save']);
            Route::get('/whatsapp-templates', [BotFlowController::class, 'getTemplates']);
            Route::post('/upload-media', [BotFlowController::class, 'upload']);
            // Dynamic Templates
            Route::get('/dynamic-template', [WhatsappDynamicTemplateController::class, 'create'])->name('dynamic-template.index');
            Route::post('/dynamic-template', [WhatsappDynamicTemplateController::class, 'store'])->name('dynamic-template.store');
            Route::get('/dynamic-template/{id}', [WhatsappDynamicTemplateController::class, 'show'])->name('dynamic-template.show');
            Route::post('/dynamic-template/{id}/update', [WhatsappDynamicTemplateController::class, 'update'])->name('dynamic-template.update');

            // NEW: File upload routes
            Route::post('/dynamic-template/upload-media', [WhatsappDynamicTemplateController::class, 'uploadMedia'])->name('tenant.dynamic-template.upload-media');

            Route::get('/custom-fields', CustomFieldList::class)->name('custom-fields.list');
            Route::get('/custom-fields/field/{fieldId?}', CustomFieldCreator::class)->name('custom-fields.save');

            // E-commerce Routes
            Route::prefix('abc/ecommerce')->name('ecommerce.')->group(function () {
                Route::get('/', \App\Livewire\Tenant\Ecommerce\EcommerceDashboard::class)->name('dashboard');
                Route::get('/setup', \App\Livewire\Tenant\Ecommerce\EcommerceSetup::class)->name('setup');
                Route::get('/products', \App\Livewire\Tenant\Ecommerce\ProductManager::class)->name('products');
                Route::get('/orders', \App\Livewire\Tenant\Ecommerce\OrderManager::class)->name('orders');
                Route::get('/analytics', \App\Livewire\Tenant\Ecommerce\EcommerceAnalytics::class)->name('analytics');
                Route::get('/settings', \App\Livewire\Tenant\Ecommerce\EcommerceSettings::class)->name('settings');
            });
            Route::get('/custom-fields/{customFieldId}/edit', CustomFieldCreator::class)->name('custom-fields.edit');

            Route::post('/coupons/validate', [CouponController::class, 'validate'])->name('coupon.validate');
            Route::post('/coupons/apply', [CouponController::class, 'apply'])->name('coupon.apply');
            Route::post('/coupons/remove', [CouponController::class, 'remove'])->name('coupon.remove');
        });
        // Route without 'sanitize.inputs'
        Route::post('send-message', [WhatsAppWebhookController::class, 'send_message'])->name('send_message');
    });

// PayPal webhook route (outside middleware groups)
Route::post('/webhooks/paypal', [PayPalController::class, 'handleWebhook'])
    ->name('webhooks.paypal')
    ->withoutMiddleware(['web', 'auth', 'verified']);
