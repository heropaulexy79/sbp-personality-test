<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Ai\PersonalityQuizGeneratorController;
use App\Http\Controllers\Public\ClassroomController as PublicClassroomController;
use App\Http\Controllers\LessonController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PersonalityTraitController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Models\Lesson;

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Quiz Management (Teacher/Admin quiz creator)
    Route::resource('quizzes', QuizController::class)
        ->except(['show'])
        ->parameters(['quizzes' => 'quiz:slug']);

    // --- ENROLLMENT ROUTES ---
    Route::get('/quizzes/{quiz:slug}/enroll', [CourseEnrollmentController::class, 'edit'])->name('quizzes.enroll');
    Route::post('/quizzes/{quiz:slug}/enroll', [CourseEnrollmentController::class, 'update'])->name('quizzes.enroll.update');

    // API ROUTE: Server-side search for users during enrollment
    Route::get('/quizzes/{quiz:slug}/users/search', [CourseEnrollmentController::class, 'searchUsersForEnrollment'])->name('api.quizzes.users.search');
    // --- END ENROLLMENT ROUTES ---

    // Lesson Management (Nested under Quiz with shallow routing)
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

    // AI Generator
    Route::post('/ai/quizzes/generate-and-store', [PersonalityQuizGeneratorController::class, 'generateAndStore'])
        ->name('ai.quizzes.generate_and_store');

    Route::post('/quizzes/{lesson}/generate-content', [PersonalityQuizGeneratorController::class, 'generateAndUpdate'])
        ->name('quizzes.generate_content');

    // API for personality traits
    Route::get('/api/personality-traits', [PersonalityTraitController::class, 'index'])->name('api.personality-traits.index');
});

// --- PROFILE ROUTES ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::match(['get', 'post'], '/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'storeAll'])->name('course.enroll');

require __DIR__ . '/auth.php';