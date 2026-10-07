<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'icon',
        'is_active',
        'is_sandbox',
        'credentials',
        'min_order_amount',
        'max_order_amount',
        'extra_fee',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'credentials' => 'array',
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'extra_fee' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'ar' && !empty($this->name_ar)) ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return ($locale === 'ar' && !empty($this->description_ar)) ? $this->description_ar : $this->description_en;
    }

    public static function getActiveGateways()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
