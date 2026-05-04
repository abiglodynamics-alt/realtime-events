<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'track_id',
        'day_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'session_type',
        'is_live',
        'live_url',
        'recording_url',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_live' => 'boolean',
    ];

    /**
     * Get the event that owns the session.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the track for the session.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the day for the session.
     */
    public function day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class, 'day_id');
    }

    /**
     * Get the speakers for the session.
     */
    public function speakers()
    {
        return $this->belongsToMany(Speaker::class, 'session_speaker')
            ->withPivot('is_moderator', 'sort_order')
            ->orderBy('session_speaker.sort_order');
    }

    /**
     * Get the questions for the session.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the polls for the session.
     */
    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    /**
     * Check if the session is currently happening.
     */
    public function isLiveNow(): bool
    {
        return $this->is_live && now()->between($this->start_time, $this->end_time);
    }

    /**
     * Get the duration in minutes.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Scope sessions by session type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('session_type', $type);
    }

    /**
     * Scope to only live sessions.
     */
    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }
}
