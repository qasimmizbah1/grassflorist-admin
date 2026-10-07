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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('zip_code', 30)->nullable()->change();
            $table->string('city', 30)->nullable()->change();
            $table->string('state', 30)->nullable()->change();
            $table->string('country', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('zip_code', 30)->nullable(false)->change();
            $table->string('city', 30)->nullable(false)->change();
            $table->string('state', 30)->nullable(false)->change();
            $table->string('country', 20)->nullable(false)->change();
        });
    }
};
