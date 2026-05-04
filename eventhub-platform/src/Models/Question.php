<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'session_id',
        'user_id',
        'content',
        'upvotes',
        'is_answered',
        'is_hidden',
        'answer',
        'answered_by',
        'answered_at',
    ];

    protected $casts = [
        'upvotes' => 'integer',
        'is_answered' => 'boolean',
        'is_hidden' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(QuestionVote::class);
    }

    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeAnswered($query)
    {
        return $query->where('is_answered', true);
    }

    public function scopeUnanswered($query)
    {
        return $query->where('is_answered', false);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('upvotes', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function upvote(User $user): bool
    {
        if ($this->votes()->where('user_id', $user->id)->exists()) {
            return false;
        }

        $this->votes()->create(['user_id' => $user->id]);
        $this->increment('upvotes');
        
        return true;
    }

    public function removeUpvote(User $user): bool
    {
        $vote = $this->votes()->where('user_id', $user->id)->first();
        
        if (!$vote) {
            return false;
        }

        $vote->delete();
        $this->decrement('upvotes');
        
        return true;
    }

    public function hasUpvoted(User $user): bool
    {
        return $this->votes()->where('user_id', $user->id)->exists();
    }

    public function markAsAnswered(?User $answerer = null): bool
    {
        if ($this->is_answered) {
            return false;
        }

        $this->update([
            'is_answered' => true,
            'answered_by' => $answerer?->id,
            'answered_at' => now()
        ]);

        return true;
    }

    public function hide(): bool
    {
        if ($this->is_hidden) {
            return false;
        }

        $this->update(['is_hidden' => true]);
        return true;
    }

    public function show(): bool
    {
        if (!$this->is_hidden) {
            return false;
        }

        $this->update(['is_hidden' => false]);
        return true;
    }

    public function isVisible(): bool
    {
        return !$this->is_hidden;
    }
}
