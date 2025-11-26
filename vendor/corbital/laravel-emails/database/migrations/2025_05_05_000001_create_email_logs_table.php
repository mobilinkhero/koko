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
        if (Schema::hasTable('email_templates') && ! Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->string('subject');
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->string('cc')->nullable();
                $table->string('bcc')->nullable();
                $table->string('reply_to')->nullable();
                $table->json('data')->nullable();
                $table->enum('status', ['pending', 'sent', 'failed', 'scheduled'])->default('pending');
                $table->text('error')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
