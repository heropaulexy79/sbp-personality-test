<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuizController extends Controller
{
    // List all quizzes for the logged-in user
    public function index()
    {
        // Filter quizzes to only show those created by the currently authenticated user
        // and filter by the correct lowercase type ('personality_quiz').
        $quizzes = Lesson::query()
            ->where('user_id', auth()->id()) // RESTORING THE USER FILTER
            ->where('type', 'personality_quiz')
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
            'type' => 'required|string|in:PERSONALITY_QUIZ,STANDARD_QUIZ',
            // --- 1. ADD VALIDATION FOR YOUR QUIZ DATA ---
            'content_json' => 'present|array', 
            'content_json.questions' => 'nullable|array',
            'content_json.traits' => 'nullable|array',
            'content_json.archetypes' => 'nullable|array', // <-- ADD THIS LINE
            // FIX: Add validation for is_published status
            'is_published' => 'boolean', 
        ]);

        $lesson = Lesson::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'user_id' => $request->user()->id,
            // FIX: Set is_published based on the request, defaulting to false
            'is_published' => $validated['is_published'] ?? false, 
            // --- 2. ADD THE SLUG ---
            'slug' => Str::slug($validated['title']), // This creates a slug like "my-new-quiz"
            // --- 3. USE THE VALIDATED DATA, NOT AN EMPTY ARRAY ---
            'content_json' => $validated['content_json'] ?? [
                'questions' => [],
                'traits' => [],
                'archetypes' => [],
            ]
        ]);

        return redirect()->route('quizzes.edit', $lesson);
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
            // FIX: Ensure is_published is validated as a boolean
            'is_published' => 'required|boolean', 
        ]);

        // Map the frontend 'quiz' or 'personality_quiz' data to the 'content_json' column
        if ($quiz->type === 'personality_quiz') {
            $validated['content_json'] = $validated['personality_quiz'];
        } else {
            $validated['content_json'] = $validated['quiz'];
        }

        unset($validated['quiz']);
        unset($validated['personality_quiz']);
        
        // FIX: Explicitly map the boolean value for is_published
        $quiz->is_published = $validated['is_published'];
        
        $quiz->update($validated);

        return back()->with('success', 'Quiz updated successfully');
    }

    public function destroy(Lesson $quiz)
    {
        $quiz->delete();
        return redirect()->route('dashboard');
    }

    /**
     * Searches for users to enroll in a quiz.
     * * NOTE: This method is placed here for demonstration, but based on your
     * routes/web.php file, this should ideally be in CourseEnrollmentController.php.
     */
    public function searchUsersForEnrollment(Request $request, Lesson $quiz)
    {
        // For security, ensure the authenticated user owns or can manage this quiz
        if ($quiz->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized to manage enrollment for this quiz.');
        }

        $search = $request->input('search');

        // Start with all users except the authenticated one (the teacher/admin)
        $query = User::query()
            ->where('id', '!=', $request->user()->id); 
        
        // Apply search filter if a term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Fetch results, limiting the list for performance on the search dropdown
        $users = $query->limit(50)->get(['id', 'name', 'email']);

        return response()->json(['users' => $users]);
    }
}