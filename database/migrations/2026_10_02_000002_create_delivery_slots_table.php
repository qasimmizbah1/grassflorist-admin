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
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->enum('day_type', ['regular', 'friday', 'all'])->default('regular'); // regular = Sat-Thu, friday = Fri, all = Every day
            $table->string('title_en'); // e.g. "11:00 AM to 03:00 PM"
            $table->string('title_ar'); // e.g. "من 11 صباحاً وحتى 3 مساءً"
            $table->time('start_time')->nullable(); // e.g. 11:00:00
            $table->time('end_time')->nullable();   // e.g. 15:00:00
            $table->integer('cutoff_hours_before')->default(0); // Cutoff in hours before slot starts
            $table->integer('max_orders_capacity')->nullable(); // Max orders allowed in this slot (null = unlimited)
            $table->decimal('extra_charge', 10, 2)->default(0.00); // Express delivery fee if any
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_slots');
    }
};
