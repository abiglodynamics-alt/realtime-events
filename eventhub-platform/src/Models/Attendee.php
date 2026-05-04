<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'ticket_type',
        'rsvp_status',
        'qr_code',
        'checked_in_at',
        'check_in_count',
        'registration_notes',
        'dietary_requirements',
        'tshirt_size',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'check_in_count' => 'integer',
    ];

    const TICKET_TYPES = [
        'free' => 'Free',
        'vip' => 'VIP',
        'premium' => 'Premium',
        'student' => 'Student',
        'speaker' => 'Speaker',
        'sponsor' => 'Sponsor',
        'staff' => 'Staff',
    ];

    const RSVP_STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'waitlist' => 'Waitlist',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('rsvp_status', $status);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('rsvp_status', 'confirmed');
    }

    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('checked_in_at');
    }

    public function scopeTicketType($query, $type)
    {
        return $query->where('ticket_type', $type);
    }

    public function isConfirmed(): bool
    {
        return $this->rsvp_status === 'confirmed';
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function isOnWaitlist(): bool
    {
        return $this->rsvp_status === 'waitlist';
    }

    public function confirm(): bool
    {
        if ($this->isConfirmed()) {
            return false;
        }

        $this->update(['rsvp_status' => 'confirmed']);
        return true;
    }

    public function cancel(): bool
    {
        if ($this->rsvp_status === 'cancelled') {
            return false;
        }

        $this->update([
            'rsvp_status' => 'cancelled',
            'checked_in_at' => null
        ]);
        return true;
    }

    public function checkIn(): bool
    {
        if (!$this->isConfirmed()) {
            return false;
        }

        $this->increment('check_in_count');
        $this->update(['checked_in_at' => now()]);
        return true;
    }

    public function generateQrCode(): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        
        $data = json_encode([
            'attendee_id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'ticket_type' => $this->ticket_type,
            'timestamp' => $this->created_at->timestamp
        ]);

        $qrCodeSvg = $writer->writeString($data);
        
        // Save to file or storage
        $filename = "qr_codes/attendee_{$this->id}.svg";
        // Storage::put($filename, $qrCodeSvg);
        
        $this->update(['qr_code' => $filename]);
        
        return $qrCodeSvg;
    }

    public function getTicketTypeLabelAttribute(): string
    {
        return self::TICKET_TYPES[$this->ticket_type] ?? ucfirst($this->ticket_type);
    }

    public function getRsvpStatusLabelAttribute(): string
    {
        return self::RSVP_STATUSES[$this->rsvp_status] ?? ucfirst($this->rsvp_status);
    }
}
