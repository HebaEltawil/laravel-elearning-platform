<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'lesson_id',
        'title'
    ];
    protected $with = ['questions'];
    public function lesson(){
        return $this->belongsTo(Lesson::class);
    }
    public function results(){
        return $this->hasMany(QuizResult::class);
    }
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
