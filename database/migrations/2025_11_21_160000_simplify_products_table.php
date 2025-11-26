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
        Schema::table('products', function (Blueprint $table) {
            // Drop unnecessary columns - everything is in meta_data now!
            $table->dropColumn([
                'google_sheet_row_id',
                'description',
                'sale_price',
                'cost_price',
                'low_stock_threshold',
                'category',
                'subcategory',
                'tags',
                'images',
                'weight',
                'dimensions',
                'featured',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore columns if needed
            $table->integer('google_sheet_row_id')->nullable()->after('tenant_id');
            $table->text('description')->nullable()->after('name');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->decimal('cost_price', 10, 2)->nullable()->after('sale_price');
            $table->integer('low_stock_threshold')->default(5)->after('stock_quantity');
            $table->string('category')->nullable()->after('low_stock_threshold');
            $table->string('subcategory')->nullable()->after('category');
            $table->json('tags')->nullable()->after('subcategory');
            $table->json('images')->nullable()->after('tags');
            $table->decimal('weight', 8, 2)->nullable()->after('images');
            $table->json('dimensions')->nullable()->after('weight');
            $table->boolean('featured')->default(false)->after('status');
        });
    }
};
