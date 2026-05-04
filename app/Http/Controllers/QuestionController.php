<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Session;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\QuestionAsked;

class QuestionController extends Controller
{
    /**
     * Ask a question for a session or event.
     */
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|exists:sessions,id',
            'event_id' => 'required|exists:events,id',
            'question_text' => 'required|string|max:1000',
        ]);

        // Get attendee record
        $attendee = \App\Models\Attendee::where('user_id', Auth::id())
            ->where('event_id', $validated['event_id'])
            ->first();

        if (!$attendee) {
            return response()->json([
                'message' => 'You must be registered for this event to ask questions'
            ], 403);
        }

        $question = Question::create([
            'session_id' => $validated['session_id'] ?? null,
            'event_id' => $validated['event_id'],
            'attendee_id' => $attendee->id,
            'question_text' => $validated['question_text'],
            'upvotes' => 0,
            'is_answered' => false,
            'is_hidden' => false,
        ]);

        // Broadcast the question
        event(new QuestionAsked($question));

        return response()->json($question, 201);
    }

    /**
     * Upvote a question.
     */
    public function upvote(Question $question)
    {
        // Check if user is registered for the event
        $attendee = \App\Models\Attendee::where('user_id', Auth::id())
            ->where('event_id', $question->event_id)
            ->first();

        if (!$attendee) {
            return response()->json([
                'message' => 'You must be registered for this event'
            ], 403);
        }

        $question->upvote();

        return response()->json([
            'success' => true,
            'upvotes' => $question->upvotes
        ]);
    }

    /**
     * Answer a question (organizer/speaker only).
     */
    public function answer(Request $request, Question $question)
    {
        $validated = $request->validate([
            'answer_text' => 'required|string',
        ]);

        // Check authorization - must be event organizer or session speaker
        $event = $question->event;
        
        if ($event->organizer_id !== Auth::id()) {
            // Check if user is a speaker for the session
            if ($question->session) {
                $isSpeaker = $question->session->speakers()
                    ->where('speaker_id', Auth::id())
                    ->exists();
                
                if (!$isSpeaker) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $question->markAsAnswered(
            $validated['answer_text'],
            Auth::id()
        );

        return response()->json($question);
    }

    /**
     * Hide/show a question (moderator only).
     */
    public function toggleVisibility(Question $question)
    {
        // Check authorization - must be event organizer
        $event = $question->event;
        
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($question->is_hidden) {
            $question->show();
        } else {
            $question->hide();
        }

        return response()->json($question);
    }

    /**
     * Get questions for a session or event.
     */
    public function index(Request $request)
    {
        $query = Question::visible();

        if ($request->has('session_id')) {
            $query->forSession($request->session_id);
        }

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Sort by upvotes (top rated first)
        $questions = $query->with(['attendee.user', 'answerer'])
            ->orderBy('upvotes', 'desc')
            ->paginate(20);

        return response()->json($questions);
    }

    /**
     * Get unanswered questions for a session/event.
     */
    public function unanswered(Request $request)
    {
        $query = Question::visible()->unanswered();

        if ($request->has('session_id')) {
            $query->forSession($request->session_id);
        }

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $questions = $query->with(['attendee.user'])
            ->orderBy('upvotes', 'desc')
            ->paginate(20);

        return response()->json($questions);
    }
}
