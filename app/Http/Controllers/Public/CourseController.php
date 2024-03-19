<?php

namespace App\Http\Controllers\Public;

use App\Models\Course;
use App\Http\Controllers\Controller;
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

        dd($courses);

        return Inertia::render('Course/Index', [
            'courses' => $courses,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        // If it is public allow?
        if (!$course->is_published) {
            abort(404);
        }

        dd($course);

        return Inertia::render('Course/View', [
            'course' => $course,
        ]);
    }
}
