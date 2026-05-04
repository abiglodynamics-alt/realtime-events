<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'event_id',
        'attendee_id',
        'question_text',
        'upvotes',
        'is_answered',
        'answer_text',
        'answered_by',
        'answered_at',
        'is_hidden',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_answered' => 'boolean',
        'is_hidden' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the session for the question.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the event for the question.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the attendee who asked the question.
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    /**
     * Get the user who answered the question.
     */
    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    /**
     * Upvote the question.
     */
    public function upvote(): int
    {
        $this->increment('upvotes');
        return $this->upvotes;
    }

    /**
     * Downvote the question (remove an upvote).
     */
    public function downvote(): int
    {
        if ($this->upvotes > 0) {
            $this->decrement('upvotes');
        }
        return $this->upvotes;
    }

    /**
     * Mark the question as answered.
     */
    public function markAsAnswered(string $answerText, int $answeredBy): void
    {
        $this->update([
            'is_answered' => true,
            'answer_text' => $answerText,
            'answered_by' => $answeredBy,
            'answered_at' => now(),
        ]);
    }

    /**
     * Hide the question.
     */
    public function hide(): void
    {
        $this->update(['is_hidden' => true]);
    }

    /**
     * Show the question.
     */
    public function show(): void
    {
        $this->update(['is_hidden' => false]);
    }

    /**
     * Scope to visible questions.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope to unanswered questions.
     */
    public function scopeUnanswered($query)
    {
        return $query->where('is_answered', false);
    }

    /**
     * Scope to top questions by upvotes.
     */
    public function scopeTopRated($query, int $limit = 10)
    {
        return $query->orderBy('upvotes', 'desc')->limit($limit);
    }

    /**
     * Scope questions by session.
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
