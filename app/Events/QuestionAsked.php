<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionAsked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;

    /**
     * Create a new event instance.
     */
    public function __construct(Question $question)
    {
        $this->question = $question->load(['attendee.user', 'session']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('event.' . $this->question->event_id),
            new PrivateChannel('session.' . $this->question->session_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'question.asked';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'question' => [
                'id' => $this->question->id,
                'question_text' => $this->question->question_text,
                'upvotes' => $this->question->upvotes,
                'is_answered' => $this->question->is_answered,
                'asked_at' => $this->question->created_at->toIso8601String(),
                'attendee' => [
                    'name' => $this->question->attendee->user->name ?? 'Anonymous',
                ],
                'session' => [
                    'id' => $this->question->session_id,
                    'title' => $this->question->session?->title,
                ],
            ],
        ];
    }
}
