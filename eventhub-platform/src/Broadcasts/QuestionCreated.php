<?php

namespace App\Broadcasts;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Question;

class QuestionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $question;
    public $eventId;
    public $sessionId;

    public function __construct(Question $question)
    {
        $this->question = [
            'id' => $question->id,
            'content' => $question->content,
            'upvotes' => $question->upvotes,
            'is_answered' => $question->is_answered,
            'user' => [
                'id' => $question->user->id,
                'name' => $question->user->name,
                'avatar_url' => $question->user->avatar_url,
            ],
            'created_at' => $question->created_at->toIso8601String(),
        ];
        
        $this->eventId = $question->event_id;
        $this->sessionId = $question->session_id;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel("events.{$this->eventId}.questions"),
        ];

        if ($this->sessionId) {
            $channels[] = new Channel("events.{$this->eventId}.sessions.{$this->sessionId}.questions");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'question.created';
    }

    public function broadcastWith(): array
    {
        return [
            'question' => $this->question,
            'event_id' => $this->eventId,
            'session_id' => $this->sessionId,
        ];
    }
}
