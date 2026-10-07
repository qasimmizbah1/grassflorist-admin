<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'con_title',
        'con_address',
        'con_phone',
        'con_email',
        'con_map',
        'meta_tag_title',
        'meta_tag_description',
        'meta_tag_keywords',
        'updated_at',
        'updated_by',
    ];

    protected array $translatable = [
        'con_title',
        'con_address',
        'meta_tag_title',
        'meta_tag_description',
        'meta_tag_keywords',
    ];

    protected static function booted(): void
    {
        static::updating(function ($home) {
            if (auth()->check()) {
                $home->updated_by = auth()->id();
            }
        });
        static::creating(function ($post) {
            $post->updated_by = auth()->id();
        });
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
