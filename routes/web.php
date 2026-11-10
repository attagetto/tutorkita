<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LearningSessionController;
use App\Http\Controllers\LearningMaterialController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\NotificationController;

Route::get('/', LandingController::class)->name('home');


Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/sessions/{learningSession}', [LearningSessionController::class, 'show'])->name('dashboard.sessions.show');
    Route::post('/dashboard/sessions', [LearningSessionController::class, 'store'])->name('dashboard.sessions.store');
    Route::post('/dashboard/sessions/{learningSession}/cancel', [LearningSessionController::class, 'cancel'])->name('dashboard.sessions.cancel');
    Route::get('/dashboard/materials/{learningMaterial}', [LearningMaterialController::class, 'show'])->name('dashboard.materials.show');
    Route::post('/dashboard/materials/{learningMaterial}/bookmark', [LearningMaterialController::class, 'toggleBookmark'])->name('dashboard.materials.bookmark');
    Route::post('/dashboard/materials/{learningMaterial}/complete', [LearningMaterialController::class, 'complete'])->name('dashboard.materials.complete');
    Route::get('/dashboard/materials/{learningMaterial}/file', [LearningMaterialController::class, 'file'])->name('dashboard.materials.file');
    Route::get('/dashboard/assignments/{assignment}', [AssignmentController::class, 'show'])->name('dashboard.assignments.show');
    Route::post('/dashboard/assignments/{assignment}/submissions', [AssignmentController::class, 'submit'])->name('dashboard.assignments.submit');
    Route::get('/dashboard/assignments/{assignment}/submissions/{submission}/file', [AssignmentController::class, 'file'])->name('dashboard.assignments.submission-file');
    Route::post('/dashboard/assignments', [AssignmentController::class, 'create'])->name('dashboard.assignments.create');
    Route::post('/dashboard/assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('dashboard.assignments.grade');
    Route::post('/dashboard/notifications/{notification}/read', [NotificationController::class, 'read'])->name('dashboard.notifications.read');
    Route::post('/dashboard/notifications/read-all', [NotificationController::class, 'readAll'])->name('dashboard.notifications.read-all');
    Route::post('/dashboard/notifications/preferences', [NotificationController::class, 'preference'])->name('dashboard.notifications.preference');
    Route::get('/subjects', fn () => redirect()->route('dashboard')->withFragment('materials'))->name('subjects');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
