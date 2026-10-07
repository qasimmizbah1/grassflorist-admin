<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CmsCategory extends Model
{
    use HasFactory, \App\Traits\HasTranslations;

    protected $fillable = [
        'source_id',
        'name',
        'slug',
        'slug_ar',
        'content',
        'meta_tag_title',
        'meta_tag_description',
        'meta_tag_keywords',
        'is_active',
        'updated_at',
        'updated_by',
    ];

    protected array $translatable = [
        'name',
        'content',
        'meta_tag_title',
        'meta_tag_description',
        'meta_tag_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function posts()
    {
        return $this->hasMany(CmsPost::class);
    }

    protected static function booted(): void
    {
        static::updating(function ($cat) {
            if (auth()->check()) {
                $cat->updated_by = auth()->id();
            }
        });
        static::creating(function ($cat) {
            $cat->updated_by = auth()->id();
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
