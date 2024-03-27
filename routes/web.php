<?php

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CourseController as PublicCourseController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

Route::get('/dashboard', function (Request $request) {

    $status = $request->query('status');
    $courses = $request->user()->enrolledCourses()->where("is_completed", $status === "completed" ? "1" : "0")
        ->paginate()->withQueryString();


    return Inertia::render('Dashboard', ['courses' => $courses]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Organisation
    Route::resource('organisation', OrganisationController::class)->only(['store', 'update']);
    Route::get('/settings/organisation', [OrganisationController::class, 'edit'])->name('organisation.edit');
    Route::post('/organisation/{organisation}/invite', [OrganisationController::class, 'inviteEmployee'])->name('organisation.invite');
    Route::delete('/organisation/{organisation}/invite/{invitation}', [OrganisationController::class, 'uninviteEmployee'])->name('organisation.uninvite');
    Route::patch('/organisation/{organisation}/employee/{employee}', [OrganisationController::class, 'updateEmployee'])->name('organisation.updateEmployee');

    // Org-Course
    Route::get('/organisation/course', [CourseController::class, 'index'])->name('course.index');
    // Route::get("/organisation/{organisation}/course", [CourseController::class, 'index'])->name('course.index');
    Route::get('/organisation/course/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/organisation/course/{course}/edit', [CourseController::class, 'edit'])->name('course.edit');
    Route::get('/organisation/course/create', [CourseController::class, 'create'])->name('course.create');

    Route::post('/organisation/course', [CourseController::class, 'store'])->name('course.store');
    Route::patch('/organisation/course/{course}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/organisation/course/{course}', [CourseController::class, 'destroy'])->name('course.destroy');

    // Course - public?
    Route::get('/course', [PublicCourseController::class, 'index'])->name('public.course.index');
    Route::get('/course/{course:slug}', [PublicCourseController::class, 'show'])->name('public.course.show');
    Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'store'])->name('course.enroll');

    // Lesson - public?
    Route::get('/course/{course}/lesson/{lesson}', [LessonController::class, 'show'])->name('lesson.show');
    // Lesson
    Route::get('/organisation/course/{course}/lesson/{lesson}/edit', [LessonController::class, 'edit'])->name('lesson.edit');
    Route::patch('/organisation/course/{course}/lesson/{lesson}', [LessonController::class, 'update'])->name('lesson.update');
    Route::get('/organisation/course/{course}/lesson/create', [LessonController::class, 'create'])->name('lesson.create');
    Route::post('/organisation/course/{course}/lesson', [LessonController::class, 'store'])->name('lesson.store');

    // Classrooom
    Route::middleware(['enrolled'])->group(function () {
        Route::get('/classroom/course/{course:slug}', [ClassroomController::class, 'show:slug'])->name('classroom.course.show');
        Route::get('/classroom/course/{course:slug}/lesson', [ClassroomController::class, 'showLessons'])->name('classroom.lesson.index');
        Route::get('/classroom/course/{course:slug}/lesson/{lesson:slug}', [ClassroomController::class, 'showLesson'])->name('classroom.lesson.show');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/mark-complete', [ClassroomController::class, 'markLessonComplete'])->name('classroom.lesson.markComplete');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/answer-quiz', [ClassroomController::class, 'answerQuiz'])->name('classroom.lesson.answerQuiz');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
