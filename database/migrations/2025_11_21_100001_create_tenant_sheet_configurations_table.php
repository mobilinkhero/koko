<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores dynamic column mappings for each tenant's Google Sheets
     * allowing universal structure support without fixed schema requirements.
     */
    public function up(): void
    {
        Schema::create('tenant_sheet_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            
            // Sheet metadata
            $table->string('sheet_type')->default('products'); // products, orders, customers
            $table->string('sheet_name')->nullable(); // The actual tab name in Google Sheets
            $table->string('sheet_id')->nullable(); // Google Sheets ID
            
            // Dynamic column mapping (Google Sheet Column → Database Field)
            // Format: {"Sheet Column": "database_field", "Custom Col": "custom_field_name"}
            $table->json('column_mapping')->nullable();
            
            // Detected columns from sheet (auto-discovered on first sync)
            // Format: ["Column1", "Column2", "Column3"]
            $table->json('detected_columns')->nullable();
            
            // Core field requirements (which columns are mandatory)
            // Format: {"name": "Product Name", "price": "Price", "sku": "SKU"}
            $table->json('required_field_mapping')->nullable();
            
            // Custom fields configuration (for extra columns)
            // Format: {"custom_color": {"type": "string", "label": "Color"}}
            $table->json('custom_fields_config')->nullable();
            
            // Data types for custom fields
            // Format: {"custom_size": "string", "custom_rating": "number"}
            $table->json('column_types')->nullable();
            
            // Sync settings
            $table->boolean('auto_detect_columns')->default(true);
            $table->boolean('allow_custom_fields')->default(true);
            $table->boolean('strict_mode')->default(false); // If true, reject unknown columns
            
            // Status tracking
            $table->string('detection_status')->default('pending'); // pending, detected, configured
            $table->integer('total_columns_detected')->default(0);
            $table->integer('mapped_columns_count')->default(0);
            $table->timestamp('last_detection_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'sheet_type']);
            $table->unique(['tenant_id', 'sheet_type']); // One config per tenant per sheet type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_sheet_configurations');
    }
};
