<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment; // 1. Import the model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; // 2. Ensure Auth is imported

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     * We use index() since your file already has it.
     */
    public function index(Request $request)
    {
        // 3. Get the ID of the currently logged-in user
        $userId = Auth::id();

        // 4. Fetch all enrollments for this user
        // We eagerly load the 'course' and its 'lessons' to get the title
        // and the link for the "Take Quiz" button
        $enrollments = CourseEnrollment::where('user_id', $userId)
            ->with(['course' => function ($query) {
                // Ensure lessons are loaded, you might need to order them
                $query->with(['lessons' => function ($lessonQuery) {
                    $lessonQuery->orderBy('id'); // Or by an 'order' column if you have one
                }]);
            }])
            ->get();

        // 5. Extract just the courses from the enrollment records
        // We also find the first lesson for the "Take Quiz" link
        $enrolledCourses = $enrollments->map(function ($enrollment) {
            if ($enrollment->course) {
                // Get the first lesson to build the "Take Quiz" link
                $firstLesson = $enrollment->course->lessons->first();
                $enrollment->course->first_lesson_slug = $firstLesson ? $firstLesson->slug : null;
                return $enrollment->course;
            }
            return null;
        })->filter(); // filter() removes any nulls if a course was deleted

        // 6. Pass the 'enrolledCourses' data as a prop to your new Vue page
        return Inertia::render('Dashboard', [ // This now points to 'Dashboard.vue'
            'enrolledCourses' => $enrolledCourses,
        ]);
    }
}