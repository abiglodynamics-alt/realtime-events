<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventDay extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'date',
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the event for the day.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the sessions for the day.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'day_id');
    }

    /**
     * Get the date with a formatted display name.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return $this->date->format('l, F j, Y');
    }

    /**
     * Scope days by date order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('date');
    }

    /**
     * Check if the day is today.
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Check if the day is in the future.
     */
    public function isFuture(): bool
    {
        return $this->date->isFuture();
    }

    /**
     * Check if the day is in the past.
     */
    public function isPast(): bool
    {
        return $this->date->isPast();
    }
}
