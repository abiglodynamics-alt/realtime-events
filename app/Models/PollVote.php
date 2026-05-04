<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'poll_id',
        'option_id',
        'attendee_id',
    ];

    /**
     * Get the poll for the vote.
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the option for the vote.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class);
    }

    /**
     * Get the attendee who voted.
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    /**
     * Prevent duplicate votes from same attendee on same poll.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($vote) {
            // Check if attendee already voted on this poll
            $existingVote = static::where('poll_id', $vote->poll_id)
                ->where('attendee_id', $vote->attendee_id)
                ->first();

            if ($existingVote) {
                throw new \Exception('Attendee has already voted on this poll');
            }
        });
    }
}
