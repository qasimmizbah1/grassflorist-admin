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
        // 1. Categories Table Updates
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('categories', 'slug_ar')) {
                $table->string('slug_ar')->nullable()->index()->after('slug');
            }
            if (! Schema::hasColumn('categories', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('description');
            }
        });

        // 2. Customers Table Updates
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('customers', 'username')) {
                $table->string('username')->nullable()->after('source_id');
            }
            if (! Schema::hasColumn('customers', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('country');
            }
            if (! Schema::hasColumn('customers', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('meta_data');
            }
        });

        // 3. Orders Table Updates (Florist & Gift Specific Metadata)
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('SAR')->after('total_amount');
            }
            if (! Schema::hasColumn('orders', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->index()->after('shipping_method');
            }
            if (! Schema::hasColumn('orders', 'delivery_time')) {
                $table->string('delivery_time')->nullable()->after('delivery_date');
            }
            if (! Schema::hasColumn('orders', 'delivery_message')) {
                $table->text('delivery_message')->nullable()->after('delivery_time');
            }
            if (! Schema::hasColumn('orders', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('delivery_message');
            }
            if (! Schema::hasColumn('orders', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('sender_name');
            }
            if (! Schema::hasColumn('orders', 'recipient_phone')) {
                $table->string('recipient_phone')->nullable()->after('recipient_name');
            }
            if (! Schema::hasColumn('orders', 'song_link')) {
                $table->string('song_link', 500)->nullable()->after('recipient_phone');
            }
            if (! Schema::hasColumn('orders', 'location_link')) {
                $table->text('location_link')->nullable()->after('song_link');
            }
            if (! Schema::hasColumn('orders', 'order_language')) {
                $table->string('order_language', 10)->nullable()->after('location_link');
            }
            if (! Schema::hasColumn('orders', 'date_paid')) {
                $table->timestamp('date_paid')->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'ordered_at')) {
                $table->timestamp('ordered_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('orders', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('ordered_at');
            }
        });

        // 4. Order Items Table Updates
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'source_item_id')) {
                $table->unsignedBigInteger('source_item_id')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('order_items', 'source_product_id')) {
                $table->unsignedBigInteger('source_product_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('order_items', 'source_variation_id')) {
                $table->unsignedBigInteger('source_variation_id')->nullable()->after('source_product_id');
            }
            if (! Schema::hasColumn('order_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_name');
            }
            if (! Schema::hasColumn('order_items', 'tax')) {
                $table->decimal('tax', 10, 2)->default(0)->after('unit_price');
            }
            if (! Schema::hasColumn('order_items', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0)->after('tax');
            }
            if (! Schema::hasColumn('order_items', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('total');
            }
        });

        // 5. Products Table Updates
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->index()->after('id');
            }
            if (! Schema::hasColumn('products', 'slug_ar')) {
                $table->string('slug_ar')->nullable()->index()->after('slug');
            }
            if (! Schema::hasColumn('products', 'meta_data')) {
                $table->json('meta_data')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['source_id', 'slug_ar', 'meta_data']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['source_id', 'username', 'meta_data', 'registered_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'source_id',
                'currency',
                'delivery_date',
                'delivery_time',
                'delivery_message',
                'sender_name',
                'recipient_name',
                'recipient_phone',
                'song_link',
                'location_link',
                'order_language',
                'date_paid',
                'ordered_at',
                'meta_data',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'source_item_id',
                'source_product_id',
                'source_variation_id',
                'sku',
                'tax',
                'subtotal',
                'meta_data',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['source_id', 'slug_ar', 'meta_data']);
        });
    }
};
