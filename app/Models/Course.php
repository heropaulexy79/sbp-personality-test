<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'organisation_id',
        'is_published'
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }


    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }
}
