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
        // Assuming you might want to only show the user's own quizzes later.
        // For now, we'll show all for simplicity, or you can add ->where('user_id', auth()->id()) if you add a user_id to lessons.
        $quizzes = Lesson::query()
            ->orderByDesc('created_at')
            ->get();

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
            'type' => 'PERSONALITY_QUIZ', // <-- Hardcode the type here
            'content_json' => [],
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
        if ($quiz->type === 'PERSONALITY_QUIZ') {
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