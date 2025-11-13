<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\CourseEnrollment;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard, showing created quizzes and enrolled quizzes.
     */
    public function index()
    {
        $user = Auth::user();
        
        // 1. Get Quizzes Created by the User (Teacher View)
        $createdQuizzes = Lesson::query()
            ->where('user_id', $user->id)
            ->where('type', 'personality_quiz')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'slug', 'is_published'])
            ->map(fn (Lesson $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'slug' => $quiz->slug,
                'is_published' => (bool) $quiz->is_published,
            ]);

        // 2. Get Quizzes the User is Enrolled In (Student View)
        // Note: CourseEnrollment uses 'course_id' which maps to Lesson ID for quizzes.
        $enrolledQuizIds = CourseEnrollment::where('user_id', $user->id)
                                          ->pluck('course_id');

        $enrolledQuizzes = Lesson::query()
            ->whereIn('id', $enrolledQuizIds)
            ->where('is_published', true) // Only show published quizzes to students
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->map(fn (Lesson $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'slug' => $quiz->slug,
            ]);

        // FIX: Determine if the user has a "Teacher" role based on created quizzes
        // This ensures a user sees the teacher section if they have created content.
        $hasCreatedQuizzes = $createdQuizzes->isNotEmpty();
        
        // We will default isTeacher to true if they have created quizzes, or fall back
        // to checking account_type/role if no quizzes are created (original logic, simplified).
        // Since you are the creator, this will almost certainly be true.
        $isTeacher = $hasCreatedQuizzes ?: (
            property_exists($user, 'account_type') 
            ? ($user->account_type === 'teacher' || $user->account_type === 'admin')
            : (property_exists($user, 'role') 
                ? ($user->role === 'teacher' || $user->role === 'admin') 
                : true
            )
        );

        return Inertia::render('Dashboard', [
            'createdQuizzes' => $createdQuizzes,
            'enrolledQuizzes' => $enrolledQuizzes,
            'isTeacher' => $isTeacher, 
        ]);
    }
}