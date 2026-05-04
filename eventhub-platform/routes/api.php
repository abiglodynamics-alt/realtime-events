<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PollController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public event routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']); // List all events
    Route::get('/{slug}', [EventController::class, 'show']); // Show event details
    
    // Event resource routes (require auth for write operations)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [EventController::class, 'store']); // Create event
        Route::put('/{event}', [EventController::class, 'update']); // Update event
        Route::delete('/{event}', [EventController::class, 'destroy']); // Delete event
    });
    
    // RSVP routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/{event}/rsvp', [EventController::class, 'rsvp']); // Register for event
        Route::delete('/{event}/rsvp', [EventController::class, 'cancelRsvp']); // Cancel registration
    });
    
    // Organizer-only routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/{event}/attendees', [EventController::class, 'attendees']); // List attendees
        Route::post('/{event}/check-in', [EventController::class, 'checkIn']); // Check in attendee
    });
    
    // Agenda/Sessions routes
    Route::prefix('{event}/agenda')->group(function () {
        Route::get('/', [AgendaController::class, 'index']); // List all sessions
        Route::get('/{session}', [AgendaController::class, 'show']); // Show session details
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [AgendaController::class, 'store']); // Create session
            Route::put('/{session}', [AgendaController::class, 'update']); // Update session
            Route::delete('/{session}', [AgendaController::class, 'destroy']); // Delete session
            Route::post('/{session}/speakers', [AgendaController::class, 'addSpeakers']); // Add speakers
            Route::delete('/{session}/speakers/{speaker}', [AgendaController::class, 'removeSpeaker']); // Remove speaker
        });
    });
    
    // Q&A routes
    Route::prefix('{event}/questions')->group(function () {
        Route::get('/', [QuestionController::class, 'index']); // List questions
        Route::get('/{question}', [QuestionController::class, 'show']); // Show question (if needed)
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [QuestionController::class, 'store']); // Ask question
            Route::post('/{question}/upvote', [QuestionController::class, 'upvote']); // Upvote question
            Route::delete('/{question}/upvote', [QuestionController::class, 'removeUpvote']); // Remove upvote
        });
        
        // Organizer-only routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/{question}/answer', [QuestionController::class, 'answer']); // Answer question
            Route::post('/{question}/hide', [QuestionController::class, 'hide']); // Hide question
            Route::post('/{question}/show', [QuestionController::class, 'show']); // Show question
            Route::delete('/{question}', [QuestionController::class, 'destroy']); // Delete question
        });
    });
    
    // Polls routes
    Route::prefix('{event}/polls')->group(function () {
        Route::get('/', [PollController::class, 'index']); // List polls
        Route::get('/{poll}', [PollController::class, 'show']); // Show poll details
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [PollController::class, 'store']); // Create poll (organizer)
            Route::put('/{poll}', [PollController::class, 'update']); // Update poll (organizer)
            Route::post('/{poll}/activate', [PollController::class, 'activate']); // Activate poll (organizer)
            Route::post('/{poll}/deactivate', [PollController::class, 'deactivate']); // Deactivate poll (organizer)
            Route::post('/{poll}/vote', [PollController::class, 'vote']); // Vote on poll
            Route::delete('/{poll}', [PollController::class, 'destroy']); // Delete poll (organizer)
        });
    });
});
