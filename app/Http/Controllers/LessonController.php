<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $courseId)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        $course = Course::find($courseId);
        if(!$course){
            return response()->json([
            'error'=> 'Course not found to get its lessons'
            ],404);
        }
        $user = Auth::user();
        if($user->role === 'admin'){
            return response()->json([
            'message'=> 'Retrieve All lessons successfully! ',
            'data' => $course->lessons
        ],200);
        }
        if($user->role === 'instructor' && $course->instructor_id === $user->id){
            return response()->json([
                'message' => 'Retrieve All lessons successfully!',
                'data' => $course->lessons
            ],200);
        }
        if($user->role === 'student' && $course->enrollments()->where('user_id', $user->id)->exists()){
            return response()->json([
                'message' => 'Retrieve All lessons successfully!',
                'data' => $course->lessons
            ],200);
        }
        return response()->json([
            'error'=> 'Unauthorized'
            ],403);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $courseId)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        if(Auth::user()->role !== 'instructor'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $lesson = $request->validate([
            'title' =>'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'content_url' => 'required|string',
        ]);
        $course = Course::find($courseId);
        if(!$course){
            return response()->json([
            'error'=> 'Course not found to create lesson'
            ],404);
        }
        if(Auth::user()->role !== 'instructor' || $course->instructor_id !== Auth::id()){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $newLesson = $course->lessons()->create($lesson);
        return response()->json([
            'message' => 'Lesson created successfully! ',
            'data' => $newLesson
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        $lesson = Lesson::with(['quizzes','course.enrollments'])->find($id);
        if(!$lesson){
            return response()->json([
            'error'=> 'Lesson not found '
            ],404);
        }
        if( Auth::user()->role === 'instructor' && Auth::id() === $lesson->course->instructor_id ){
            return response()->json([
            'message' => 'Retrieve lesson successfully! ',
            'data' => $lesson
            ],200);
        }
        if(Auth::user()->role === 'student' && $lesson->course->enrollments()->where('user_id', Auth::id())->exists()){
            return response()->json([
            'message' => 'Retrieve lesson successfully! ',
            'data' => $lesson
            ],200);
        }
        if(Auth::user()->role === 'admin'){
            return response()->json([
            'message' => 'Retrieve lesson successfully! ',
            'data' => $lesson
            ],200);
        }
        return response()->json([
            'error'=> 'Unauthorized'
            ],403);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        $lesson = Lesson::find($id);
        if(!$lesson){
            return response()->json([
            'error'=> 'Lesson not found '
            ],404);
        }
        if(Auth::user()->role !== 'admin' && Auth::id() !== $lesson->course->instructor_id ){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $lesson->delete();
        return response()->json([
            'message' => 'Lesson deleted successfully! ',
        ],200);
    }
}
