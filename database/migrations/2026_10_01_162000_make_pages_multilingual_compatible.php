<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. cms_pages
        Schema::table('cms_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_pages', 'slug_ar')) {
                $table->string('slug_ar')->nullable()->after('slug');
            }
            $table->text('title')->change();
            $table->longText('short_description')->nullable()->change();
            $table->longText('content')->nullable()->change();
            $table->longText('banner_images')->nullable()->change();
            $table->string('updated_by')->nullable()->change();
        });

        // 2. contact_pages
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->text('con_title')->nullable()->change();
            $table->longText('con_address')->nullable()->change();
            $table->string('con_phone')->nullable()->change();
            $table->string('con_email')->nullable()->change();
            $table->string('updated_by')->nullable()->change();
        });

        // 3. home_pages
        Schema::table('home_pages', function (Blueprint $table) {
            $table->text('page_title')->nullable()->change();
            $table->text('popular_title')->nullable()->change();
            $table->text('popular_subtitle')->nullable()->change();
            $table->text('popular_category')->nullable()->change();
            $table->text('mock_subtitle')->nullable()->change();
            $table->text('mock_test_category')->nullable()->change();
            $table->text('hobby_subtitle')->nullable()->change();
            $table->text('hobby_category')->nullable()->change();
            $table->text('publications_subtitle')->nullable()->change();
            $table->text('publication')->nullable()->change();
            $table->text('slider_section')->nullable()->change();
            $table->text('mslider_section')->nullable()->change();
            $table->text('featured_products_title')->nullable()->change();
            $table->text('best_sellers_title')->nullable()->change();
            $table->text('best_sellers_subtitle')->nullable()->change();
            $table->string('updated_by')->nullable()->change();
        });
    }

    public function down(): void
    {
    }
};
