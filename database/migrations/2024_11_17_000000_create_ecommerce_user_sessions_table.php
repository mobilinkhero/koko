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
        Schema::create('ecommerce_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('contact_id');
            $table->string('phone');
            $table->string('current_step')->default('idle'); // idle, quantity_selection, awaiting_custom_qty, invoice_review, payment_selection
            $table->json('session_data')->nullable(); // Store product_id, quantity, etc.
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['tenant_id', 'contact_id']);
            $table->index(['tenant_id', 'phone']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_user_sessions');
    }
};
