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
            $table->timestamp('last_synced_at')->nullable()->after('use_case_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_assistants', function (Blueprint $table) {
            $table->dropColumn('last_synced_at');
        });
    }
};
