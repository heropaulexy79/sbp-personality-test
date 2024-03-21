<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    // Only allow if user is enrolled

    public function showCourse(Request $request, Course $course)
    {
        return redirect(route('classroom.lesson.index', ["course" => $course->id]));
    }

    public function showLessons(Request $request, Course $course)
    {

        $user = $request->user();

        $lastCompletedLesson = UserLesson::where('user_id', $user->id)
            ->where('completed', 1)
            ->join('lessons', 'user_lessons.lesson_id', '=', 'lessons.id')
            ->select('user_lessons.*', 'lessons.course_id', 'lessons.is_published', 'lessons.position')
            ->where('lessons.is_published', 1)
            ->where('lessons.course_id', $course->id)
            ->orderBy('lessons.position', 'desc')
            ->first();


        $redirectLesson = $lastCompletedLesson->id ?? $course->lessons()->first(); // Redirect to 1st if none completed

        return redirect(route('classroom.lesson.show', ["course" => $course->id, "lesson" => $redirectLesson->id]));
    }


    public function showLesson(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();

        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
        }

        $lessons = $course->lessons()->published()->get(['title', 'position', 'type', 'id']);

        foreach ($lessons as $l) {
            $l->completed = UserLesson::where('user_id', $user->id)
                ->where('lesson_id', $l->id)
                ->where('completed', 1)->exists() ?? false;
        };

        $lesson->completed = UserLesson::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->where('completed', 1)->exists() ?? false;

        return Inertia::render('Classroom/Lesson', [
            'course' => $course,
            'lessons' => $lessons,
            'lesson' => $lesson,
        ]);
    }



    public function markLessonComplete(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();

        // $user_lesson = UserLesson::where('user_id', $user->id)
        // ->where('lesson_id', $lessonId)
        // ->first();

        UserLesson::upsert(
            [["user_id" => $user->id, "lesson_id" => $lesson->id, "completed" => true],],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed']
        );

        $next_lesson_id = $request->query('next') ?? $lesson->id;

        return redirect(route('classroom.lesson.show', ['course' => $course->id, 'lesson' => $next_lesson_id]));
    }


    public function answerQuiz(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();


        $score = 0.0;

        // Calculate score

        // Todo : Quiz, lesson
        UserLesson::upsert(
            [["user_id" => $user->id, "lesson_id" => $lesson->id, "quiz" => true],],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['quiz', 'score']
        );

        return redirect()->back()->with("message", [
            "status" => "success",
            "message" => "",
            "score" => $score
        ]);
    }
}
