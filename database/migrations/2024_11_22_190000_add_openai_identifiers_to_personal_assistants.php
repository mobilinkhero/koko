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
            $table->string('openai_assistant_id')->nullable()->after('last_synced_at');
            $table->string('openai_vector_store_id')->nullable()->after('openai_assistant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_assistants', function (Blueprint $table) {
            $table->dropColumn(['openai_assistant_id', 'openai_vector_store_id']);
        });
    }
};
