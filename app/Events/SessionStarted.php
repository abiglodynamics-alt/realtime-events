<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct(Session $session)
    {
        $this->session = $session->load(['speakers', 'track']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('event.' . $this->session->event_id),
            new PrivateChannel('session.' . $this->session->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'session.started';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'session' => [
                'id' => $this->session->id,
                'title' => $this->session->title,
                'description' => $this->session->description,
                'start_time' => $this->session->start_time->toIso8601String(),
                'end_time' => $this->session->end_time->toIso8601String(),
                'session_type' => $this->session->session_type,
                'is_live' => $this->session->is_live,
                'live_url' => $this->session->live_url,
                'speakers' => $this->session->speakers->map(fn($speaker) => [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'profile_image' => $speaker->profile_image,
                    'job_title' => $speaker->job_title,
                    'company' => $speaker->company,
                ]),
            ],
        ];
    }
}
