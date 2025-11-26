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
            // Customer details settings
            if (!Schema::hasColumn('ecommerce_configurations', 'required_customer_fields')) {
                $table->json('required_customer_fields')->nullable(); // ['name', 'address', 'city', 'phone', 'email', 'notes']
            }
            
            // Payment methods settings
            if (!Schema::hasColumn('ecommerce_configurations', 'enabled_payment_methods')) {
                $table->json('enabled_payment_methods')->nullable(); // ['cod', 'bank_transfer', 'card', 'online']
            }
            
            if (!Schema::hasColumn('ecommerce_configurations', 'payment_method_responses')) {
                $table->json('payment_method_responses')->nullable(); // Custom responses for each method
            }
            
            // Default settings
            if (!Schema::hasColumn('ecommerce_configurations', 'collect_customer_details')) {
                $table->boolean('collect_customer_details')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_configurations', function (Blueprint $table) {
            $columnsToRemove = [];
            
            if (Schema::hasColumn('ecommerce_configurations', 'required_customer_fields')) {
                $columnsToRemove[] = 'required_customer_fields';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'enabled_payment_methods')) {
                $columnsToRemove[] = 'enabled_payment_methods';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'payment_method_responses')) {
                $columnsToRemove[] = 'payment_method_responses';
            }
            if (Schema::hasColumn('ecommerce_configurations', 'collect_customer_details')) {
                $columnsToRemove[] = 'collect_customer_details';
            }
            
            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
