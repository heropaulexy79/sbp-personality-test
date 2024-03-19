<?php

namespace App\Http\Controllers;

// use App\Models\CourseEnrollment;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
{

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
                "status" => "success",
                "message" => 'User successfully enrolled in the course!'
            ]], 201);
            //return redirect(route('classrom'))
        } else {
            //return redirect(route('classrom'))
            // return response()->json(['message' => 'User is already enrolled in this course.'], 422);
        }
    }
}
