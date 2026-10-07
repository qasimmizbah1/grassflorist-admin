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
        Schema::table('global_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('global_settings', 'google_analytics_id')) {
                $table->string('google_analytics_id')->nullable()->after('site_favicon');
            }
            if (!Schema::hasColumn('global_settings', 'google_tag_manager_id')) {
                $table->string('google_tag_manager_id')->nullable()->after('google_analytics_id');
            }
            if (!Schema::hasColumn('global_settings', 'meta_pixel_id')) {
                $table->string('meta_pixel_id')->nullable()->after('google_tag_manager_id');
            }
            if (!Schema::hasColumn('global_settings', 'tiktok_pixel_id')) {
                $table->string('tiktok_pixel_id')->nullable()->after('meta_pixel_id');
            }
            if (!Schema::hasColumn('global_settings', 'snapchat_pixel_id')) {
                $table->string('snapchat_pixel_id')->nullable()->after('tiktok_pixel_id');
            }
            if (!Schema::hasColumn('global_settings', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->default(15.00)->after('snapchat_pixel_id');
            }
            if (!Schema::hasColumn('global_settings', 'vat_registration_number')) {
                $table->string('vat_registration_number')->nullable()->after('vat_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_analytics_id',
                'google_tag_manager_id',
                'meta_pixel_id',
                'tiktok_pixel_id',
                'snapchat_pixel_id',
                'vat_percentage',
                'vat_registration_number'
            ]);
        });
    }
};
