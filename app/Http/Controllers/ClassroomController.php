<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\UserLesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    // Only allow if user is enrolled

    public function showCourse(Request $request, Course $course)
    {
        return redirect(route('classroom.lesson.index', ['course' => $course->slug]));
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

        return redirect(route('classroom.lesson.show', ['course' => $course->slug, 'lesson' => $redirectLesson->slug]));
    }


    public function showLesson(Request $request, Course $course, Lesson $lesson)
    {

        $user = $request->user();

        $temp_content_json = $lesson->content_json;

        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
        }

        $lessons = $course->lessons()->published()->orderBy('position')
            ->with(['user_lesson' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get(['title', 'position', 'type', 'id', 'slug',]);
        $total_completed = 0;

        // foreach ($lessons as $l) {
        //     $l->completed = UserLesson::where('user_id', $user->id)
        //         ->where('lesson_id', $l->id)
        //         ->where('completed', 1)->exists() ?? false;

        //     if ($l->completed) {
        //         $total_completed++;
        //     }
        // };
        foreach ($lessons as $l) {
            $l->completed = $l->user_lesson->first()?->completed === 1;


            if ($l->completed) {
                $total_completed++;
            }
        };




        // dd($lessons);

        if ($total_completed === count($lessons)) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($enrollment) {
                $enrollment->is_completed = true;
                $enrollment->save();
            }
        }

        $user_lesson = $lessons->filter(function ($item) use ($lesson) {
            return $item['id'] === $lesson->id;  // Strict comparison with ===
        })->first()->user_lesson->first();


        $lesson->completed = $user_lesson?->completed === 1;
        $lesson->answers = $user_lesson?->answers ?? null;

        if ($lesson->completed) {
            $lesson->content_json = $temp_content_json;
        }

        $lessons->makeHidden('user_lesson');

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
            [['user_id' => $user->id, 'lesson_id' => $lesson->id, 'completed' => true],],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed']
        );

        // TODO: MOve complete enrollment here?

        $next_lesson_id = $request->query('next') ?? $lesson->slug;

        return redirect(route('classroom.lesson.show', ['course' => $course->slug, 'lesson' => $next_lesson_id]));
    }


    public function answerQuiz(Request $request, Course $course, Lesson $lesson)
    {
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array|min:0',
            'answers.*.question_id' => 'required|string',
            'answers.*.selected_option' => 'nullable|string',
        ]);

        $score = 0.0;
        $total = 0.0;

        // Calculate score
        $quiz = $lesson->content_json;
        $answers = $request->input('answers');

        foreach ($answers as $key => $value) {
            $v = array_search($value['question_id'],  array_column($quiz, 'id'));
            if ($v === false) {
                continue;
            }
            $question = $quiz[$v];

            if (!$question) {
                continue;
            }

            if ($question['type'] === 'single_choice') {
                $total++;

                if ($question['correct_option'] === $value['selected_option']) {
                    $score++;
                }
            }
        }


        $scoreInPercent = ($score / $total) * 100;

        // Todo : Quiz, lesson
        UserLesson::upsert(
            [[
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'answers' => json_encode($answers),
                'score' => $scoreInPercent
            ]],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed', 'score', 'answers']
        );

        return redirect()->back()->with('message', [
            'status' => 'success',
            'message' => 'You scored ' . $score . ' out of ' . $total,
            'score' => $scoreInPercent
        ]);
    }
}
