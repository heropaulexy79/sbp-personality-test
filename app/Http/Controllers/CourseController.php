<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{

    private function authorizeGuard(User $user, Organisation $organisation, Course $course)
    {
        if (
            !$user->isAdminInOrganisation($organisation) ||
            $user->organisation_id !== $course->organisation_id
        ) {
            return abort(401, "You don't have permission to make this request");
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // TODO: Make a public route ?
        $user = $request->user();
        $organisation = $user->organisation;

        if (!$organisation || !$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }


        return Inertia::render('Organisation/Course/Index', [
            'courses' => $organisation->courses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Organisation $organisation)
    {
        //
        $user = $request->user();
        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        return Inertia::render('Course/Create', [
            'organisation' => $organisation,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Organisation $organisation)
    {
        //
        $user = $request->user();
        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(404);
        }

        $request->validate([
            "title" => "required|string|max:255",
            'description' => "string|max:1500",
        ]);

        // dd($organisation);

        $course = Course::create([
            "title" => $request->input("title"),
            "description" => $request->input("description"),
            "organisation_id" => $organisation->id,
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

        if (!$user->isMemberOfOrganisation($organisation) || $organisation->id !== $course->organisation_id) {
            return abort(404);
        }

        return Inertia::render('Organisation/Course/View', [
            'organisation' => $user->organisation,
            'course' => $course,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Course $course)
    {
        //

        $user = $request->user();


        if (!$user->isAdminInOrganisation($user->organisation)) {
            return abort(404);
        }

        return Inertia::render('Course/Edit', [
            'organisation' => $user->organisation,
            'course' => $course,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organisation $organisation, Course $course)
    {
        //
        $user = $request->user();

        if (!$user->isAdminInOrganisation($organisation)) {
            return abort(401);
        }

        $request->validate([
            'title' => "required|string|max:255",
            'description' => "string|max:1500",
        ]);

        $course->title = $request->title;
        $course->description = $request->description;

        $course->save();

        return $request->back()->with('message', ["status" => "error", "message" => "Changes saved!"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Organisation $organisation, Course $course)
    {
        //
        $user = $request->user();

        $this->authorizeGuard($user, $organisation, $course);

        $course->delete();

        return redirect()->back();
        // return redirect(route('dashboard'));
    }
}
