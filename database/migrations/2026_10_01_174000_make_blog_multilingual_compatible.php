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
        Schema::table('cms_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_categories', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('cms_categories', 'slug_ar')) {
                $table->string('slug_ar')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('cms_categories', 'meta_tag_title')) {
                $table->longText('meta_tag_title')->nullable()->after('content');
            }
            if (!Schema::hasColumn('cms_categories', 'meta_tag_description')) {
                $table->longText('meta_tag_description')->nullable()->after('meta_tag_title');
            }
            if (!Schema::hasColumn('cms_categories', 'meta_tag_keywords')) {
                $table->longText('meta_tag_keywords')->nullable()->after('meta_tag_description');
            }
            if (!Schema::hasColumn('cms_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('meta_tag_keywords');
            }
        });

        // Modify text/longtext columns for translations on cms_categories
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_categories` MODIFY `name` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_categories` MODIFY `content` LONGTEXT NULL');

        Schema::table('cms_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_posts', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('cms_posts', 'slug_ar')) {
                $table->string('slug_ar')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('cms_posts', 'short_description')) {
                $table->longText('short_description')->nullable()->after('slug_ar');
            }
            if (!Schema::hasColumn('cms_posts', 'banner_image')) {
                $table->string('banner_image')->nullable()->after('image');
            }
            if (!Schema::hasColumn('cms_posts', 'author')) {
                $table->string('author')->nullable()->after('banner_image');
            }
            if (!Schema::hasColumn('cms_posts', 'published_at')) {
                $table->dateTime('published_at')->nullable()->after('author');
            }
            if (!Schema::hasColumn('cms_posts', 'views_count')) {
                $table->unsignedBigInteger('views_count')->default(0)->after('published_at');
            }
        });

        // Modify text/longtext columns for translations on cms_posts
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `title` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `content` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `meta_title` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `meta_description` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `meta_keywords` LONGTEXT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `cms_posts` MODIFY `cms_category_id` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
