<?php

namespace App\Broadcasts;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Poll;

class PollUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $poll;
    public $eventId;
    public $sessionId;

    public function __construct(Poll $poll)
    {
        $this->poll = [
            'id' => $poll->id,
            'title' => $poll->title,
            'description' => $poll->description,
            'poll_type' => $poll->poll_type,
            'is_active' => $poll->is_active,
            'show_results' => $poll->show_results,
            'total_votes' => $poll->total_votes,
            'options' => $poll->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'text' => $option->text,
                    'vote_count' => $option->vote_count,
                    'percentage' => $option->percentage,
                    'sort_order' => $option->sort_order,
                ];
            })->values(),
            'starts_at' => $poll->starts_at?->toIso8601String(),
            'ends_at' => $poll->ends_at?->toIso8601String(),
        ];
        
        $this->eventId = $poll->event_id;
        $this->sessionId = $poll->session_id;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel("events.{$this->eventId}.polls"),
        ];

        if ($this->sessionId) {
            $channels[] = new Channel("events.{$this->eventId}.sessions.{$this->sessionId}.polls");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'poll.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'poll' => $this->poll,
            'event_id' => $this->eventId,
            'session_id' => $this->sessionId,
        ];
    }
}
