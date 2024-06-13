<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // TODO: Make a public route ?
        // $user = $request->user();

        // $courses = $user->createdCourses();

        // return Inertia::render('Teacher/Course/Index', [
        //     'courses' => $courses,
        // ]);

        return to_route('dashboard');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $user = $request->user();

        if ($user->account_type !== 'TEACHER') {
            return abort(404);
        }

        return Inertia::render('Teacher/Course/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $user = $request->user();

        if ($user->account_type !== 'TEACHER') {
            return abort(404);
        }



        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|max:1500',
            'slug' => 'required|string|unique:courses,slug',
            'banner_image' => 'string|nullable|max:255',
        ]);

        // dd($organisation);

        $course = Course::create([
            'slug' => $request->input('slug'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'teacher_id' => $user->id,
            'banner_image' => $request->input('banner_image'),
        ]);

        // Redirect to course lesson management
        return redirect(route('course.show', [
            'course' => $course->id,
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Course $course)
    {
        //
        $user = $request->user();

        if ($user->account_type !== 'TEACHER' || $user->id !== $course->teacher_id) {
            return abort(404);
        }

        $course->lessons = $course->lessons->makeHidden(['content', 'content_json']);
        $course->lessons = $course->lessons->sortBy('position');



        return Inertia::render('Teacher/Course/View', [
            'course' => $course->withoutRelations(),
            'lessons' => $course->lessons->values()->all(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Course $course)
    {
        //

        $user = $request->user();

        if ($user->account_type !== 'TEACHER' || $user->id !== $course->teacher_id) {
            return abort(404);
        }

        return Inertia::render('Teacher/Course/Edit', [
            'course' => $course,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        //
        $user = $request->user();
        $organisation = $user->organisationNew->organisation;

        if ($user->account_type !== 'TEACHER' || $user->id !== $course->teacher_id) {
            return abort(401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|max:1500',
            'banner_image' => 'string|nullable|max:255',
        ]);

        $course->title = $request->title;
        $course->description = $request->description;
        if ($request->is_published === 'true' && $course->lessons->count() < 1) {
            return redirect(400)->back()->with('message', ['status' => 'error', 'message' => 'Course cannot be published without any lessons']);
        }
        $course->is_published = $request->is_published === 'true' ? true : false;
        $course->banner_image = $request->banner_image ?? $course->banner_image;

        $course->save();

        return redirect()->back()->with('message', ['status' => 'success', 'message' => 'Changes saved!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Course $course)
    {
        //
        $user = $request->user();

        if ($user->account_type !== 'TEACHER' || $user->id !== $course->teacher_id) {
            return abort(401);
        }

        $course->delete();

        return redirect()->back();
        // return redirect(route('dashboard'));
    }
}
