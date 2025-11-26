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
        Schema::create('module_validation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('username');
            $table->string('purchase_code'); // Partial code for security
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('validation_response')->nullable();
            $table->timestamps();

            $table->index(['module_name', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_validation_logs');
    }
};
