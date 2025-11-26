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
        Schema::create('personal_assistants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('system_instructions');
            $table->string('model')->default('gpt-4o-mini');
            $table->decimal('temperature', 2, 1)->default(0.7);
            $table->integer('max_tokens')->default(1000);
            $table->boolean('file_analysis_enabled')->default(true);
            $table->json('uploaded_files')->nullable(); // Store file paths and metadata
            $table->text('processed_content')->nullable(); // Processed file content for context
            $table->boolean('is_active')->default(true);
            $table->json('use_case_tags')->nullable(); // FAQ, Product, Onboarding, CSV, SOP
            $table->timestamps();

            // Ensure one assistant per tenant
            $table->unique('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_assistants');
    }
};
