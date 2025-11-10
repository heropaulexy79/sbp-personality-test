<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Ai\PersonalityQuizGeneratorController;
use App\Http\Controllers\Public\ClassroomController as PublicClassroomController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PersonalityTraitController;


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
    Route::get('/dashboard', [QuizController::class, 'index'])->name('dashboard');

    // Quiz Management
    Route::resource('quizzes', QuizController::class)->except(['show']);

    // AI Generator (UPDATED)
    Route::post('/ai/generate-quiz', [PersonalityQuizGeneratorController::class, 'generate'])->name('ai.quiz.generate');
    Route::post('/ai/save-quiz', [PersonalityQuizGeneratorController::class, 'store'])->name('ai.quiz.store');

    // API for traits
    Route::get('/api/personality-traits', [PersonalityTraitController::class, 'index'])->name('api.personality-traits.index');
});

// --- PROFILE ROUTES ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';