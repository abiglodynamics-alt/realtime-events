<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'bio',
        'phone',
        'company',
        'job_title',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function questionVotes(): HasMany
    {
        return $this->hasMany(QuestionVote::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function speakerProfile(): HasOne
    {
        return $this->hasOne(Speaker::class);
    }

    public function isOrganizer(): bool
    {
        return $this->organizedEvents()->count() > 0;
    }

    public function isAttending(Event $event): bool
    {
        return $this->attendees()
                    ->where('event_id', $event->id)
                    ->where('rsvp_status', 'confirmed')
                    ->exists();
    }

    public function getAttendeeForEvent(Event $event): ?Attendee
    {
        return $this->attendees()->where('event_id', $event->id)->first();
    }
}
