<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson; // New: To support quiz binding (quizzes/{quiz:slug})
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // New: Import DB facade (used in update)

class CourseEnrollmentController extends Controller
{
    /**
     * Display the enrollment management page for a specific quiz (Lesson).
     * This corresponds to the /quizzes/{quiz:slug}/enroll route.
     * @param  \App\Models\Lesson  $quiz The Lesson model instance (representing the Quiz).
     */
    public function edit(Request $request, Lesson $quiz)
    {
        // Only fetch IDs of already enrolled users. The list of all potential users
        // is now fetched via AJAX in the frontend search component for scalability.
        $enrolledUserIds = CourseEnrollment::where('course_id', $quiz->id)
            ->pluck('user_id')
            ->all();

        // Pass the quiz/lesson object to the frontend. It is aliased as 'course' 
        // in the frontend template.
        return Inertia::render('Quiz/Enroll', [
            'course' => $quiz, 
            'enrolledUserIds' => $enrolledUserIds,
        ]);
    }

    /**
     * Update the enrollment status for a specific quiz (Lesson).
     * This is the new 'update' method for our route.
     * * @param  \App\App\Http\Controllers\Request  $request
     * @param  \App\Models\Lesson  $quiz The Lesson model instance (representing the Quiz).
     */
    public function update(Request $request, Lesson $quiz)
    {
        // Authorization check (optional but recommended for security)
        if ($quiz->user_id !== Auth::id()) {
             abort(403, 'You do not have permission to modify this quiz.');
        }

        // 7. Validate the incoming data
        $validated = $request->validate([
            // We expect an array of user IDs, even if it's empty
            'user_ids' => 'present|array',
            'user_ids.*' => 'integer|exists:users,id', // Ensure every ID exists
        ]);

        // 8. Sync the enrollments
        $enrollments = collect($validated['user_ids'])->map(function ($userId) use ($quiz) {
            return [
                'user_id' => $userId,
                'course_id' => $quiz->id, // Use quiz (lesson) ID as the course_id reference
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        // 9. Start a database transaction for safety
        DB::transaction(function () use ($quiz, $enrollments) {
            // 10. First, remove ALL existing enrollments for this quiz/lesson.
            CourseEnrollment::where('course_id', $quiz->id)->delete();

            // 11. Second, insert all the new enrollments from the form.
            if ($enrollments->isNotEmpty()) {
                CourseEnrollment::insert($enrollments->all());
            }
        });

        // 12. Redirect back to the enrollment page with a success message
        return to_route('quizzes.enroll', $quiz->slug)->with('success', 'Enrollment updated successfully!');
    }

    /**
     * Searches for users to enroll in a quiz via AJAX.
     * This corresponds to the /quizzes/{quiz:slug}/users/search route.
     * * @param \Illuminate\Http\Request $request
     * @param \App\Models\Lesson $quiz
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsersForEnrollment(Request $request, Lesson $quiz)
    {
        // Authorization check: ensure the authenticated user owns or can manage this quiz
        if ($quiz->user_id !== $request->user()->id) {
            // Return empty set instead of aborting the whole AJAX request
            return response()->json(['users' => []], 403);
        }

        $search = $request->input('search');
        $currentUserId = $request->user()->id; // Get the authenticated user ID

        // 1. Fetch currently enrolled users first
        $enrolledUserIds = CourseEnrollment::where('course_id', $quiz->id)
                                           ->pluck('user_id')
                                           ->all();
        
        // Fetch all enrolled users, including the teacher if they are enrolled.
        $enrolledUsers = User::whereIn('id', $enrolledUserIds)
                             ->get(['id', 'name', 'email']);

        $enrolledUserIdsFound = $enrolledUsers->pluck('id')->all();

        // 2. Fetch all other users based on search term
        $query = User::query()
            // FIX: Removed exclusion of the currently logged-in teacher from the search pool
            // We rely on 'whereNotIn' to exclude duplicates later.
            // Old: ->where('id', '!=', $currentUserId)
            // Now we rely solely on excluding duplicates from the search result set to include
            // the teacher if they were not already enrolled but match the search term.
            ->whereNotIn('id', $enrolledUserIdsFound); 
        
        // Apply search filter if a term is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $searchWildcard = '%' . $search . '%';
                $q->where('name', 'like', $searchWildcard)
                  ->orWhere('email', 'like', $searchWildcard);
            });
        }
        
        // Limit the pool of non-enrolled users to search for
        $newlyFoundUsers = $query->limit(50)->get(['id', 'name', 'email']);

        // 3. Combine unique users
        // The teacher will appear here if they weren't in $enrolledUsers and match the search term.
        // We need to ensure the teacher user is in the pool if they exist and are not already in $enrolledUsers.
        $teacherUser = null;
        if (!in_array($currentUserId, $enrolledUserIdsFound)) {
            $teacherUser = User::find($currentUserId, ['id', 'name', 'email']);
        }
        
        $combinedUsers = $enrolledUsers;
        
        if ($teacherUser && ($search === null || $teacherUser->name === $search || $teacherUser->email === $search)) {
            // If search is empty or teacher matches search, add teacher to the list
             $combinedUsers = $combinedUsers->push($teacherUser);
        }
        
        $combinedUsers = $combinedUsers->concat($newlyFoundUsers)->unique('id')->values();

        // 4. Sort the list (enrolled users naturally float to the top due to the concatenation order, 
        // we'll primarily sort by name for the rest)
        $sortedUsers = $combinedUsers->sortBy('name')->values();
        
        return response()->json(['users' => $sortedUsers]);
    }


    /**
     * Store a new enrollment for a user.
     * This 'storeAll' method seems to be for a different purpose
     * (like a public enroll button), so we leave it as-is, retaining the Course model binding.
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