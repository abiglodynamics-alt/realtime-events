<?php

namespace App\Events;

use App\Models\Poll;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PollResultsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $poll;

    /**
     * Create a new event instance.
     */
    public function __construct(Poll $poll)
    {
        $this->poll = $poll->load(['options']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('event.' . $this->poll->event_id),
            new PrivateChannel('session.' . $this->poll->session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'poll.results.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'poll' => [
                'id' => $this->poll->id,
                'question' => $this->poll->question,
                'total_votes' => $this->poll->total_votes,
                'show_results' => $this->poll->show_results,
                'is_active' => $this->poll->is_active,
                'options' => $this->poll->options->map(fn($option) => [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'vote_count' => $option->vote_count,
                    'percentage' => $option->percentage,
                ]),
            ],
        ];
    }
}
