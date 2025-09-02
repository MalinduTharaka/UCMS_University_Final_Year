<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'image',
        'status',
    ];

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'course_assigns', 'course_id', 'user_id');
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }

}
