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
     * The attributes that should be cast to native types.
     * Casting content_json to 'array' ensures it's decoded from JSON
     * into a PHP array automatically.
     *
     * @var array<string, string>
     */
    protected $casts = [
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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function userLesson()
    {
        return $this->hasMany(UserLesson::class);
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

    /**
     * Accessor for personality quiz data.
     * Returns structured questions and traits arrays.
     *
     * @return array
     */
    public function getPersonalityQuizDataAttribute(): array
    {
        if ($this->type !== self::TYPE_PERSONALITY_QUIZ || !is_array($this->content_json)) {
            return ['questions' => [], 'traits' => []];
        }

        return [
            'questions' => $this->content_json['questions'] ?? [],
            'traits' => $this->content_json['traits'] ?? [],
        ];
    }
}
