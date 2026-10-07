<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasTranslations
{
    /**
     * Determine if an attribute is translatable.
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return property_exists($this, 'translatable') && is_array($this->translatable) && in_array($key, $this->translatable);
    }

    /**
     * Convert the model's attributes to an array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (property_exists($this, 'translatable') && is_array($this->translatable)) {
            foreach ($this->translatable as $key) {
                if (array_key_exists($key, $this->attributes)) {
                    $attributes[$key] = $this->getTranslations($key);
                }
            }
        }

        return $attributes;
    }

    /**
     * Get all translations for a given attribute as an associative array.
     */
    public function getTranslations(string $key): array
    {
        $value = $this->attributes[$key] ?? null;

        if (is_null($value)) {
            return [];
        }

        if (is_array($value)) {
            $en = isset($value['en']) && filled($value['en']) ? $value['en'] : null;
            $ar = isset($value['ar']) && filled($value['ar']) ? $value['ar'] : null;

            if (filled($en) && !filled($ar)) {
                $ar = $en;
            } elseif (filled($ar) && !filled($en)) {
                $en = $ar;
            }

            return ['en' => $en, 'ar' => $ar];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $en = isset($decoded['en']) && filled($decoded['en']) ? $decoded['en'] : null;
                $ar = isset($decoded['ar']) && filled($decoded['ar']) ? $decoded['ar'] : null;

                if (filled($en) && !filled($ar)) {
                    $ar = $en;
                } elseif (filled($ar) && !filled($en)) {
                    $en = $ar;
                }

                return ['en' => $en, 'ar' => $ar];
            }

            return ['en' => $value, 'ar' => $value];
        }

        return [];
    }

    /**
     * Get translation for a specific locale.
     */
    public function getTranslation(string $key, ?string $locale = null, bool $useFallback = true): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->getTranslations($key);

        if (empty($translations)) {
            return null;
        }

        if (isset($translations[$locale]) && filled($translations[$locale])) {
            return (string) $translations[$locale];
        }

        if (! $useFallback) {
            return null;
        }

        $fallbackLocale = config('app.fallback_locale', 'en');
        if (isset($translations[$fallbackLocale]) && filled($translations[$fallbackLocale])) {
            return (string) $translations[$fallbackLocale];
        }

        foreach ($translations as $text) {
            if (filled($text)) {
                return (string) $text;
            }
        }

        return null;
    }

    /**
     * Set a single locale translation for an attribute.
     */
    public function setTranslation(string $key, string $locale, mixed $value): self
    {
        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        $en = isset($translations['en']) && filled($translations['en']) ? $translations['en'] : null;
        $ar = isset($translations['ar']) && filled($translations['ar']) ? $translations['ar'] : null;

        if (filled($en) && !filled($ar)) {
            $ar = $en;
        } elseif (filled($ar) && !filled($en)) {
            $en = $ar;
        }

        $this->attributes[$key] = json_encode(['en' => $en, 'ar' => $ar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }

    /**
     * Set multiple translations for an attribute.
     */
    public function setTranslations(string $key, array $translations): self
    {
        $en = isset($translations['en']) && filled($translations['en']) ? $translations['en'] : null;
        $ar = isset($translations['ar']) && filled($translations['ar']) ? $translations['ar'] : null;

        if (filled($en) && !filled($ar)) {
            $ar = $en;
        } elseif (filled($ar) && !filled($en)) {
            $en = $ar;
        }

        $this->attributes[$key] = json_encode(['en' => $en, 'ar' => $ar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }

    /**
     * Override Eloquent's getAttributeValue so Filament and data_get can access array keys like name.en and name.ar.
     */
    public function getAttributeValue($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            return $this->getTranslations($key);
        }

        return parent::getAttributeValue($key);
    }

    /**
     * Override Eloquent's setAttribute to store translatable fields cleanly as JSON with bidirectional fallback.
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute($key)) {
            if (is_array($value)) {
                $en = isset($value['en']) && filled($value['en']) ? $value['en'] : null;
                $ar = isset($value['ar']) && filled($value['ar']) ? $value['ar'] : null;

                if (filled($en) && !filled($ar)) {
                    $ar = $en;
                } elseif (filled($ar) && !filled($en)) {
                    $en = $ar;
                }

                $this->attributes[$key] = json_encode(['en' => $en, 'ar' => $ar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return $this;
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $en = isset($decoded['en']) && filled($decoded['en']) ? $decoded['en'] : null;
                    $ar = isset($decoded['ar']) && filled($decoded['ar']) ? $decoded['ar'] : null;

                    if (filled($en) && !filled($ar)) {
                        $ar = $en;
                    } elseif (filled($ar) && !filled($en)) {
                        $en = $ar;
                    }

                    $this->attributes[$key] = json_encode(['en' => $en, 'ar' => $ar], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    return $this;
                }

                // If a plain string is set, both locales get it
                $this->attributes[$key] = json_encode(['en' => $value, 'ar' => $value], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                return $this;
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Scope to find records by either English or Arabic slug (supporting URL-encoded slugs).
     */
    public function scopeWhereAnySlug(Builder $query, string $slug): Builder
    {
        $decodedSlug = urldecode($slug);

        return $query->where(function (Builder $q) use ($slug, $decodedSlug) {
            $q->where('slug', $slug)
              ->orWhere('slug', $decodedSlug);

            if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'slug_ar')) {
                $q->orWhere('slug_ar', $slug)
                  ->orWhere('slug_ar', $decodedSlug);
            }
        });
    }
}
