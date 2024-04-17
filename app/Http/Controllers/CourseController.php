<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Organisation;
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
        $user = $request->user();
        $organisation = $user->organisation;

        if (!$organisation || !$user->isMemberOfOrganisation($organisation)) {
            return abort(404);
        }

        // dd($organisation->courses->where('is_published', '1'));
        // dd($user->role === "ADMIN" ? $organisation->courses : $organisation->course->where('is_published', '1')->get());

        $courses = $user->role === "ADMIN" ? $organisation->courses : $organisation->courses->where('is_published', '1');

        return Inertia::render('Organisation/Course/Index', [
            'courses' => $courses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $user = $request->user();
        $organisation = $user->organisation;

        if ($request->user()->cannot('create', Organisation::class)) {
            return abort(404);
        }

        return Inertia::render('Organisation/Course/Create', [
            'organisation' => $organisation,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $user = $request->user();
        $organisation = $user->organisation;

        if ($request->user()->cannot('update', $organisation)) {
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
            'organisation_id' => $organisation->id,
            'banner_image' => $request->input('banner_image'),
        ]);

        // Redirect to course lesson management
        return redirect(route('course.show', [
            'course' => $course->id,
            // 'organisation' => $organisation->id
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Course $course)
    {
        //
        $user = $request->user();
        $organisation = $user->organisation;

        if ($request->user()->cannot('view', $user->organisation) || $organisation->id !== $course->organisation_id) {
            return abort(404);
        }

        $course->lessons = $course->lessons->makeHidden(['content', 'content_json']);
        $course->lessons = $course->lessons->sortBy('position');

        // dd(

        // );

        return Inertia::render('Organisation/Course/View', [
            'organisation' => $user->organisation,
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

        if ($request->user()->cannot('update', $user->organisation)) {
            return abort(404);
        }

        return Inertia::render('Organisation/Course/Edit', [
            'organisation' => $user->organisation,
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
        $organisation = $user->organisation;

        if ($request->user()->cannot('update', $organisation)) {
            return abort(401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|max:1500',
            'banner_image' => 'string|nullable|max:255',
        ]);

        $course->title = $request->title;
        $course->description = $request->description;
        $course->is_published = $request->is_published === 'true' ? true : false;
        $course->banner_image = $request->banner_image ?? $course->banner_image;

        $course->save();

        return redirect()->back()->with('message', ['status' => 'error', 'message' => 'Changes saved!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Course $course)
    {
        //
        $user = $request->user();
        $organisation = $user->organisation;

        if ($request->user()->cannot('delete', $organisation)) {
            return abort(401);
        }

        $course->delete();

        return redirect()->back();
        // return redirect(route('dashboard'));
    }
}
