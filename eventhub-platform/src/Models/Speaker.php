<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'bio',
        'job_title',
        'company',
        'avatar_url',
        'twitter_handle',
        'linkedin_url',
        'github_url',
        'website_url',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_speaker')
                    ->withPivot('role', 'sort_order')
                    ->orderByPivot('sort_order');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getTwitterUrlAttribute(): ?string
    {
        if (!$this->twitter_handle) {
            return null;
        }

        $handle = str_replace('@', '', $this->twitter_handle);
        return "https://twitter.com/{$handle}";
    }

    public function getLinkedinUrlAttribute(): ?string
    {
        return $this->linkedin_url;
    }

    public function getGithubUrlAttribute(): ?string
    {
        return $this->github_url;
    }

    public function getWebsiteUrlAttribute(): ?string
    {
        return $this->website_url;
    }

    public function getSocialLinksAttribute(): array
    {
        $links = [];

        if ($this->twitter_url) {
            $links['twitter'] = [
                'url' => $this->twitter_url,
                'label' => 'Twitter',
                'icon' => 'twitter'
            ];
        }

        if ($this->linkedin_url) {
            $links['linkedin'] = [
                'url' => $this->linkedin_url,
                'label' => 'LinkedIn',
                'icon' => 'linkedin'
            ];
        }

        if ($this->github_url) {
            $links['github'] = [
                'url' => $this->github_url,
                'label' => 'GitHub',
                'icon' => 'github'
            ];
        }

        if ($this->website_url) {
            $links['website'] = [
                'url' => $this->website_url,
                'label' => 'Website',
                'icon' => 'globe'
            ];
        }

        return $links;
    }

    public function getFullTitleAttribute(): ?string
    {
        $parts = array_filter([$this->job_title, $this->company]);
        
        if (empty($parts)) {
            return null;
        }

        return implode(' at ', $parts);
    }
}
