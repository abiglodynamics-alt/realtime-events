<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\PollResultsUpdated;

class PollController extends Controller
{
    /**
     * Create a new poll.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'session_id' => 'nullable|exists:sessions,id',
            'question' => 'required|string|max:500',
            'poll_type' => 'required|in:single,multiple',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:500',
            'ends_at' => 'nullable|date|after:now',
        ]);

        // Check authorization - must be event organizer
        $event = \App\Models\Event::findOrFail($validated['event_id']);
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $poll = Poll::create([
                'event_id' => $validated['event_id'],
                'session_id' => $validated['session_id'] ?? null,
                'question' => $validated['question'],
                'poll_type' => $validated['poll_type'],
                'is_active' => false,
                'show_results' => false,
                'ends_at' => $validated['ends_at'] ?? null,
            ]);

            // Create poll options
            foreach ($validated['options'] as $index => $optionText) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'option_text' => $optionText,
                    'vote_count' => 0,
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            $poll->load('options');

            return response()->json($poll, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create poll'], 500);
        }
    }

    /**
     * Vote on a poll.
     */
    public function vote(Request $request, Poll $poll)
    {
        $validated = $request->validate([
            'option_ids' => 'required|array|min:1',
            'option_ids.*' => 'required|exists:poll_options,id',
        ]);

        // Verify all options belong to this poll
        $validOptions = PollOption::where('poll_id', $poll->id)
            ->whereIn('id', $validated['option_ids'])
            ->count();

        if ($validOptions !== count($validated['option_ids'])) {
            return response()->json(['message' => 'Invalid option(s)'], 400);
        }

        // Check if poll is active
        if (!$poll->isActiveNow()) {
            return response()->json(['message' => 'Poll is not active'], 400);
        }

        // Get attendee record
        $attendee = Attendee::where('user_id', Auth::id())
            ->where('event_id', $poll->event_id)
            ->first();

        if (!$attendee) {
            return response()->json([
                'message' => 'You must be registered for this event to vote'
            ], 403);
        }

        // Check if already voted
        $existingVote = PollVote::where('poll_id', $poll->id)
            ->where('attendee_id', $attendee->id)
            ->first();

        if ($existingVote) {
            return response()->json([
                'message' => 'You have already voted on this poll'
            ], 409);
        }

        // For single choice polls, only allow one option
        if ($poll->poll_type === Poll::TYPE_SINGLE && count($validated['option_ids']) > 1) {
            return response()->json([
                'message' => 'This poll only allows single choice'
            ], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($validated['option_ids'] as $optionId) {
                $option = PollOption::findOrFail($optionId);
                $option->addVote();

                PollVote::create([
                    'poll_id' => $poll->id,
                    'option_id' => $optionId,
                    'attendee_id' => $attendee->id,
                ]);
            }

            DB::commit();

            // Broadcast updated results
            event(new PollResultsUpdated($poll));

            $poll->load('options');

            return response()->json([
                'success' => true,
                'poll' => $poll
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit vote'], 500);
        }
    }

    /**
     * Get poll results.
     */
    public function results(Poll $poll)
    {
        // Check if user is allowed to see results
        if (!$poll->show_results) {
            // Organizers and speakers can always see results
            $event = $poll->event;
            $canSeeResults = $event->organizer_id === Auth::id();

            if (!$canSeeResults && $poll->session) {
                $canSeeResults = $poll->session->speakers()
                    ->where('speaker_id', Auth::id())
                    ->exists();
            }

            if (!$canSeeResults) {
                return response()->json([
                    'message' => 'Results are not visible yet'
                ], 403);
            }
        }

        $poll->load('options');

        return response()->json($poll);
    }

    /**
     * Activate/deactivate a poll.
     */
    public function toggleActive(Poll $poll)
    {
        // Check authorization
        $event = $poll->event;
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($poll->is_active) {
            $poll->deactivate();
        } else {
            $poll->activate();
        }

        // Broadcast updated status
        event(new PollResultsUpdated($poll));

        return response()->json($poll);
    }

    /**
     * Toggle results visibility.
     */
    public function toggleResults(Poll $poll)
    {
        // Check authorization
        $event = $poll->event;
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $poll->toggleResults();

        // Broadcast updated status
        event(new PollResultsUpdated($poll));

        return response()->json($poll);
    }

    /**
     * Get active polls for an event/session.
     */
    public function active(Request $request)
    {
        $query = Poll::active();

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $polls = $query->with('options')->get();

        return response()->json($polls);
    }
}
