<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['categories', 'customers', 'orders', 'order_items', 'products'];

        foreach ($tables as $table) {
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
            } catch (\Exception $e) {
                // Ignore if already auto_increment
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
