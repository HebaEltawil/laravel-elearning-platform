<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizResultController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/editProfile', [AuthController::class, 'editProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('courses/{courseId}',[CourseController::class, 'show']);
    Route::post('/createCourse', [CourseController::class, 'store']);
    Route::delete('/deleteCourse/{courseId}', [CourseController::class, 'destroy']);
    Route::patch('/courses/{courseId}/status', [CourseController::class, 'updateStatus']);

});

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/courses/{courseId}/lessons', [LessonController::class, 'index']);
    Route::get('lessons/{lessonId}',[LessonController::class, 'show']);
    Route::post('/courses/{courseId}/createLesson', [LessonController::class, 'store']);
    Route::delete('/deleteLesson/{lessonId}', [LessonController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/createEnrollment', [EnrollmentController::class, 'store']);
    Route::delete('/deleteEnrollment/{enrollmentId}', [EnrollmentController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/quizzes/{quizId}', [QuizController::class, 'show']);
    Route::post('/createQuiz', [QuizController::class, 'store']);
    Route::delete('/deleteQuiz/{quizId}', [QuizController::class, 'destroy']);
    Route::post('/storeQuizResult', [QuizResultController::class, 'store']);
    Route::get('/quizResult/{quizId}', [QuizResultController::class, 'show']);
});
