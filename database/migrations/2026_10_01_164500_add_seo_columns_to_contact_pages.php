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
        Schema::table('contact_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_pages', 'meta_tag_title')) {
                $table->longText('meta_tag_title')->nullable()->after('con_map');
            }
            if (!Schema::hasColumn('contact_pages', 'meta_tag_description')) {
                $table->longText('meta_tag_description')->nullable()->after('meta_tag_title');
            }
            if (!Schema::hasColumn('contact_pages', 'meta_tag_keywords')) {
                $table->longText('meta_tag_keywords')->nullable()->after('meta_tag_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_tag_title', 'meta_tag_description', 'meta_tag_keywords']);
        });
    }
};
