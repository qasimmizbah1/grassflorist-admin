<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'source_item_id',
        'product_id',
        'source_product_id',
        'source_variation_id',
        'product_name',
        'product_image',
        'sku',
        'quantity',
        'unit_price',
        'price',
        'tax',
        'subtotal',
        'total',
        'payment_method',
        'product_weight',
        'meta_data',
        'vendor_id'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'unit_price' => 'decimal:2',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function vendor()
    {
    return $this->belongsTo(Vendor::class);
    }
   
}
