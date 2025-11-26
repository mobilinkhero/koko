<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TemplateFieldSeerder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Schema::hasTable('whatsapp_templates')) {
            Schema::table('whatsapp_templates', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_templates', 'template_id')) {
                    $table->unsignedBigInteger('template_id')->nullable()->change();
                }

                if (! Schema::hasColumn('whatsapp_templates', 'header_file_url')) {
                    $table->text('header_file_url')->nullable();
                }

                if (! Schema::hasColumn('whatsapp_templates', 'header_variable_value')) {
                    $table->json('header_variable_value')->nullable();
                }

                if (! Schema::hasColumn('whatsapp_templates', 'body_variable_value')) {
                    $table->json('body_variable_value')->nullable();
                }

            });
        }
    }
}
