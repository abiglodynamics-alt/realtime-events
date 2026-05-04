<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionVote;
use App\Models\Event;
use App\Models\Session;
use App\Broadcasts\QuestionCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $query = Question::forEvent($event->id)->visible();

        if ($request->has('session_id')) {
            $query->forSession($request->session_id);
        }

        if ($request->has('answered')) {
            $query->where('is_answered', $request->boolean('answered'));
        }

        $sortBy = $request->get('sort', 'popular');
        if ($sortBy === 'recent') {
            $query->recent();
        } else {
            $query->popular();
        }

        $questions = $query->with(['user', 'answerer'])
                          ->paginate(20);

        // Add hasUpvoted flag for current user
        if (Auth::check()) {
            $questionIds = $questions->pluck('id');
            $upvotedIds = QuestionVote::where('user_id', Auth::id())
                                     ->whereIn('question_id', $questionIds)
                                     ->pluck('question_id')
                                     ->flip();

            $questions->getCollection()->transform(function ($question) use ($upvotedIds) {
                $question->has_upvoted = $upvotedIds->has($question->id);
                return $question;
            });
        }

        return response()->json($questions);
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'session_id' => 'nullable|exists:sessions,id',
        ]);

        // Validate session belongs to event if provided
        if (isset($validated['session_id'])) {
            $session = Session::findOrFail($validated['session_id']);
            if ($session->event_id !== $event->id) {
                return response()->json(['message' => 'Invalid session for this event'], 400);
            }
        }

        $validated['event_id'] = $event->id;
        $validated['user_id'] = Auth::id();
        $validated['upvotes'] = 0;
        $validated['is_answered'] = false;
        $validated['is_hidden'] = false;

        $question = Question::create($validated);
        $question->load('user');

        // Broadcast the new question
        broadcast(new QuestionCreated($question))->toOthers();

        return response()->json($question, 201);
    }

    public function upvote(Event $event, Question $question)
    {
        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        if (!$question->isVisible()) {
            return response()->json(['message' => 'Question is not visible'], 403);
        }

        DB::transaction(function () use ($question) {
            $question->upvote(Auth::user());
        });

        $question->load('user');
        
        broadcast(new QuestionCreated($question))->toOthers();

        return response()->json([
            'success' => true,
            'upvotes' => $question->upvotes,
            'has_upvoted' => true
        ]);
    }

    public function removeUpvote(Event $event, Question $question)
    {
        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        $removed = $question->removeUpvote(Auth::user());

        if (!$removed) {
            return response()->json(['message' => 'You have not upvoted this question'], 400);
        }

        return response()->json([
            'success' => true,
            'upvotes' => $question->upvotes,
            'has_upvoted' => false
        ]);
    }

    public function answer(Request $request, Event $event, Question $question)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        $validated = $request->validate([
            'answer' => 'required|string',
        ]);

        $question->update([
            'answer' => $validated['answer'],
            'is_answered' => true,
            'answered_by' => Auth::id(),
            'answered_at' => now(),
        ]);

        $question->load(['user', 'answerer']);

        return response()->json($question);
    }

    public function hide(Event $event, Question $question)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        $question->hide();

        return response()->json(['message' => 'Question hidden successfully']);
    }

    public function show(Event $event, Question $question)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        $question->show();

        return response()->json(['message' => 'Question shown successfully']);
    }

    public function destroy(Event $event, Question $question)
    {
        if (Auth::id() !== $event->organizer_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($question->event_id !== $event->id) {
            return response()->json(['message' => 'Question not found in this event'], 404);
        }

        $question->delete();

        return response()->json(null, 204);
    }
}
