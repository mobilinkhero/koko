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
        Schema::table('personal_assistants', function (Blueprint $table) {
            // Remove the unique constraint on tenant_id to allow multiple assistants per tenant
            $table->dropUnique(['tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_assistants', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique('tenant_id');
        });
    }
};
