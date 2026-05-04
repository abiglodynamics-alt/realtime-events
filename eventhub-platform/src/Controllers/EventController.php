<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendee;
use App\Models\Session;
use App\Models\Speaker;
use App\Models\Question;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::published();

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('featured') && $request->boolean('featured')) {
            $query->featured();
        }

        if ($request->has('upcoming') && $request->boolean('upcoming')) {
            $query->upcoming();
        }

        $events = $query->with(['organizer', 'days'])
                       ->orderBy('starts_at', 'desc')
                       ->paginate(12);

        return response()->json($events);
    }

    public function show($slug)
    {
        $event = Event::where('slug', $slug)
                     ->with([
                         'organizer',
                         'days.sessions.speakers',
                         'tracks',
                         'speakers.sessions',
                     ])
                     ->firstOrFail();

        if (!$event->is_published && Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json($event);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after:registration_starts_at',
            'max_attendees' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'allow_rsvp' => 'boolean',
        ]);

        $validated['organizer_id'] = Auth::id();
        $validated['timezone'] = $validated['timezone'] ?? config('app.timezone');

        $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
            'starts_at' => 'sometimes|required|date',
            'ends_at' => 'sometimes|required|date|after:starts_at',
            'registration_starts_at' => 'nullable|date',
            'registration_ends_at' => 'nullable|date|after:registration_starts_at',
            'max_attendees' => 'nullable|integer|min:1',
            'is_published' => 'boolean',
            'allow_rsvp' => 'boolean',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(null, 204);
    }

    public function rsvp(Request $request, Event $event)
    {
        if (!$event->registrationIsOpen()) {
            return response()->json([
                'message' => 'Registration is not open for this event'
            ], 400);
        }

        if ($event->isFull()) {
            return response()->json([
                'message' => 'Event is full. You can join the waitlist.'
            ], 400);
        }

        $validated = $request->validate([
            'ticket_type' => 'sometimes|in:free,vip,premium,student,speaker,sponsor,staff',
            'dietary_requirements' => 'nullable|string',
            'tshirt_size' => 'nullable|string|max:20',
            'registration_notes' => 'nullable|string',
        ]);

        $attendee = Attendee::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ],
            array_merge($validated, [
                'rsvp_status' => 'confirmed',
            ])
        );

        if ($event->isFull() && $attendee->wasRecentlyCreated) {
            $attendee->update(['rsvp_status' => 'waitlist']);
        }

        $attendee->generateQrCode();

        return response()->json($attendee->load('event'), 201);
    }

    public function cancelRsvp(Event $event)
    {
        $attendee = Attendee::where('event_id', $event->id)
                           ->where('user_id', Auth::id())
                           ->first();

        if (!$attendee) {
            return response()->json(['message' => 'No registration found'], 404);
        }

        $attendee->cancel();

        // Promote someone from waitlist if available
        $nextOnWaitlist = Attendee::where('event_id', $event->id)
                                  ->where('rsvp_status', 'waitlist')
                                  ->orderBy('created_at')
                                  ->first();

        if ($nextOnWaitlist) {
            $nextOnWaitlist->confirm();
        }

        return response()->json(['message' => 'Registration cancelled successfully']);
    }

    public function attendees(Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendees = $event->attendees()
                          ->with('user')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json($attendees);
    }

    public function checkIn(Request $request, Event $event)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'qr_code_data' => 'required|string',
        ]);

        try {
            $data = json_decode($validated['qr_code_data'], true);
            
            if (!isset($data['attendee_id'])) {
                return response()->json(['message' => 'Invalid QR code'], 400);
            }

            $attendee = Attendee::where('id', $data['attendee_id'])
                               ->where('event_id', $event->id)
                               ->firstOrFail();

            if (!$attendee->isConfirmed()) {
                return response()->json(['message' => 'Attendee is not confirmed'], 400);
            }

            $attendee->checkIn();

            return response()->json([
                'message' => 'Check-in successful',
                'attendee' => $attendee->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid QR code data'], 400);
        }
    }
}
