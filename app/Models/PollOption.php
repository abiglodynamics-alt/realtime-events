<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'poll_id',
        'option_text',
        'vote_count',
        'sort_order',
    ];

    /**
     * Get the poll for the option.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the votes for the option.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Add a vote to this option.
     */
    public function addVote(): void
    {
        $this->increment('vote_count');
    }

    /**
     * Remove a vote from this option.
     */
    public function removeVote(): void
    {
        if ($this->vote_count > 0) {
            $this->decrement('vote_count');
        }
    }

    /**
     * Get the percentage of total votes.
     */
    public function getPercentageAttribute(): float
    {
        $totalVotes = $this->poll->total_votes;

        if ($totalVotes === 0) {
            return 0;
        }

        return round(($this->vote_count / $totalVotes) * 100, 2);
    }

    /**
     * Scope options by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
