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
        $redirectLesson = $course->lessons()->first();

        return redirect(route('classroom.lesson.show', ['course' => $course->slug, 'lesson' => $redirectLesson->slug]));
    }


    public function showLesson(Request $request, Course $course, Lesson $lesson)
    {
        if ($lesson->type === Lesson::TYPE_QUIZ) {
            $lesson->content_json = $lesson->quizWithoutCorrectAnswer();
        }

        $lessons = $course->lessons()
            ->published()
            ->orderBy('position')
            ->get(['title', 'position', 'type', 'id', 'slug',]);


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

        return Inertia::render('Classroom/Lesson', [
            'course' => $course,
            'lessons' => $lessons,
            'lesson' => $lesson,
        ]);
    }

    public function showCompleted(Request $request, Course $course)
    {
        $user = $request->user();
        $lessons = $course->lessons()->published()
            ->where('type', '!=', Lesson::TYPE_PERSONALITY_QUIZ)
            ->orderBy('position')
            ->get(['title', 'position', 'type', 'id', 'slug',]);


        return Inertia::render('Classroom/Completed', [
            'course' => $course,
            'lessons' => $lessons,
            'progress' => 100,
            'completed_lessons' => $lessons->count(),
            'total_score' => 0,
        ]);
    }



    public function markLessonComplete(Request $request, Course $course, Lesson $lesson)
    {
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
