<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicCourseController extends Controller
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
