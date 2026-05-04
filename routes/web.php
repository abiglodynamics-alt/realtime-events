<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SpeakerController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AttendeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return view('home');
})->name('home');

// Events
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event}/agenda', [EventController::class, 'agenda'])->name('events.agenda');
Route::get('/events/{event}/speakers', [EventController::class, 'speakers'])->name('events.speakers');

// Speakers
Route::get('/speakers', [SpeakerController::class, 'index'])->name('speakers.index');
Route::get('/speakers/{speaker}', [SpeakerController::class, 'show'])->name('speakers.show');

// Attendee Registration
Route::post('/events/{event}/register', [AttendeeController::class, 'register'])->name('events.register');
Route::get('/events/{event}/ticket/{attendee}', [AttendeeController::class, 'ticket'])->name('events.ticket');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    // Events
    Route::apiResource('events', EventController::class);
    
    // Event Sessions
    Route::get('events/{event}/sessions', [SessionController::class, 'index']);
    Route::post('events/{event}/sessions', [SessionController::class, 'store']);
    
    // Event Speakers
    Route::get('events/{event}/speakers', [SpeakerController::class, 'byEvent']);
    
    // Q&A
    Route::get('events/{event}/questions', [EventController::class, 'questions']);
    Route::post('events/{event}/questions', [EventController::class, 'askQuestion']);
    Route::post('events/{event}/questions/{question}/vote', [EventController::class, 'voteQuestion']);
    
    // Polls
    Route::get('events/{event}/polls', [EventController::class, 'polls']);
    Route::post('events/{event}/polls', [EventController::class, 'createPoll']);
    Route::post('events/{event}/polls/{poll}/vote', [EventController::class, 'votePoll']);
    
    // Attendees
    Route::post('events/{event}/attendees', [AttendeeController::class, 'register']);
    Route::get('events/{event}/attendees/{attendee}/qr', [AttendeeController::class, 'qrCode']);
    Route::post('attendees/checkin', [AttendeeController::class, 'checkIn']);
});
