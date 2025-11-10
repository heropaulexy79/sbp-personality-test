<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuizController extends Controller
{
    // List all quizzes for the logged-in user
    public function index()
    {
        // Filter quizzes to only show those created by the currently authenticated user
        // We ensure the type filter matches the value saved by the generator controller ('personality_quiz').
        $quizzes = Lesson::query()
            ->where('user_id', auth()->id())
            ->where('type', 'personality_quiz') // <-- CHANGED to lowercase 'personality_quiz'
            ->orderByDesc('created_at')
            // Map the collection to ensure only necessary, simple data is sent to the frontend
            ->get()
            ->map(fn (Lesson $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'slug' => $quiz->slug,
                'type' => $quiz->type,
                // Add is_published if the frontend uses it for display
                'is_published' => (bool) $quiz->is_published,
            ]);

        return Inertia::render('Quiz/Index', [
            'quizzes' => $quizzes
        ]);
    }

    public function create()
    {
        return Inertia::render('Quiz/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // 'type' is no longer needed from the request
        ]);

        $quiz = Lesson::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
            'type' => 'personality_quiz', // <-- CHANGED to lowercase 'personality_quiz'
            'content_json' => [],
            'user_id' => auth()->id(), // <-- MANDATORY: Set ownership for the logged-in user
        ]);

        return redirect()->route('quizzes.edit', $quiz->id);
    }

    public function edit(Lesson $quiz)
    {
        return Inertia::render('Quiz/Edit', [
            'lesson' => $quiz
        ]);
    }

    public function update(Request $request, Lesson $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:lessons,slug,' . $quiz->id,
            'content' => 'nullable|string',
            'quiz' => 'nullable|array',
            'personality_quiz' => 'nullable|array',
            'is_published' => 'required',
        ]);

        // Map the frontend 'quiz' or 'personality_quiz' data to the 'content_json' column
        // We use lowercase 'personality_quiz' here for consistency
        if ($quiz->type === 'personality_quiz') {
            $validated['content_json'] = $validated['personality_quiz'];
        } else {
            $validated['content_json'] = $validated['quiz'];
        }

        unset($validated['quiz']);
        unset($validated['personality_quiz']);

        $quiz->update($validated);

        return back()->with('success', 'Quiz updated successfully');
    }

    public function destroy(Lesson $quiz)
    {
        $quiz->delete();
        return redirect()->route('dashboard');
    }
}