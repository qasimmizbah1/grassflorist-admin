<?php

if (! function_exists('currency_symbol')) {
    function currency_symbol(?string $currency = null): string {
        $curr = $currency ?: currency_code();
        return match (strtoupper($curr)) {
            'SAR', 'SR' => 'SAR ',
            'AED' => 'AED ',
            'USD' => '$',
            'EUR' => '€',
            'INR' => '₹',
            default => config('app.currency_symbol', 'SAR '),
        };
    }
}

if (! function_exists('currency_code')) {
    function currency_code(): string {
        return config('app.currency', 'SAR');
    }
}

if (! function_exists('format_currency')) {
    function format_currency($amount, int $decimals = 2, ?string $currency = null): string {
        return currency_symbol($currency) . number_format((float) ($amount ?? 0), $decimals);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string {
        return app()->getLocale() ?: config('app.locale', 'en');
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(?string $locale = null): bool {
        $loc = $locale ?: current_locale();
        return in_array(strtolower($loc), ['ar', 'fa', 'ur', 'he']);
    }
}

if (! function_exists('format_translatable')) {
    /**
     * Extract string for the requested/active locale from array, JSON string, or legacy plain string.
     */
    function format_translatable(mixed $value, ?string $locale = null): ?string {
        if (is_null($value)) {
            return null;
        }

        $targetLocale = $locale ?: current_locale();
        $fallbackLocale = config('app.fallback_locale', 'en');

        if (is_array($value)) {
            if (isset($value[$targetLocale]) && filled($value[$targetLocale])) {
                return (string) $value[$targetLocale];
            }
            if (isset($value[$fallbackLocale]) && filled($value[$fallbackLocale])) {
                return (string) $value[$fallbackLocale];
            }
            foreach ($value as $item) {
                if (filled($item) && is_scalar($item)) {
                    return (string) $item;
                }
            }
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return format_translatable($decoded, $targetLocale);
            }

            return $value;
        }

        return (string) $value;
    }
}
