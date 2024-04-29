<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'course_id', 'slug', 'type', 'content', 'content_json', 'is_published'];

    protected $casts = [
        'content_json' => 'json',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user_lesson()
    {
        return $this->hasMany(UserLesson::class);
    }

    public function quizWithoutCorrectAnswer()
    {
        $filtered = [];

        foreach ($this->content_json as $quiz) {
            $filtered[] = Arr::except($quiz, ['correct_option']);
        }

        return $filtered;
    }

    const TYPE_DEFAULT = 'DEFAULT';

    const TYPE_QUIZ = 'QUIZ';
}
