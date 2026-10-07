<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DeliverySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_type',
        'title_en',
        'title_ar',
        'start_time',
        'end_time',
        'cutoff_hours_before',
        'max_orders_capacity',
        'extra_charge',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'extra_charge' => 'decimal:2',
        'cutoff_hours_before' => 'integer',
        'max_orders_capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'ar' && !empty($this->title_ar)) ? $this->title_ar : $this->title_en;
    }

    /**
     * Get active slots for a given date taking into account Friday vs Regular days and cutoffs.
     */
    public static function getSlotsForDate(string $date)
    {
        $targetDate = Carbon::parse($date);
        $isFriday = $targetDate->isFriday();
        $isToday = $targetDate->isToday();
        $currentTime = Carbon::now();

        $dayType = $isFriday ? 'friday' : 'regular';

        $slots = static::where('is_active', true)
            ->whereIn('day_type', [$dayType, 'all'])
            ->orderBy('sort_order', 'asc')
            ->get();

        return $slots->map(function ($slot) use ($isToday, $currentTime, $targetDate) {
            $isAvailable = true;
            $cutoffReason = null;

            if ($isToday && $slot->start_time) {
                $slotStart = Carbon::parse($targetDate->toDateString() . ' ' . $slot->start_time);
                $cutoffTime = $slotStart->copy()->subHours($slot->cutoff_hours_before ?? 0);

                if ($currentTime->greaterThanOrEqualTo($cutoffTime)) {
                    $isAvailable = false;
                    $cutoffReason = 'Time slot passed or cutoff reached for today.';
                }
            }

            return [
                'id' => $slot->id,
                'title_en' => $slot->title_en,
                'title_ar' => $slot->title_ar,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'extra_charge' => (float)$slot->extra_charge,
                'day_type' => $slot->day_type,
                'is_available' => $isAvailable,
                'cutoff_reason' => $cutoffReason,
            ];
        });
    }
}
