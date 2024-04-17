<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $courses = Course::where('is_published', true)->paginate();

        // dd($courses);

        return Inertia::render('Course/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Course $course)
    {

        $user = $request->user();

        // If it is public allow?
        if (!$course->is_published) {
            abort(404);
        }

        // dd($course->enrolledUsers()->count());
        // dd($course->lessons()->published()->get());

        return Inertia::render('Course/View', [
            'course' => $course,
            'enrolled_count' => $course->enrolledUsers()->count(),
            'lessons' => $course->lessons()->published()->get(['title', 'position']),
        ]);
    }
}
