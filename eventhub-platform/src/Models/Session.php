<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'day_id',
        'track_id',
        'title',
        'description',
        'session_type',
        'starts_at',
        'ends_at',
        'room',
        'stream_url',
        'recording_url',
        'slides_url',
        'capacity',
        'is_live',
        'is_break',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_live' => 'boolean',
        'is_break' => 'boolean',
        'capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(EventDay::class, 'day_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class, 'session_speaker')
                    ->withPivot('role', 'sort_order')
                    ->orderByPivot('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('session_type', $type);
    }

    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('starts_at', '>', Carbon::now());
    }

    public function scopeInProgress($query)
    {
        return $query->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>=', Carbon::now());
    }

    public function isLive(): bool
    {
        return $this->is_live && Carbon::now()->between($this->starts_at, $this->ends_at);
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at->isFuture();
    }

    public function isPast(): bool
    {
        return $this->ends_at->isPast();
    }

    public function getDurationAttribute(): string
    {
        return $this->starts_at->diffForHumans($this->ends_at, ['parts' => 1]);
    }

    public function getTypeLabelAttribute(): string
    {
        return ucfirst($this->session_type);
    }
}
