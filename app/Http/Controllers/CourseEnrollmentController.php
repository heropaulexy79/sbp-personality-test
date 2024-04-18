<?php

namespace App\Http\Controllers;

// use App\Models\CourseEnrollment;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseEnrollmentController extends Controller
{

    // TODO MOVE TO Namespaced Organisation/Course
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        $enrolledUsers  = $course->enrolledUsers()->where('organisation_id', $user->organisation_id)->get();

        if (!$enrolledUsers || count($enrolledUsers) < 1) {
            abort(404);
        }

        $students = [];

        foreach ($enrolledUsers as $eu) {
            $userScores = $eu->lessons()
                ->select('user_lessons.score') // Select specific columns
                ->get();

            // dd(array_map(fn ($item) => $item->score, $userScores));

            $students[] = [
                'user' => $eu->only(['id', 'name']),
                'score' => $userScores->sum('score'),
                // 'score' => array_sum(array_map(fn($item)=>$item->score, $userScores)), // Array of lesson scores for the user
                'scores' => $userScores, // Array of lesson scores for the user
            ];
        }

        return Inertia::render('Organisation/Course/Leaderboard', [
            "students" => $students,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        $user = $request->user();

        // TODO: if course is public
        if (!$course->is_published || ($course->organisation_id !== $user->organisation_id)) {
            abort(401);
        }

        if (!$course->enrolledUsers->contains($user)) {
            $course->enrolledUsers()->attach($user);

            // return response()->json(['message' => 'User successfully enrolled in the course!'], 201);
            return redirect()->back()->with(['global:message' => [
                'status' => 'success',
                'message' => 'User successfully enrolled in the course!',
            ]], 201);
            //return redirect(route('classrom'))
        } else {
            //return redirect(route('classrom'))
            // return response()->json(['message' => 'User is already enrolled in this course.'], 422);
        }
    }
}
