<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth ;

class CourseController extends Controller
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
            $courses = Course::all();
        }
        elseif($user->role === 'instructor'){
            $courses= Course::where('instructor_id', $user->id)->get();
        }
        else{
            $courses = Course::where('status', 'approved')->get();
        }
        return response()->json([
            'message'=> 'Retrieve All courses successfully! ',
            'data' => $courses
        ],200);
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
        $course = $request->validate([
            'title' =>'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ]);
        $course['instructor_id'] = Auth::id();
        $course['status'] = 'pending';
        $newCourse= Course::create($course);
        return response()->json([
            'message' => 'Course created successfully!',
            'data'  => $newCourse
        ], 201);
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
        $course = Course::with('lessons.quizzes')->find($id);
        if(!$course){
            return response()->json([
            'error'=> 'Course not found'
            ],404);
        }
        return response()->json([
            'message'=> 'Retrieve course successfully! ',
            'data' => $course
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
    public function update(Request $request)
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
        $course = Course::find($id);
        if(!$course){
            return response()->json([
            'error'=> 'Course not found'
            ],404);
        }
        if(Auth::id() !== $course->instructor_id && Auth::user()->role !== 'admin'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $course->delete();
        return response()->json([
            'message' => 'Course deleted successfully!',
        ], 200);
    }

    public function updateStatus(Request $request, $courseId)
    {
        if(!Auth::check()){
            return response()->json([
            'error'=> 'Token is invalid or expired',
            ],401);
        }
        $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $course = Course::find($courseId);

        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ], 404);
        }
        if(Auth::user()->role !== 'admin'){
            return response()->json([
            'error'=> 'Unauthorized'
            ],403);
        }
        $course->status = $request->status;
        $course->save();
        return response()->json([
            'message' => 'Course status updated successfully!',
            'course' => $course
        ], 200);
    }

}
