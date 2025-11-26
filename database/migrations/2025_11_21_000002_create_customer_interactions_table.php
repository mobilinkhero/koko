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
        Schema::create('customer_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('contact_id')->index();
            $table->string('interaction_type', 50); // message, order, support, etc.
            $table->json('interaction_data')->nullable();
            $table->json('sentiment_analysis')->nullable();
            $table->json('ai_insights')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();

            // Indexes for analytics
            $table->index(['tenant_id', 'contact_id', 'created_at']);
            $table->index(['tenant_id', 'interaction_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_interactions');
    }
};
