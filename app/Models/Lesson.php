<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Lesson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'course_id',
        'slug',
        'type',
        'content',
        'content_json',
        'is_published',
        'user_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // We removed 'personality_quiz_data' because $casts makes it redundant
    protected $appends = [];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // This cast automatically handles JSON encoding/decoding
        // and provides a default empty array if the value is null.
        'content_json' => 'array',
        'is_published' => 'boolean',
    ];

    // -------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------

    public const TYPE_DEFAULT = 'default';
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_PERSONALITY_QUIZ = 'personality_quiz';

    // -------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------

    /**
     * Scope a query to only include published lessons.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    // -------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------

    /**
     * Get the course that this lesson belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user lesson records associated with this lesson.
     */
    public function userLesson()
    {
        return $this->hasMany(UserLesson::class);
    }

    /**
     * Get the user who created this lesson.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------
    // Custom Methods
    // -------------------------------------------------------------

    /**
     * Return the quiz data without exposing correct answers.
     *
     * @return array
     */
    public function quizWithoutCorrectAnswer(): array
    {
        if ($this->type !== self::TYPE_QUIZ || !is_array($this->content_json)) {
            return [];
        }

        $filtered = [];

        foreach ($this->content_json as $quiz) {
            $filtered[] = Arr::except($quiz, ['correct_option']);
        }

        return $filtered;
    }
}