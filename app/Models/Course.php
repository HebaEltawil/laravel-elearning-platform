<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'price',
        'status',
        'instructor_id',
    ];
    public function instructor(){
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function lessons(){
        return $this->hasMany(Lesson::class);
    }
    public function enrollments(){
        return $this->hasMany(Enrollment::class);
    }
    public function students(){
        return $this->belongsToMany(User::class, 'enrollments')->withPivot('progress')->withTimestamps();
    }
}
