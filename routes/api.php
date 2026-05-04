<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PollController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Event management (organizer only)
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    
    // Attendee/RSVP routes
    Route::post('/events/{event}/rsvp', [AttendeeController::class, 'rsvp']);
    Route::get('/my-events', [AttendeeController::class, 'myEvents']);
    Route::get('/attendees/{attendee}/qr-code', [AttendeeController::class, 'downloadQrCode']);
    Route::post('/attendees/{attendee}/cancel', [AttendeeController::class, 'cancel']);
    
    // Check-in (organizer/staff only)
    Route::post('/check-in', [AttendeeController::class, 'checkIn']);
    Route::post('/attendees/{attendee}/confirm', [AttendeeController::class, 'confirm']);
    
    // Q&A routes
    Route::post('/questions', [QuestionController::class, 'ask']);
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/unanswered', [QuestionController::class, 'unanswered']);
    Route::post('/questions/{question}/upvote', [QuestionController::class, 'upvote']);
    Route::post('/questions/{question}/answer', [QuestionController::class, 'answer']);
    Route::post('/questions/{question}/toggle-visibility', [QuestionController::class, 'toggleVisibility']);
    
    // Poll routes
    Route::post('/polls', [PollController::class, 'store']);
    Route::get('/polls/active', [PollController::class, 'active']);
    Route::get('/polls/{poll}', [PollController::class, 'results']);
    Route::post('/polls/{poll}/vote', [PollController::class, 'vote']);
    Route::post('/polls/{poll}/toggle-active', [PollController::class, 'toggleActive']);
    Route::post('/polls/{poll}/toggle-results', [PollController::class, 'toggleResults']);
});

/*
|--------------------------------------------------------------------------
| Broadcast Routes
|--------------------------------------------------------------------------
*/

Route::post('/broadcasting/auth', function () {
    return request()->user() ? 
        auth()->user()->canAccessChannel(request()->channelName) : 
        false;
})->middleware(['auth:sanctum']);
