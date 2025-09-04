<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        $validated = $request->validate([
            'lesson_id'=>'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct_answer' => 'required|string'
        ]);
        $quiz = Quiz::create([
            'lesson_id' => $validated['lesson_id'],
            'title' => $validated['title'],
        ]);
        foreach($validated['questions'] as $question){
            if(!in_array($question['correct_answer'], $question['options'])){
                return response()->json([
                    'error' => 'Correct answer must be one of the options for question: '. $question['question']
                ],404);
            }
            $quiz->questions()->create([
                'question' => $question['question'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
            ]);
        }


        return response()->json([
                'message'=>'Quiz created successfully!',
                'data' => $quiz,
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
        $quiz = Quiz::with('questions')->find($id);
        if(!$quiz){
            return response()->json([
                'error' => 'Quiz not found'
            ],404);
        }
        return response()->json([
                'message'=>'Quiz retrieved successfully!',
                'data' => $quiz,
            ],200);

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
        //
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
        if(Auth::user()->role !== 'instructor'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $quiz = Quiz::find($id);
        if(!$quiz){
            return response()->json([
                'error' => 'Quiz not found'
            ],404);
        }
        $quiz->delete();
        return response()->json([
            'message' => 'Quiz deleted successfully!'
        ],200);
    }
}
