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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->integer('google_sheet_row_id')->nullable();
            $table->string('order_number')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 
                'shipped', 'delivered', 'cancelled', 'refunded'
            ])->default('pending');
            $table->json('items');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', [
                'pending', 'paid', 'failed', 'refunded', 'partial'
            ])->default('pending');
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            $table->string('source')->default('whatsapp');
            $table->string('sync_status')->default('pending');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payment_status']);
            $table->index(['tenant_id', 'customer_phone']);
            $table->index(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'contact_id']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
