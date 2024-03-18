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

    // // Mutator to handle content based on type
    // public function getContentAttribute()
    // {
    //     if ($this->type === self::TYPE_DEFAULT) {
    //         return $this->content;
    //     } else if ($this->type === self::TYPE_QUIZ) {
    //         return json_decode($this->content_json, true);
    //     } else {
    //         return null; // Or throw an exception for invalid type
    //     }
    // }

    // // Mutator to handle setting content based on type
    // public function setContentAttribute($value)
    // {
    //     if (is_string($value)) {
    //         $this->type = self::TYPE_DEFAULT;
    //         $this->content = $value;
    //         $this->content_json = null;
    //     } else if (is_array($value)) {
    //         $this->type = self::TYPE_QUIZ;
    //         $this->content = null;
    //         $this->content_json = json_encode($value);
    //     } else {
    //         throw new InvalidArgumentException('Invalid lesson content type');
    //     }
    // }
}
