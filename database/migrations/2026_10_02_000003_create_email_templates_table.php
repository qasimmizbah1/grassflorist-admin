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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // order_confirmed, order_failed, order_shipped, order_cancelled
            $table->string('name'); // Descriptive name for admin
            $table->string('subject_en');
            $table->string('subject_ar');
            $table->longText('body_en'); // Rich HTML body or markdown with shortcodes
            $table->longText('body_ar'); // Rich HTML body or markdown with shortcodes
            $table->string('recipient_type')->default('customer'); // customer, admin, both
            $table->text('allowed_shortcodes')->nullable(); // Explanatory list of shortcodes for UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
