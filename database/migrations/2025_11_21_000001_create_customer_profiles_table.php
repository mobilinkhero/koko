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
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('contact_id')->index();
            $table->string('full_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('tier', ['standard', 'regular', 'premium', 'vip'])->default('standard');
            $table->json('preferences')->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->decimal('average_order_value', 10, 2)->default(0);
            $table->json('favorite_categories')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('last_order_date')->nullable();
            $table->integer('behavioral_score')->default(50); // 0-100
            $table->enum('price_sensitivity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('interaction_frequency', ['new', 'occasional', 'regular', 'frequent'])->default('new');
            $table->string('preferred_language', 10)->default('english');
            $table->json('seasonal_patterns')->nullable();
            $table->json('purchase_timing')->nullable();
            $table->json('communication_preferences')->nullable();
            $table->json('ai_insights')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->unique(['tenant_id', 'contact_id']);
            $table->index(['tenant_id', 'tier']);
            $table->index(['tenant_id', 'behavioral_score']);
            $table->index(['tenant_id', 'last_interaction_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
