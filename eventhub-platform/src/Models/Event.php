<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'description',
        'short_description',
        'image_url',
        'banner_url',
        'location_name',
        'address',
        'city',
        'country',
        'timezone',
        'starts_at',
        'ends_at',
        'registration_starts_at',
        'registration_ends_at',
        'max_attendees',
        'is_published',
        'is_featured',
        'allow_rsvp',
        'rsvp_deadline',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'registration_starts_at' => 'datetime',
        'registration_ends_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'allow_rsvp' => 'boolean',
        'max_attendees' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });

        static::updating(function ($event) {
            if ($event->isDirty('title') && empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->title);
            }
        });
    }

    private static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = 1;
        $originalSlug = $slug;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(EventDay::class)->orderBy('sort_order');
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('sort_order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', Carbon::now());
    }

    public function scopeActive($query)
    {
        return $query->where('starts_at', '<=', Carbon::now())
                    ->where('ends_at', '>=', Carbon::now());
    }

    public function scopeWithRegistrationOpen($query)
    {
        return $query->where('allow_rsvp', true)
                    ->where(function ($q) {
                        $q->whereNull('registration_ends_at')
                          ->orWhere('registration_ends_at', '>', Carbon::now());
                    });
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at->isFuture();
    }

    public function isActive(): bool
    {
        return Carbon::now()->between($this->starts_at, $this->ends_at);
    }

    public function isPast(): bool
    {
        return $this->ends_at->isPast();
    }

    public function registrationIsOpen(): bool
    {
        if (!$this->allow_rsvp) {
            return false;
        }

        $now = Carbon::now();

        if ($this->registration_starts_at && $now->isBefore($this->registration_starts_at)) {
            return false;
        }

        if ($this->registration_ends_at && $now->isAfter($this->registration_ends_at)) {
            return false;
        }

        return true;
    }

    public function isFull(): bool
    {
        if (!$this->max_attendees) {
            return false;
        }

        return $this->attendees()->where('rsvp_status', 'confirmed')->count() >= $this->max_attendees;
    }

    public function getDurationAttribute(): string
    {
        return $this->starts_at->diffForHumans($this->ends_at, ['parts' => 2]);
    }

    public function getLocationAttribute(): string
    {
        $parts = array_filter([
            $this->location_name,
            $this->city,
            $this->country
        ]);

        return implode(', ', $parts) ?? 'Online';
    }
}
