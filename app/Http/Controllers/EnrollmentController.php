<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
        ],401);
        }
        $user = Auth::user();
        if($user->role === 'admin'){
            $enrollments = Enrollment::with('course','user')->get();
        }
        elseif($user->role === 'instructor'){
            $enrollments = Enrollment::with('course', 'user')->whereHas('course', function($crs) use($user) {
                $crs->where('instructor_id', $user->id);
            })->get();
        }
        else{
            $enrollments = Enrollment::with('course')->where('user_id',$user->id)->get();
        }
        return response()->json([
                'message'=>'Retrieved all enrollements successfully',
                'data' => $enrollments,
            ],200);
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
        $enrollment = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'progress' => 'integer|min:0|max:100|default:0'
        ]);
        $enrollment['user_id'] = Auth::id();
        $alreadyEnrolled = Enrollment::where('user_id', $enrollment['user_id'])->where('course_id', $enrollment['course_id'])->first();
        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'You are already enrolled in this course',
                'data' => $alreadyEnrolled
                ], 409);
        }
        $newEnrollment = Enrollment::create($enrollment);
        return response()->json([
                'message'=>'Enrollment created successfully!',
                'data' => $newEnrollment,
            ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $enrollment = Enrollment::find($id);
        if(!$enrollment){
            return response()->json([
            'error'=> 'Enrollment not found'
            ],404);
        }
        if(Auth::user()->role !== 'admin'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $enrollment->delete();
        return response()->json([
            'message' => 'Enrollment deleted successfully!',
        ], 200);
    }
}
