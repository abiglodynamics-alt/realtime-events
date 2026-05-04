<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'text',
        'vote_count',
        'sort_order',
    ];

    protected $casts = [
        'vote_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function getPercentageAttribute(): float
    {
        $totalVotes = $this->poll->total_votes;

        if ($totalVotes === 0) {
            return 0.0;
        }

        return round(($this->vote_count / $totalVotes) * 100, 1);
    }

    public function incrementVotes(): void
    {
        $this->increment('vote_count');
    }

    public function decrementVotes(): void
    {
        if ($this->vote_count > 0) {
            $this->decrement('vote_count');
        }
    }
}
