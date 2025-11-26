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
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('contact_id')->index();
            $table->string('contact_phone', 20)->index();
            $table->string('thread_id')->unique();
            $table->longText('system_prompt');
            $table->json('conversation_data');
            $table->timestamp('last_activity_at')->index();
            $table->timestamp('expires_at')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('message_count')->default(0);
            $table->integer('total_tokens_used')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index(['tenant_id', 'contact_id', 'is_active']);
            $table->index(['tenant_id', 'is_active', 'last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
