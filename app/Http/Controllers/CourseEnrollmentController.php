<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User; // 1. Import the User model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth; // 2. Import Auth

class CourseEnrollmentController extends Controller
{
    /**
     * Display the enrollment management page for a specific course.
     * This is the new 'edit' method for our route.
     * @param  \App\Models\Course  $quiz  The Course model instance.
     */
    public function edit(Request $request, Course $quiz) // FIX: Changed $course to $quiz
    {
        // 3. Optional: Ensure only the quiz creator can enroll users
        //    (Uncomment if you want a security check)
        // if ($quiz->user_id !== Auth::id()) { // FIX: Changed $course to $quiz
        //     abort(403);
        // }

        // 4. Get all users to display on the page
        //    We only select id, name, and email for efficiency.
        $allUsers = User::select('id', 'name', 'email')->get();

        // 5. Get a simple list (an array) of IDs for users
        //    who are ALREADY enrolled in this course.
        $enrolledUserIds = CourseEnrollment::where('course_id', $quiz->id) // FIX: Changed $course to $quiz
            ->pluck('user_id')
            ->all();

        // 6. Return the new 'Enroll' page, passing in the course,
        //    all users, and the list of enrolled IDs as props.
        return Inertia::render('Quiz/Enroll', [
            'course' => $quiz, // FIX: Changed $course to $quiz (prop name stays 'course' as expected by Vue)
            'allUsers' => $allUsers,
            'enrolledUserIds' => $enrolledUserIds,
        ]);
    }

    /**
     * Update the enrollment status for a specific course.
     * This is the new 'update' method for our route.
     * * @param  \App\App\Http\Controllers\Request  $request
     * @param  \App\Models\Course  $quiz  The Course model instance.
     */
    public function update(Request $request, Course $quiz) // FIX: Changed $course to $quiz
    {
        // Optional: Add the same authorization check as in edit()
        // if ($quiz->user_id !== Auth::id()) { // FIX: Changed $course to $quiz
        //     abort(403);
        // }

        // 7. Validate the incoming data
        $validated = $request->validate([
            // We expect an array of user IDs, even if it's empty
            'user_ids' => 'present|array',
            'user_ids.*' => 'integer|exists:users,id', // Ensure every ID exists
        ]);

        // 8. Sync the enrollments
        // - Get all user IDs from the request.
        // - Map them into the format needed for CourseEnrollment.
        $enrollments = collect($validated['user_ids'])->map(function ($userId) use ($quiz) { // FIX: Changed $course to $quiz
            return [
                'user_id' => $userId,
                'course_id' => $quiz->id, // FIX: Changed $course to $quiz
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // 9. Start a database transaction for safety
        \DB::transaction(function () use ($quiz, $enrollments) { // FIX: Changed $course to $quiz
            // 10. First, remove ALL existing enrollments for this course.
            CourseEnrollment::where('course_id', $quiz->id)->delete(); // FIX: Changed $course to $quiz

            // 11. Second, insert all the new enrollments from the form.
            if ($enrollments->isNotEmpty()) {
                CourseEnrollment::insert($enrollments->all());
            }
        });

        // 12. Redirect back to the enrollment page with a success message
        
        return to_route('quizzes.enroll', ['quiz' => $quiz->slug])->with('success', 'Enrollment updated successfully!');
    }


    /**
     * Store a new enrollment for a user.
     * This 'storeAll' method seems to be for a different purpose
     * (like a public enroll button), so we leave it as-is.
     * This method is correct as it's called by the 'course.enroll' route.
     */
    public function storeAll(Request $request, Course $course)
    {
        $user = Auth::user();

        // Check if already enrolled
        $isEnrolled = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($isEnrolled) {
            return to_route('classroom.lesson', [
                'course' => $course->slug,
                'lesson' => $course->lessons->first()->slug,
            ]);
        }

        // Enroll the user
        CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return to_route('classroom.lesson', [
            'course' => $course->slug,
            'lesson' => $course->lessons->first()->slug,
        ]);
    }
}