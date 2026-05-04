<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request)
    {
        $query = Event::with('organizer')
            ->published()
            ->orderBy('start_date', 'asc');

        // Search filter
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Location type filter
        if ($request->has('location_type')) {
            if ($request->location_type === 'online') {
                $query->whereNull('location_name');
            } else {
                $query->whereNotNull('location_name');
            }
        }

        $events = $query->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['days.sessions.speakers', 'days.tracks', 'speakers']);
        
        $speakers = $event->speakers()->with('sessions')->get();

        return view('events.show', compact('event', 'speakers'));
    }

    /**
     * Get questions for an event (API).
     */
    public function questions(Event $event)
    {
        $questions = $event->questions()
            ->where('is_hidden', false)
            ->with('user')
            ->orderBy('votes', 'desc')
            ->get();

        return response()->json($questions);
    }

    /**
     * Submit a new question (API).
     */
    public function askQuestion(Request $request, Event $event)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
        ]);

        $question = $event->questions()->create([
            'user_id' => auth()->id() ?? null,
            'question' => $validated['question'],
            'is_hidden' => false,
            'is_answered' => false,
        ]);

        broadcast(new \App\Events\QuestionAsked($question))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $question
        ]);
    }

    /**
     * Vote on a question (API).
     */
    public function voteQuestion(Request $request, Event $event, $questionId)
    {
        $question = $event->questions()->findOrFail($questionId);
        $question->increment('votes');

        return response()->json([
            'success' => true,
            'votes' => $question->votes
        ]);
    }

    /**
     * Get active polls for an event (API).
     */
    public function polls(Event $event)
    {
        $polls = $event->polls()
            ->where('is_active', true)
            ->with('options')
            ->get();

        return response()->json($polls);
    }

    /**
     * Create a new poll (API).
     */
    public function createPoll(Request $request, Event $event)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:10',
            'allow_multiple' => 'boolean',
        ]);

        $poll = $event->polls()->create([
            'question' => $validated['question'],
            'allow_multiple' => $validated['allow_multiple'] ?? false,
            'is_active' => true,
        ]);

        foreach ($validated['options'] as $optionText) {
            $poll->options()->create(['text' => $optionText]);
        }

        broadcast(new \App\Events\PollCreated($poll))->toOthers();

        return response()->json([
            'success' => true,
            'data' => $poll->load('options')
        ]);
    }

    /**
     * Vote on a poll (API).
     */
    public function votePoll(Request $request, Event $event, $pollId)
    {
        $validated = $request->validate([
            'option_ids' => 'required|array',
        ]);

        $poll = $event->polls()->findOrFail($pollId);

        foreach ($validated['option_ids'] as $optionId) {
            $option = $poll->options()->findOrFail($optionId);
            $option->increment('votes');
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
