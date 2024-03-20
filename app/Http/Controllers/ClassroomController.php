<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    // Only allow if user is enrolled

    public function showCourse(Request $request, Course $course)
    {
        // TODO: ?? Is this page necessary
        dd($course);
    }

    public function showLessons(Request $request, Course $course)
    {
        // dd($course);

        // TODO:Redirect to last completed lesson page or  1st lesson page
        return redirect(route('classroom.lesson.show', ["course" => $course->id, "lesson" => 1]));
    }


    public function showLesson(Request $request, Course $course, Lesson $lesson)
    {


        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
        }

        return Inertia::render('Classroom/Lesson', [
            'course' => $course,
            'lessons' => $course->lessons()->published()->get(['title', 'position', 'type', 'id']),
            'lesson' => $lesson,
        ]);
    }
}
