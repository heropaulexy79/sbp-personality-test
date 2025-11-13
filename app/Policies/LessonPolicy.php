<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LessonPolicy
{
    /**
     * Allow super-admins to bypass all checks related to the Lesson model.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    /**
     * Determine whether the user can update the model (lesson/quiz).
     * This method covers the authorization check used in CourseEnrollmentController for quiz enrollment.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        // Teachers should be able to update lessons and quizzes they own.
        return $user->id === $lesson->user_id;
    }

    /**
     * Determine whether the user can enroll students in the quiz.
     * This is often mapped to the 'update' ability.
     */
    public function enroll(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}