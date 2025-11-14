<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\CourseEnrollment; // Essential for finding enrolled quizzes
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection; // ADDED: Import Collection class

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     */
    public function index(Request $request) // Renamed from __invoke to index to resolve routing error
    {
        $user = $request->user();

        // Determine if the user has a teacher/admin role (or custom logic)
        // Assuming a 'teacher' account type or similar logic is used to define a teacher.
        $isTeacher = $user->account_type === 'teacher'; // Assuming User model has 'account_type'

        // 1. Quizzes I Manage (Quizzes created by the user)
        $managedQuizzes = Lesson::query()
            ->where('user_id', $user->id)
            ->where('type', 'personality_quiz')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Lesson $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'slug' => $quiz->slug,
                'type' => $quiz->type,
                'is_published' => (bool) $quiz->is_published,
                // Assuming userLessons() relationship exists to count responses
                'total_responses' => $quiz->userLessons()->count()
            ]);

        // 2. Quizzes I Can Take (Enrolled by the user, excluding managed quizzes for initial clean list)
        $enrolledQuizzes = Lesson::query()
            // FIX 1: Changed 'lessons->is_published' to 'lessons.is_published'
            ->select('lessons.id', 'lessons.title', 'lessons.slug', 'lessons.type', 'lessons.is_published')
            ->join('course_enrollments', 'lessons.id', '=', 'course_enrollments.course_id')
            ->where('course_enrollments.user_id', $user->id)
            ->where('lessons.type', 'personality_quiz')
            // Exclude owned quizzes to prevent double fetching, we will merge the managed ones back in later
            ->where('lessons.user_id', '!=', $user->id) 
            ->orderByDesc('lessons.created_at')
            ->distinct()
            ->get()
            ->map(fn (Lesson $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'slug' => $quiz->slug,
                'type' => $quiz->type,
                'is_published' => (bool) $quiz->is_published,
            ]);

        // Combine all enrolled quizzes (including the ones the user manages)
        // FIX: The ->map() call above returns an array, so we must wrap the array in a Collection 
        // using Collection::make() before calling collection methods like merge(), only(), unique(), etc.
        $allEnrolledQuizzes = Collection::make($enrolledQuizzes)->merge(
            Collection::make($managedQuizzes)->map(fn($q) => collect($q)->only(['id', 'title', 'slug', 'type', 'is_published']))
        )->unique('id')->sortByDesc('created_at')->values();


        // 3. Courses I Manage (Created by the user)
        $managedCourses = Course::query()
            ->where('user_id', $user->id)
            ->withCount('enrollments')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'is_published' => (bool) $course->is_published,
                'banner_image' => $course->banner_image,
                'lessons_count' => $course->lessons()->count(),
                'enrollments_count' => $course->enrollments_count,
            ]);

        // 4. Courses I Am Enrolled In (Enrolled by the user)
        $enrolledCourses = Course::query()
            ->select('courses.*')
            ->join('course_enrollments', 'courses.id', '=', 'course_enrollments.course_id')
            // FIX 2: Changed 'course_enrollments->user_id' to 'course_enrollments.user_id' to fix JSON extraction error
            ->where('course_enrollments.user_id', $user->id) 
            ->where('courses.is_published', true)
            ->orderByDesc('courses.created_at')
            ->distinct()
            ->get()
            ->map(function (Course $course) use ($user) {
                // Determine completion status
                $enrollment = CourseEnrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first();

                // Calculate progress
                $totalLessons = $course->lessons()->count();
                $completedLessons = $course->lessons()
                    ->whereHas('userLessons', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where('is_completed', true);
                    })
                    ->count();

                // FIX: Check if $totalLessons is > 0 to prevent division by zero (resulting in NaN)
                $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'banner_image' => $course->banner_image,
                    'is_completed' => $enrollment ? (bool) $enrollment->is_completed : false,
                    'progress' => $progress,
                    'lessons_count' => $totalLessons,
                ];
            });


        return Inertia::render('Dashboard', [
            // ADDED: Pass the isTeacher flag
            'isTeacher' => $isTeacher, 
            // FIX: Use expected prop names for quizzes
            'managedQuizzes' => $managedQuizzes,
            'enrolledQuizzes' => $allEnrolledQuizzes, // Now sends the complete list
            'managedCourses' => $managedCourses,
            'enrolledCourses' => $enrolledCourses,
        ]);
    }

    /**
     * TEMPORARY TEST METHOD: Display the public quiz for the authenticated user.
     * This bypasses PublicClassroomController to confirm basic routing/view loading.
     */
    public function showPublicQuizTest(Request $request, Lesson $lesson)
    {
        // Check if the user is enrolled or is the creator (to ensure access/data completeness)
        $isEnrolled = CourseEnrollment::where('user_id', $request->user()->id)
                                    ->where('course_id', $lesson->id)
                                    ->exists();

        if (!$isEnrolled && $lesson->user_id !== $request->user()->id) {
            // Optional: Redirect or show an error if not enrolled
             return redirect()->route('dashboard')->with('error', 'You are not enrolled in this quiz.');
        }

        // Return the Quiz view, which handles the rendering based on type
        return Inertia::render('Classroom/PersonalityQuiz', [
            'lesson' => $lesson->only([
                'id',
                'title',
                'slug',
                'type',
                'content_json', // Essential data for the quiz builder
                'is_published'
            ]),
        ]);
    }
}