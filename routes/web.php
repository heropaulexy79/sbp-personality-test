<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\CourseController as PublicCourseController;
use App\Http\Controllers\UploadController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);

    return view('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Uploads
    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');



    // Organisation
    Route::resource('organisation', OrganisationController::class)->only(['store', 'update']);
    Route::get('/settings/org', [OrganisationController::class, 'edit'])->name('organisation.edit');
    Route::post('/org/{organisation}/invite', [OrganisationController::class, 'inviteEmployee'])->name('organisation.invite');
    Route::delete('/org/{organisation}/invite/{invitation}', [OrganisationController::class, 'uninviteEmployee'])->name('organisation.uninvite');
    Route::patch('/org/{organisation}/employee/{employee}', [OrganisationController::class, 'updateEmployee'])->name('organisation.updateEmployee');
    Route::get('/org/employee/', [OrganisationController::class, 'getAllEmployees'])->name('organisation.employees');
    Route::get('/org/course/{course:slug}', [CourseEnrollmentController::class, 'show'])->name('organisation.course.leaderboard');
    Route::delete('/org/course/{course:slug}/leaderboard/reset', [CourseEnrollmentController::class, 'destroyAll'])->name('organisation.course.leaderboard.reset.students.progress');
    Route::delete('/org/course/{course:slug}/leaderboard/reset/{student}', [CourseEnrollmentController::class, 'destroy'])->name('organisation.course.leaderboard.reset.student.progress');
    Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'storeAll'])->name('course.enroll');

    Route::get('/settings/billing', [BillingController::class, 'index'])->name('organisation.billing.index');

    // Org-course
    // Enroll users

    // Teacher-Course
    Route::get('/teacher/course', [CourseController::class, 'index'])->name('course.index');
    // Route::get("/teacher/{organisation}/course", [CourseController::class, 'index'])->name('course.index');
    Route::get('/teacher/course/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/teacher/course/{course}/edit', [CourseController::class, 'edit'])->name('course.edit');
    Route::get('/teacher/course/create', [CourseController::class, 'create'])->name('course.create');

    Route::post('/teacher/course', [CourseController::class, 'store'])->name('course.store');
    Route::patch('/teacher/course/{course}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/teacher/course/{course}', [CourseController::class, 'destroy'])->name('course.destroy');

    // Course - public?
    Route::get('/course', [PublicCourseController::class, 'index'])->name('public.course.index');
    Route::get('/course/{course:slug}', [PublicCourseController::class, 'show'])->name('public.course.show');
    // Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'store'])->name('course.enroll');

    // Lesson - public?
    Route::get('/course/{course}/lesson/{lesson}', [LessonController::class, 'show'])->name('lesson.show');
    // Lesson
    Route::get('/teacher/course/{course}/lesson/{lesson}/edit', [LessonController::class, 'edit'])->name('lesson.edit');
    Route::patch('/teacher/course/{course}/lesson/{lesson}', [LessonController::class, 'update'])->name('lesson.update');
    Route::patch('/teacher/course/{course}/lesson/{lesson}/postion', [LessonController::class, 'updatePosition'])->name('lesson.update.position');
    Route::get('/teacher/course/{course}/lesson/create', [LessonController::class, 'create'])->name('lesson.create');
    Route::post('/teacher/course/{course}/lesson', [LessonController::class, 'store'])->name('lesson.store');

    // Classrooom
    Route::middleware(['enrolled'])->group(function () {
        Route::get('/classroom/course/{course:slug}', [ClassroomController::class, 'show:slug'])->name('classroom.course.show');
        Route::get('/classroom/course/{course:slug}/lesson', [ClassroomController::class, 'showLessons'])->name('classroom.lesson.index');
        Route::get('/classroom/course/{course:slug}/lesson/{lesson:slug}', [ClassroomController::class, 'showLesson'])->name('classroom.lesson.show');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/mark-complete', [ClassroomController::class, 'markLessonComplete'])->name('classroom.lesson.markComplete');
        Route::patch('/classroom/course/{course:slug}/lesson/{lesson:slug}/answer-quiz', [ClassroomController::class, 'answerQuiz'])->name('classroom.lesson.answerQuiz');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/payment/paystack/pay', [App\Http\Controllers\Payments\PaystackController::class, 'redirectToGateway'])->name('paystack.pay');
    Route::get('/payment/paystack/callback', [App\Http\Controllers\Payments\PaystackController::class, 'handleGatewayCallback'])->name('paystack.pay.gateway');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
