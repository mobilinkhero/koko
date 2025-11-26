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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }

            if (! Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->after('id');
            }

            if (! Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->after('firstname');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id');
            }

            if (Schema::hasColumn('users', 'firstname')) {
                $table->dropColumn('firstname');
                $table->dropColumn('lastname');
            }
        });
    }
};
