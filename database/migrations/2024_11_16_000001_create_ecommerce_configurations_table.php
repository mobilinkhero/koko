<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ecommerce_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('is_configured')->default(false);
            $table->text('google_sheets_url')->nullable();
            $table->string('products_sheet_id')->nullable();
            $table->string('orders_sheet_id')->nullable();
            $table->string('customers_sheet_id')->nullable();
            $table->json('payment_methods')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->json('shipping_settings')->nullable();
            $table->text('order_confirmation_message')->nullable();
            $table->text('payment_confirmation_message')->nullable();
            $table->json('abandoned_cart_settings')->nullable();
            $table->json('upselling_settings')->nullable();
            $table->boolean('ai_recommendations_enabled')->default(true);
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('configuration_completed_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_configurations');
    }
};
