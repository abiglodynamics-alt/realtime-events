<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Attendee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_id',
        'rsvp_status',
        'qr_code',
        'checked_in',
        'checked_in_at',
        'ticket_type',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    const RSVP_PENDING = 'pending';
    const RSVP_CONFIRMED = 'confirmed';
    const RSVP_CANCELLED = 'cancelled';
    const RSVP_WAITLIST = 'waitlist';

    /**
     * Get the user for the attendee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event for the attendee.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the questions asked by the attendee.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the poll votes by the attendee.
     */
    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Generate QR code for check-in.
     */
    public function generateQrCode(): string
    {
        $uniqueCode = $this->id . '-' . $this->user_id . '-' . $this->event_id . '-' . uniqid();
        
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new ImagickImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeData = $writer->writeString($uniqueCode);
        
        // Save to storage and return path
        $filename = 'qr_codes/attendee_' . $this->id . '.png';
        \Storage::put($filename, $qrCodeData);
        
        $this->update(['qr_code' => $filename]);
        
        return $filename;
    }

    /**
     * Check in the attendee.
     */
    public function checkIn(): bool
    {
        if ($this->rsvp_status !== self::RSVP_CONFIRMED) {
            return false;
        }

        $this->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);

        return true;
    }

    /**
     * Confirm the attendee's RSVP.
     */
    public function confirm(): void
    {
        $this->update(['rsvp_status' => self::RSVP_CONFIRMED]);
        
        // Generate QR code upon confirmation
        if (!$this->qr_code) {
            $this->generateQrCode();
        }
    }

    /**
     * Cancel the attendee's RSVP.
     */
    public function cancel(): void
    {
        $this->update(['rsvp_status' => self::RSVP_CANCELLED]);
    }

    /**
     * Scope to confirmed attendees.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('rsvp_status', self::RSVP_CONFIRMED);
    }

    /**
     * Scope to checked in attendees.
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('checked_in', true);
    }

    /**
     * Check if attendee is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->rsvp_status === self::RSVP_CONFIRMED;
    }

    /**
     * Check if attendee has checked in.
     */
    public function hasCheckedIn(): bool
    {
        return $this->checked_in;
    }
}
