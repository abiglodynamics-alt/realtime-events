<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Event;
use App\Models\Session;
use App\Broadcasts\PollUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $query = Poll::forEvent($event->id);

        if ($request->has('session_id')) {
            $query->forSession($request->session_id);
        }

        if ($request->has('active') && $request->boolean('active')) {
            $query->active();
        }

        $polls = $query->with(['options', 'session'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        // Add user vote info and results visibility
        if (Auth::check()) {
            $pollIds = $polls->pluck('id');
            $userVotes = PollVote::where('user_id', Auth::id())
                                ->whereIn('poll_id', $pollIds)
                                ->with('option')
                                ->get()
                                ->keyBy('poll_id');

            $polls->getCollection()->transform(function ($poll) use ($userVotes) {
                $poll->user_vote = $userVotes->has($poll->id) 
                    ? $userVotes[$poll->id]->option 
                    : null;
                $poll->can_see_results = $poll->canShowResults(Auth::user());
                return $poll;
            });
        }

        return response()->json($polls);
    }

    public function show(Event $event, Poll $poll)
    {
        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        $poll->load(['options', 'session']);

        if (Auth::check()) {
            $poll->user_vote = PollVote::where('poll_id', $poll->id)
                                      ->where('user_id', Auth::id())
                                      ->with('option')
                                      ->first()?->option;
            $poll->can_see_results = $poll->canShowResults(Auth::user());
        }

        return response()->json($poll);
    }

    public function store(Request $request, Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_id' => 'nullable|exists:sessions,id',
            'poll_type' => 'sometimes|in:single,multiple',
            'show_results' => 'sometimes|in:never,after_vote,always',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'options' => 'required|array|min:2|max:10',
            'options.*.text' => 'required|string|max:255',
        ]);

        // Validate session belongs to event if provided
        if (isset($validated['session_id'])) {
            $session = Session::findOrFail($validated['session_id']);
            if ($session->event_id !== $event->id) {
                return response()->json(['message' => 'Invalid session for this event'], 400);
            }
        }

        $validated['event_id'] = $event->id;
        $validated['poll_type'] = $validated['poll_type'] ?? 'single';
        $validated['show_results'] = $validated['show_results'] ?? 'after_vote';
        $validated['is_active'] = false;

        DB::transaction(function () use ($validated) {
            $options = $validated['options'];
            unset($validated['options']);

            $poll = Poll::create($validated);

            foreach ($options as $index => $optionData) {
                $poll->options()->create([
                    'text' => $optionData['text'],
                    'sort_order' => $index,
                    'vote_count' => 0,
                ]);
            }

            return $poll;
        });

        $poll = Poll::with('options')->find($poll->id);

        return response()->json($poll, 201);
    }

    public function update(Request $request, Event $event, Poll $poll)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'poll_type' => 'sometimes|in:single,multiple',
            'show_results' => 'sometimes|in:never,after_vote,always',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        $poll->update($validated);
        $poll->load('options');

        broadcast(new PollUpdated($poll))->toOthers();

        return response()->json($poll);
    }

    public function activate(Event $event, Poll $poll)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        // Deactivate other polls in the same session if exists
        if ($poll->session_id) {
            Poll::where('session_id', $poll->session_id)
                ->where('id', '!=', $poll->id)
                ->update(['is_active' => false]);
        }

        $poll->activate();
        $poll->load('options');

        broadcast(new PollUpdated($poll))->toOthers();

        return response()->json($poll);
    }

    public function deactivate(Event $event, Poll $poll)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        $poll->deactivate();
        $poll->load('options');

        broadcast(new PollUpdated($poll))->toOthers();

        return response()->json($poll);
    }

    public function vote(Request $request, Event $event, Poll $poll)
    {
        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        if (!$poll->isActive()) {
            return response()->json(['message' => 'Poll is not active'], 400);
        }

        $validated = $request->validate([
            'option_ids' => 'required|array',
            'option_ids.*' => 'exists:poll_options,id',
        ]);

        // Verify options belong to this poll
        $validOptionIds = $poll->options()->pluck('id');
        $submittedOptionIds = collect($validated['option_ids']);

        if (!$submittedOptionIds->diff($validOptionIds)->isEmpty()) {
            return response()->json(['message' => 'Invalid option(s) for this poll'], 400);
        }

        // Check poll type constraints
        if ($poll->poll_type === 'single' && count($validated['option_ids']) > 1) {
            return response()->json(['message' => 'This poll only allows single choice'], 400);
        }

        DB::transaction(function () use ($poll, $validated) {
            // Remove existing votes
            PollVote::where('poll_id', $poll->id)
                   ->where('user_id', Auth::id())
                   ->delete();

            // Decrement old vote counts
            $oldVotes = PollVote::whereIn('option_id', $validated['option_ids'])
                               ->where('poll_id', $poll->id)
                               ->get();
            
            // Create new votes
            foreach ($validated['option_ids'] as $optionId) {
                $option = PollOption::findOrFail($optionId);
                
                PollVote::create([
                    'poll_id' => $poll->id,
                    'option_id' => $optionId,
                    'user_id' => Auth::id(),
                ]);

                $option->incrementVotes();
            }
        });

        $poll->load('options');

        broadcast(new PollUpdated($poll))->toOthers();

        return response()->json([
            'success' => true,
            'poll' => $poll,
            'can_see_results' => $poll->canShowResults(Auth::user())
        ]);
    }

    public function destroy(Event $event, Poll $poll)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($poll->event_id !== $event->id) {
            return response()->json(['message' => 'Poll not found in this event'], 404);
        }

        $poll->delete();

        return response()->json(null, 204);
    }
}
