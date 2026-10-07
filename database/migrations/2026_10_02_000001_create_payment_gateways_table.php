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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // hyperpay, tabby, tamara, paypal, cod, razorpay
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('icon')->nullable(); // Logo url or icon name
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sandbox')->default(false); // Test vs Live mode
            $table->json('credentials')->nullable(); // JSON object for API keys, entity IDs, tokens
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_order_amount', 10, 2)->nullable();
            $table->decimal('extra_fee', 10, 2)->default(0.00); // COD fee or handling surcharge
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
