<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Reader\QrReader;

class AttendeeController extends Controller
{
    /**
     * RSVP to an event.
     */
    public function rsvp(Request $request, Event $event)
    {
        $validated = $request->validate([
            'ticket_type' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if already registered
        $existingAttendee = Attendee::where('user_id', Auth::id())
            ->where('event_id', $event->id)
            ->first();

        if ($existingAttendee) {
            return response()->json([
                'message' => 'Already registered for this event',
                'attendee' => $existingAttendee
            ], 409);
        }

        $attendee = Attendee::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'rsvp_status' => Attendee::RSVP_PENDING,
            'ticket_type' => $validated['ticket_type'] ?? 'general',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($attendee, 201);
    }

    /**
     * Confirm attendee registration.
     */
    public function confirm(Attendee $attendee)
    {
        // Check authorization (organizer or admin)
        $event = $attendee->event;
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendee->confirm();

        return response()->json($attendee);
    }

    /**
     * Check in an attendee via QR code.
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        // Extract attendee ID from QR code
        // QR code format: {attendee_id}-{user_id}-{event_id}-{unique}
        $parts = explode('-', $validated['qr_code']);
        
        if (count($parts) < 3) {
            return response()->json(['message' => 'Invalid QR code'], 400);
        }

        $attendeeId = $parts[0];
        $userId = $parts[1];
        $eventId = $parts[2];

        $attendee = Attendee::where('id', $attendeeId)
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->first();

        if (!$attendee) {
            return response()->json(['message' => 'Attendee not found'], 404);
        }

        // Verify the person checking in is authorized (event organizer/staff)
        $event = $attendee->event;
        if ($event->organizer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($attendee->hasCheckedIn()) {
            return response()->json([
                'message' => 'Attendee already checked in',
                'checked_in_at' => $attendee->checked_in_at
            ], 409);
        }

        $success = $attendee->checkIn();

        if (!$success) {
            return response()->json([
                'message' => 'Cannot check in. RSVP status must be confirmed.'
            ], 400);
        }

        return response()->json([
            'message' => 'Check-in successful',
            'attendee' => $attendee
        ]);
    }

    /**
     * Get attendee's events with QR codes.
     */
    public function myEvents()
    {
        $attendees = Attendee::where('user_id', Auth::id())
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attendees);
    }

    /**
     * Download QR code for an attendee.
     */
    public function downloadQrCode(Attendee $attendee)
    {
        // Check authorization
        if ($attendee->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$attendee->qr_code) {
            $attendee->generateQrCode();
        }

        $filePath = $attendee->qr_code;
        
        if (!Storage::exists($filePath)) {
            $attendee->generateQrCode();
        }

        return Storage::download($filePath);
    }

    /**
     * Cancel RSVP.
     */
    public function cancel(Attendee $attendee)
    {
        // Check authorization
        if ($attendee->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $attendee->cancel();

        return response()->json($attendee);
    }
}
