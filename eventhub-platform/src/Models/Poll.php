<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'session_id',
        'title',
        'description',
        'poll_type',
        'is_active',
        'show_results',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', Carbon::now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                          ->orWhere('ends_at', '>=', Carbon::now());
                    });
    }

    public function scopeScheduled($query)
    {
        return $query->where('starts_at', '>', Carbon::now());
    }

    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->isAfter($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function canShowResults(User $user): bool
    {
        if ($this->show_results === 'always') {
            return true;
        }

        if ($this->show_results === 'after_vote') {
            return $this->votes()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function allowMultipleVotes(): bool
    {
        return $this->poll_type === 'multiple';
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->options()->sum('vote_count');
    }

    public function activate(): bool
    {
        if ($this->is_active) {
            return false;
        }

        $this->update(['is_active' => true]);
        return true;
    }

    public function deactivate(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $this->update(['is_active' => false]);
        return true;
    }
}
