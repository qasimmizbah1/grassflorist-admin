<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CmsPost extends Model
{
    use HasFactory, \App\Traits\HasTranslations;

    protected $fillable = [
        'source_id',
        'cms_category_id',
        'title',
        'slug',
        'slug_ar',
        'short_description',
        'content',
        'image',
        'banner_image',
        'author',
        'published_at',
        'views_count',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'updated_at',
        'updated_by',
    ];

    protected array $translatable = [
        'title',
        'short_description',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(CmsCategory::class, 'cms_category_id');
    }

    protected static function booted(): void
    {
        static::updating(function ($post) {
            if (auth()->check()) {
                $post->updated_by = auth()->id();
            }
        });
        static::creating(function ($post) {
            if (auth()->check()) {
                $post->updated_by = auth()->id();
            }
            if (empty($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
