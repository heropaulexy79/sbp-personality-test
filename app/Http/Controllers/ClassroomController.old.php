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

    public function showLessonPublic(Request $request, Course $course, Lesson $lesson)
    {
        $temp_content_json = $lesson->content_json;

        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
        }

        $lessons = $course->lessons()->published()->orderBy('position')
            ->get(['title', 'position', 'type', 'id', 'slug',]);
        $total_completed = 0;


        foreach ($lessons as $l) {
            $l->completed = $l->user_lesson->first()?->completed === 1;


            if ($l->completed) {
                $total_completed++;
            }
        };


        if ($lesson->completed) {
            $lesson->content_json = $temp_content_json;
        }


        return Inertia::render('OpenClassroom/Lesson', [
            'course' => $course,
            'lessons' => $lessons,
            'lesson' => $lesson,
        ]);
    }

    public function showCompleted(Request $request, Course $course)
    {

        $user = $request->user();
        $lessons = $course->lessons()->published()->orderBy('position')
            ->with(['user_lesson' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get(['title', 'position', 'type', 'id', 'slug',]);
        $total_completed = 0;

        foreach ($lessons as $l) {
            $l->completed = $l->user_lesson->first()?->completed === 1;
            if ($l->completed) {
                $total_completed++;
            }
        };

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
        if ($total_completed === count($lessons)) {

            if ($enrollment) {
                $enrollment->is_completed = true;
                $enrollment->save();
            }
        }


        $lessons->makeHidden('user_lesson');


        // if (!$enrollment->is_completed) {
        //     return redirect(route('classroom.lesson.index', ['course' => $course->slug]));
        // }

        // dd($enrollment);
        $userScores = $user->lessons()
            ->with('lesson')
            ->whereHas('lesson', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->select('user_lessons.score') // Select specific columns
            ->get();




        return Inertia::render('Classroom/Completed', [
            'course' => $course,
            'lessons' => $lessons,
            'enrollment' => $enrollment,
            'progress' => ($total_completed / count($lessons) * 100),
            'completed_lessons' => $total_completed,
            'total_score' => $userScores->sum('score'),
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
            'answers.*.selected_option_id' => 'nullable|string',
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

                if ($question['correct_option'] === $value['selected_option_id']) {
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

    public function answerPersonalityQuiz(Request $request, Course $course, Lesson $lesson)
    {
        if ($lesson->type !== Lesson::TYPE_PERSONALITY_QUIZ) {
            return redirect()->back()->with('message', [
                'status' => 'error',
                'message' => 'This is not a personality quiz lesson.',
            ]);
        }

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|string',
            'answers.*.selected_option_id' => 'required|string',
        ]);

        $user = $request->user();
        $userAnswers = $request->input('answers');


        $quizData = $lesson->getPersonalityQuizData();
        $personalityQuestions = collect($quizData['questions']);
        $personalityTraits = collect($quizData['traits']);


        $calculatedTraitScores = []; // Holds sum of scores for each trait
        $traitQuestionCounts = []; // Holds count of questions that contributed to each trait


        foreach ($personalityTraits as $trait) {
            $calculatedTraitScores[$trait['id']] = 0;
            $traitQuestionCounts[$trait['id']] = 0;
        }

        // 3. Calculate Scores
        foreach ($userAnswers as $userAnswer) {
            $questionId = $userAnswer['question_id'];
            $selectedOptionId = $userAnswer['selected_option_id'];

            $question = $personalityQuestions->firstWhere('id', $questionId);

            if (!$question) {
                // Log or handle case where question is not found (e.g., tampered data)
                continue;
            }


            $selectedOption = collect($question['options'])->firstWhere('id', $selectedOptionId);

            if (!$selectedOption || !isset($selectedOption['scores']) || !is_array($selectedOption['scores'])) {
                // Log or handle case where option is not found or has no scores
                continue;
            }


            foreach ($selectedOption['scores'] as $traitId => $scoreValue) {
                if (isset($calculatedTraitScores[$traitId])) { // Ensure the trait ID is valid
                    $calculatedTraitScores[$traitId] += (float) $scoreValue; // Add score
                    // Only count if this question actually contributes to the trait's score
                    // This is important for averaging, as some questions might not impact all traits
                    if ((float) $scoreValue !== 0 || count($selectedOption['scores']) === 1) { // Count non-zero contributions, or if it's the only score for an option
                        $traitQuestionCounts[$traitId]++;
                    }
                }
            }
        }


        $finalPersonalityResults = [];
        foreach ($calculatedTraitScores as $traitId => $sumScore) {
            $count = $traitQuestionCounts[$traitId];
            $averageScore = $count > 0 ? ($sumScore / $count) : 0;
            // Ensure score stays within 0-100 bounds
            $finalPersonalityResults[$traitId] = max(0, min(100, round($averageScore)));
        }


        $answersJson = json_encode($userAnswers);
        $resultsJson = json_encode($finalPersonalityResults);



        UserLesson::upsert(
            [[
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'answers' => $answersJson,
                'score' => null,
                'personality_scores' => $resultsJson,
            ]],
            uniqueBy: ['user_id', 'lesson_id'],
            update: ['completed', 'answers', 'personality_scores']
        );

        // 6. Return Response
        return redirect()->back()->with('message', [
            'status' => 'success',
            'message' => 'Personality quiz completed!',
            'answersJson' => $answersJson,
            'personality_results' => $finalPersonalityResults,
            'personality_traits' => $personalityTraits->toArray(),
        ]);
    }
}
