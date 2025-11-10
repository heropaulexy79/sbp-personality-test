<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Course $course)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('update', $organisation)) {
            return abort(404);
        }

        return Inertia::render('Organisation/Course/Lesson/Create', [
            'course' => $course,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request, Course $course)
    {
        $lesson = new Lesson;

        $lesson->title = $request->input('title');
        $lesson->course_id = $course->id;
        $lesson->slug = $request->input('slug');
        $lesson->is_published = $request->input('is_published', false);

        $lesson->type = $request->input('type', Lesson::TYPE_DEFAULT);

        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $request->quiz;
        } elseif ($lesson->type === Lesson::TYPE_PERSONALITY_QUIZ) {
            $lesson->content_json = $request->personality_quiz;
        } else {
            $lesson->content = $request->content;
        }

        $lesson->save();

        return redirect(route('course.show', [
            'course' => $course->id,
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('view', $organisation) || $organisation->id !== $course->organisation_id) {
            return abort(404);
        }

        return Inertia::render('Organisation/Course/Lesson/View', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        $lesson->load('course');

        return Inertia::render('Organisation/Course/Lesson/Edit', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreLessonRequest $request, Lesson $lesson)
    {
        $validated = $request->validated();

        if (isset($validated['title']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        if (isset($validated['type'])) {
            if ($validated['type'] === Lesson::TYPE_DEFAULT) {
                $validated['content_json'] = null;
            } elseif ($validated['type'] === Lesson::TYPE_QUIZ) {
                $validated['content'] = null;
                $validated['content_json'] = $validated['quiz'] ?? [];
            } elseif ($validated['type'] === Lesson::TYPE_PERSONALITY_QUIZ) {
                $validated['content'] = null;
                $validated['content_json'] = $validated['personality_quiz'] ?? ['traits' => [], 'questions' => []];
            }
        }

        $lesson->update($validated);

        return redirect()->route('lesson.edit', $lesson->id)
                         ->with('success', 'Lesson updated successfully.');
    }

    public function updatePosition(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();
        $organisation = $user->organisation();

        if ($user->cannot('view', $organisation) || $organisation->id !== $course->organisation_id) {
            return abort(404);
        }

        $request->validate(['position' => 'numeric']);

        $lesson->position = $request->input('position');
        $lesson->save();

        if ($request->acceptsJson()) {
            return response()->json([
                "message" => [
                    "status" => "success",
                    "message" => "Order has been updated",
                ]
            ]);
        }

        return redirect()->back()->with('global:message', [
            'status' => 'success',
            'message' => 'Changes have been saved!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $courseId = $lesson->course_id;
        $lesson->delete();

        if ($courseId) {
            return redirect()->route('course.edit', [
                'course' => $courseId,
                'tab' => 'lessons',
            ])->with('success', 'Lesson deleted.');
        }

        return redirect()->route('dashboard')->with('success', 'Lesson deleted.');
    }
}
