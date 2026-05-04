<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Speaker;
use App\Models\EventDay;
use App\Models\Track;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    public function index(Event $event)
    {
        $sessions = $event->sessions()
                         ->with(['day', 'track', 'speakers'])
                         ->orderBy('starts_at')
                         ->get()
                         ->groupBy(function ($session) {
                             return $session->day?->date ?? $session->starts_at->format('Y-m-d');
                         });

        return response()->json([
            'event' => $event,
            'sessions_by_day' => $sessions
        ]);
    }

    public function show(Event $event, Session $session)
    {
        if ($session->event_id !== $event->id) {
            return response()->json(['message' => 'Session not found in this event'], 404);
        }

        $session->load(['day', 'track', 'speakers', 'questions.visible.popular']);

        return response()->json($session);
    }

    public function store(Request $request, Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'sometimes|in:keynote,talk,workshop,panel,break,networking,other',
            'day_id' => 'nullable|exists:event_days,id',
            'track_id' => 'nullable|exists:tracks,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'room' => 'nullable|string|max:100',
            'stream_url' => 'nullable|url|max:255',
            'capacity' => 'nullable|integer|min:1',
            'is_live' => 'boolean',
            'is_break' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Validate day belongs to event if provided
        if (isset($validated['day_id'])) {
            $day = EventDay::findOrFail($validated['day_id']);
            if ($day->event_id !== $event->id) {
                return response()->json(['message' => 'Invalid day for this event'], 400);
            }
        }

        // Validate track belongs to event if provided
        if (isset($validated['track_id'])) {
            $track = Track::findOrFail($validated['track_id']);
            if ($track->event_id !== $event->id) {
                return response()->json(['message' => 'Invalid track for this event'], 400);
            }
        }

        $validated['event_id'] = $event->id;
        $validated['session_type'] = $validated['session_type'] ?? 'talk';
        $validated['is_live'] = $validated['is_live'] ?? false;
        $validated['is_break'] = $validated['is_break'] ?? false;

        $session = Session::create($validated);

        return response()->json($session->load(['day', 'track', 'speakers']), 201);
    }

    public function update(Request $request, Event $event, Session $session)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->event_id !== $event->id) {
            return response()->json(['message' => 'Session not found in this event'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'sometimes|in:keynote,talk,workshop,panel,break,networking,other',
            'day_id' => 'nullable|exists:event_days,id',
            'track_id' => 'nullable|exists:tracks,id',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'sometimes|required|date|after:starts_at',
            'room' => 'nullable|string|max:100',
            'stream_url' => 'nullable|url|max:255',
            'capacity' => 'nullable|integer|min:1',
            'is_live' => 'boolean',
            'is_break' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $session->update($validated);

        return response()->json($session->load(['day', 'track', 'speakers']));
    }

    public function destroy(Event $event, Session $session)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->event_id !== $event->id) {
            return response()->json(['message' => 'Session not found in this event'], 404);
        }

        $session->delete();

        return response()->json(null, 204);
    }

    public function addSpeakers(Request $request, Event $event, Session $session)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->event_id !== $event->id) {
            return response()->json(['message' => 'Session not found in this event'], 404);
        }

        $validated = $request->validate([
            'speaker_ids' => 'required|array',
            'speaker_ids.*' => 'exists:speakers,id',
        ]);

        // Verify speakers belong to this event (optional business logic)
        $speakerIds = $validated['speaker_ids'];
        
        $session->speakers()->syncWithoutDetaching($speakerIds);

        return response()->json($session->load(['speakers']));
    }

    public function removeSpeaker(Event $event, Session $session, Speaker $speaker)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->event_id !== $event->id) {
            return response()->json(['message' => 'Session not found in this event'], 404);
        }

        $session->speakers()->detach($speaker->id);

        return response()->json(null, 204);
    }
}
