<?php

namespace App\Broadcasts;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;

class EventStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'starts_at' => $event->starts_at->toIso8601String(),
            'location' => $event->location,
            'is_live' => true,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("events.{$this->event['id']}.status"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'event.started';
    }

    public function broadcastWith(): array
    {
        return [
            'event' => $this->event,
        ];
    }
}
