<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Ai\PersonalityQuizGeneratorController;
use App\Http\Controllers\Public\ClassroomController as PublicClassroomController;
// FIX: Import the LessonController
use App\Http\Controllers\LessonController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PersonalityTraitController;
use App\Http\Controllers\CourseEnrollmentController; // Make sure this is imported for the enroll route
use App\Http\Controllers\DashboardController; // <-- 1. IMPORT THE NEW CONTROLLER

// --- PUBLIC HOME ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- PUBLIC QUIZ TAKING ROUTES ---
Route::get('/quiz/{lesson:slug}', [PublicClassroomController::class, 'showLessonPublic'])->name('public.quiz.show');
Route::patch('/quiz/{lesson:slug}/answer-personality', [PublicClassroomController::class, 'answerPersonalityQuiz'])->name('public.quiz.answerPersonality');


// --- ADMIN / DASHBOARD ROUTES ---
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    // 2. POINT THE DASHBOARD ROUTE TO THE 'DashboardController'
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quiz Management (This is now your Admin/Teacher quiz creator)
    // FIX: Explicitly bind the resource route to the 'slug' parameter to prevent 404s if model binding is inconsistent.
    Route::resource('quizzes', QuizController::class)
        ->except(['show'])
        ->parameters(['quizzes' => 'quiz:slug']); 

    // --- ENROLLMENT ROUTES ---
    // This block includes the existing enrollment routes and the new search API route.
    Route::get('/quizzes/{quiz:slug}/enroll', [CourseEnrollmentController::class, 'edit'])->name('quizzes.enroll');
    Route::post('/quizzes/{quiz:slug}/enroll', [CourseEnrollmentController::class, 'update'])->name('quizzes.enroll.update');

    // NEW API ROUTE: Server-side search for users during enrollment
    Route::get('/quizzes/{quiz:slug}/users/search', [CourseEnrollmentController::class, 'searchUsersForEnrollment'])->name('api.quizzes.users.search');
    // --- END ENROLLMENT ROUTES ---

    // =================================================================
    // FIX: ADD THIS BLOCK
    // RE-ADDING THIS LINE. It was accidentally deleted.
    // FIX: Change parent resource path from 'courses/{course}' to 'quizzes/{quiz}' for consistency
    Route::resource('quizzes/{quiz}/lessons', LessonController::class)
        ->shallow()
        ->names([
            'create' => 'lesson.create',
            'store' => 'lesson.store',
            'show' => 'lesson.show',
            'edit' => 'lesson.edit',
            'update' => 'lesson.update',
            'destroy' => 'lesson.destroy',
        ]);
    // =================================================================
    // END OF FIX
    // =================================================================

    // AI Generator (UPDATED FOR NEW FUNCTIONALITY)
    // FIX: New route for creating a NEW quiz using AI (used on Quiz/Create.vue)
    Route::post('/ai/quizzes/generate-and-store', [PersonalityQuizGeneratorController::class, 'generateAndStore'])
        ->name('ai.quizzes.generate_and_store'); 
        
    // FIX: New route for UPDATING an existing quiz with AI content (used on Quiz/Edit.vue)
    Route::post('/quizzes/{lesson}/generate-content', [PersonalityQuizGeneratorController::class, 'generateAndUpdate'])
        ->name('quizzes.generate_content');

    // API for traits
    Route::get('/api/personality-traits', [PersonalityTraitController::class, 'index'])->name('api.personality-traits.index');
});

// --- PROFILE ROUTES ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// This route was in your file content but the controller wasn't imported. I added the import.
Route::match(['get', 'post'], '/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'storeAll'])->name('course.enroll');

require __DIR__ . '/auth.php';