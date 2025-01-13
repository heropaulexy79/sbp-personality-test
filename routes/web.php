<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationUserController;
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
Route::get('/terms-and-conditions', function () {
    return view('terms-conditions', []);
})->name('website.terms');
Route::get('/privacy-policy', function () {
    return view('privacy-policy', []);
})->name('website.privacy');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // Uploads
    Route::post('/upload', [UploadController::class, 'store'])->name('upload');
    Route::delete('/upload', [UploadController::class, 'destroy'])->name('upload.delete');



    // Organisation
    Route::resource('organisation', OrganisationController::class)->only(['store', 'update']);
    Route::get('/settings/org', [OrganisationController::class, 'edit'])->name('organisation.edit');

    Route::delete('/org/{organisation}/invite/{invitation}', [OrganisationController::class, 'uninviteEmployee'])->name('organisation.uninvite')->middleware(['subscribed']);

    Route::post('/org/{organisation}/invite', [OrganisationUserController::class, 'store'])->name('organisation.invite')->middleware(['subscribed']);
    Route::patch('/org/{organisation}/employee/{employee}', [OrganisationUserController::class, 'update'])->name('organisation.updateEmployee')->middleware(['subscribed']);
    Route::delete('/org/{organisation}/employee/{employee}', [OrganisationUserController::class, 'destroy'])->name('organisation.employee.delete')->middleware(['subscribed']);
    Route::get('/org/employee/', [OrganisationUserController::class, 'index'])->name('organisation.employees')->middleware(['subscribed']);

    Route::get('/settings/billing', [BillingController::class, 'index'])->name('organisation.billing.index');

    // Org-course
    Route::get('/org/course', [CourseController::class, 'index'])->name('course.index');
    // Route::get("/org/{organisation}/course", [CourseController::class, 'index'])->name('course.index');
    Route::get('/org/course/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/org/course/{course}/edit', [CourseController::class, 'edit'])->name('course.edit');
    Route::get('/org/course/create', [CourseController::class, 'create'])->name('course.create');

    Route::post('/org/course', [CourseController::class, 'store'])->name('course.store');
    Route::patch('/org/course/{course}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/org/course/{course}', [CourseController::class, 'destroy'])->name('course.destroy');

    Route::get('/org/course/{course:slug}/stats/leaderboard', [CourseEnrollmentController::class, 'show'])->name('organisation.course.leaderboard')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/student/{student}', [CourseEnrollmentController::class, 'destroy'])->name('organisation.course.student.destroy')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/stats/leaderboard/reset', [CourseEnrollmentController::class, 'resetAll'])->name('organisation.course.leaderboard.reset.students.progress')->middleware(['subscribed']);
    Route::delete('/org/course/{course:slug}/stats/leaderboard/reset/{student}', [CourseEnrollmentController::class, 'reset'])->name('organisation.course.leaderboard.reset.student.progress')->middleware(['subscribed']);
    Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'storeAll'])->name('course.enroll')->middleware(['subscribed']);


    // Course - public?
    Route::get('/course', [PublicCourseController::class, 'index'])->name('public.course.index');
    Route::get('/course/{course:slug}', [PublicCourseController::class, 'show'])->name('public.course.show');
    // Route::post('/course/{course:slug}/enroll', [CourseEnrollmentController::class, 'store'])->name('course.enroll');

    // Lesson - public?
    Route::get('/course/{course}/lesson/{lesson}', [LessonController::class, 'show'])->name('lesson.show');
    // Lesson
    Route::get('/org/course/{course}/lesson/{lesson}/edit', [LessonController::class, 'edit'])->name('lesson.edit');
    Route::patch('/org/course/{course}/lesson/{lesson}', [LessonController::class, 'update'])->name('lesson.update');
    Route::patch('/org/course/{course}/lesson/{lesson}/postion', [LessonController::class, 'updatePosition'])->name('lesson.update.position');
    Route::get('/org/course/{course}/lesson/create', [LessonController::class, 'create'])->name('lesson.create');
    Route::post('/org/course/{course}/lesson', [LessonController::class, 'store'])->name('lesson.store');

    // Classrooom
    Route::middleware(['subscribed', 'enrolled'])->group(function () {
        // TODO: FIX show:slug
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
    Route::get('/payment/paystack/wbhk', [App\Http\Controllers\Payments\PaystackController::class, 'handleWebhook'])->name('paystack.pay.webhook');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
