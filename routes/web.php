<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/institutions', [PublicController::class, 'institutions'])->name('institutions.index');
Route::get('/institutions/{institution}', [PublicController::class, 'institution'])->name('institutions.show');
Route::get('/programs', [PublicController::class, 'programs'])->name('programs.index');
Route::get('/programs/{program}', [PublicController::class, 'program'])->name('programs.show');
Route::get('/courses', [PublicController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course}', [PublicController::class, 'course'])->name('courses.show');
Route::get('/instructors/{instructor}', [PublicController::class, 'instructor'])->name('instructors.show');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Users management
        Route::resource('users', UserController::class);
        
        // Institutions management
        Route::resource('institutions', InstitutionController::class);
        
        // Programs management
        Route::resource('programs', ProgramController::class);
        
        // Courses management
        Route::resource('courses', CourseController::class);
    });
});
