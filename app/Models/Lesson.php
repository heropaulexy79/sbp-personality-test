<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class Lesson extends Model
{
    use HasFactory;


    protected $fillable = ['title', 'course_id', 'type', 'content', 'content_json'];

    protected $casts = [
        "content_json" => "json"
    ];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }


    public function quizWithoutCorrectAnswer()
    {
        $filtered = [];

        foreach ($this->content_json as $quiz) {
            $filtered[] = Arr::except($quiz, ['correctOption']);
        }

        return $filtered;
    }


    const TYPE_DEFAULT = 'DEFAULT';
    const TYPE_QUIZ = 'QUIZ';
}
