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
        Schema::table('ecommerce_configurations', function (Blueprint $table) {
            // AI Configuration
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_powered_mode')) {
                $table->boolean('ai_powered_mode')->default(false);
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'openai_api_key')) {
                $table->text('openai_api_key')->nullable();
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'openai_model')) {
                $table->string('openai_model', 50)->default('gpt-3.5-turbo');
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_temperature')) {
                $table->decimal('ai_temperature', 2, 1)->default(0.7);
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_max_tokens')) {
                $table->integer('ai_max_tokens')->default(500);
            }
            
            // AI Behavior Settings
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_system_prompt')) {
                $table->text('ai_system_prompt')->nullable();
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_product_context')) {
                $table->text('ai_product_context')->nullable();
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'ai_response_templates')) {
                $table->json('ai_response_templates')->nullable();
            }
            
            // Direct Sheets Integration
            if (!Schema::hasColumn('ecommerce_configurations', 'direct_sheets_integration')) {
                $table->boolean('direct_sheets_integration')->default(false);
            }
            if (!Schema::hasColumn('ecommerce_configurations', 'bypass_local_database')) {
                $table->boolean('bypass_local_database')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_configurations', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('ecommerce_configurations', 'ai_powered_mode')) {
                $columnsToDrop[] = 'ai_powered_mode';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'openai_api_key')) {
                $columnsToDrop[] = 'openai_api_key';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'openai_model')) {
                $columnsToDrop[] = 'openai_model';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'ai_temperature')) {
                $columnsToDrop[] = 'ai_temperature';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'ai_max_tokens')) {
                $columnsToDrop[] = 'ai_max_tokens';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'ai_system_prompt')) {
                $columnsToDrop[] = 'ai_system_prompt';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'ai_product_context')) {
                $columnsToDrop[] = 'ai_product_context';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'ai_response_templates')) {
                $columnsToDrop[] = 'ai_response_templates';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'direct_sheets_integration')) {
                $columnsToDrop[] = 'direct_sheets_integration';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'bypass_local_database')) {
                $columnsToDrop[] = 'bypass_local_database';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
