<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speaker extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'profile_image',
        'bio',
        'job_title',
        'company',
        'twitter',
        'linkedin',
        'github',
        'website',
    ];

    /**
     * Get the user associated with the speaker.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sessions for the speaker.
     */
    public function sessions()
    {
        return $this->belongsToMany(Session::class, 'session_speaker')
            ->withPivot('is_moderator', 'sort_order')
            ->orderBy('session_speaker.sort_order');
    }

    /**
     * Get the social links as an array.
     */
    public function getSocialLinksAttribute(): array
    {
        $links = [];

        if ($this->twitter) {
            $links['twitter'] = $this->twitter;
        }

        if ($this->linkedin) {
            $links['linkedin'] = $this->linkedin;
        }

        if ($this->github) {
            $links['github'] = $this->github;
        }

        if ($this->website) {
            $links['website'] = $this->website;
        }

        return $links;
    }

    /**
     * Scope to speakers with a specific company.
     */
    public function scopeFromCompany($query, string $company)
    {
        return $query->where('company', $company);
    }
}
