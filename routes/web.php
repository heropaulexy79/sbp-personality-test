<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Organisation
    Route::resource('organisation', OrganisationController::class)->only(['store', 'update']);
    Route::get('/settings/organisation', [OrganisationController::class, 'edit'])->name('organisation.edit');
    Route::post('/organisation/{organisation}/invite', [OrganisationController::class, 'inviteEmployee'])->name('organisation.invite');
    Route::delete('/organisation/{organisation}/invite/{invitation}', [OrganisationController::class, 'uninviteEmployee'])->name('organisation.uninvite');
    Route::patch('/organisation/{organisation}/employee/{employee}', [OrganisationController::class, 'updateEmployee'])->name('organisation.updateEmployee');

    // Org-Course
    Route::get("/organisation/course", [CourseController::class, 'index'])->name('course.index');
    // Route::get("/organisation/{organisation}/course", [CourseController::class, 'index'])->name('course.index');
    Route::get("/organisation/course/{course}", [CourseController::class, 'show'])->name('course.show');
    Route::get("/organisation/course/{course}/edit", [CourseController::class, 'edit'])->name('course.edit');
    Route::get("/organisation/course/create", [CourseController::class, 'create'])->name('course.create');

    Route::post("/organisation/course", [CourseController::class, 'store'])->name('course.store');
    Route::patch("/organisation/course/{course}", [CourseController::class, 'update'])->name('course.update');
    Route::delete("/organisation/course/{course}", [CourseController::class, 'destroy'])->name('course.destroy');


    // Course
    // Route::get("/course/{course}", [CourseController::class, 'show'])->name('course.show');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
