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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('updated_by', 264)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('updated_by', 265)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('updated_by', 264)->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('updated_by', 265)->nullable(false)->change();
        });
    }
};
