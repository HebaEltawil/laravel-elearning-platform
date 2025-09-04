<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;

class QuizResultController extends Controller
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
        if(Auth::user()->role !== 'student'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required|string'
        ]);
        $quiz = Quiz::with('questions')->find($validated['quiz_id']);
        if (!$quiz) {
            return response()->json([
                'error' => 'Quiz not found'
            ], 404);
        }
        $total_questions = $quiz->questions->count();
        $score = 0;
        foreach($quiz->questions as $question){
            $student_answer = collect($validated['answers'])->firstWhere('question_id', $question->id);
            if($student_answer && $student_answer['answer'] === $question->correct_answer){
                $score ++;
            }
        }
        $result = ($total_questions > 0 ) ? round(($score/$total_questions)* 100) : 0 ;
        $newResult = QuizResult::updateOrCreate([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id()
            ],
            [
                'score' => $result
            ]
        );
        $enrollment = Enrollment::where('user_id', Auth::id())->whereHas('course.lessons.quizzes', function($q) use ($quiz){
            $q->where('id', $quiz->id);
        })->first();
        if($enrollment){
            $courseQuizIds = Quiz::whereHas('lesson.course.enrollments', function($q) use ($enrollment){
                $q->where('id', $enrollment->id);
            })->pluck('id');
            $total_quizzes = $courseQuizIds->count();

            $solved_quizzes = QuizResult::where('user_id', Auth::id())->whereIn('quiz_id', $courseQuizIds)->count();
            $progress = ($total_quizzes > 0 ) ? ($solved_quizzes / $total_quizzes) * 100 : 0 ;
            $enrollment->update([
                'progress' => $progress
            ]);
        }
        return response()->json([
                'message'=>'result created successfully!',
                'data' => $newResult,
                'total_questions' => $total_questions,
                'correct_answers' => $score
            ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $quizId)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        $userId = Auth::id();
        $result = QuizResult::where('quiz_id',$quizId)->where('user_id', $userId)->first();
        if(!$result){
            return response()->json([
            'error'=> 'Result of this Quiz not found '
            ],404);
        }
        return response()->json([
            'message' => 'Retrieve result successfully! ',
            'data' => $result
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
        //
    }
}
