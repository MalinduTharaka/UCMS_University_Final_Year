<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssign extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'date'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
