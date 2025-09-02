<?php

// app/Models/CourseContent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    protected $fillable = ['course_id', 'title', 'path', 'type'];
    protected $appends  = ['content_url', 'thumbnail_url'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getContentUrlAttribute()
    {
        return $this->path ? asset($this->path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->type === 'image') return $this->content_url;
        if ($this->type === 'pdf')   return asset('images/icons/pdf.png');
        if ($this->type === 'video') return asset('images/icons/video.png');
        return asset('images/icons/file.png');
    }
}

