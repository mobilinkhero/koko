<?php

use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SanitizeInputs;
use App\Livewire\Admin\Payment\PaymentSettings;
use Illuminate\Support\Facades\Route;

Route::middleware([AdminMiddleware::class, SanitizeInputs::class])->group(function () {
    Route::get('/settings', PaymentSettings::class)->name('payment-settings');
    Route::prefix('settings/payment')->name('settings.payment.')->group(function () {
        // Offline Payment Settings
        Route::get('/offline', [PaymentSettingsController::class, 'showOfflineSettings'])->name('offline');
        Route::put('/offline', [PaymentSettingsController::class, 'updateOfflineSettings'])->name('offline.update');

        // Stripe payment settings
        Route::get('/stripe', [PaymentSettingsController::class, 'showStripeSettings'])->name('stripe');
        Route::post('/stripe', [PaymentSettingsController::class, 'updateStripeSettings'])->name('stripe.update');

        // Razorpay payment settings
        Route::get('/razorpay', [PaymentSettingsController::class, 'showRazorpaySettings'])->name('razorpay');
        Route::post('/razorpay', [PaymentSettingsController::class, 'updateRazorpaySettings'])->name('razorpay.update');

        // PayPal payment settings
        Route::get('/paypal', [PaymentSettingsController::class, 'showPayPalSettings'])->name('paypal');
        Route::post('/paypal', [PaymentSettingsController::class, 'updatePayPalSettings'])->name('paypal.update');

        // Paystack payment settings
        Route::get('/paystack', [PaymentSettingsController::class, 'showPaystackSettings'])->name('paystack');
        Route::post('/paystack', [PaymentSettingsController::class, 'updatePaystackSettings'])->name('paystack.update');
    });
});
