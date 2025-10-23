<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты - доступны всем пользователям
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/institutions', [PublicController::class, 'institutions'])->name('institutions.index');
Route::get('/institutions/{institution}', [PublicController::class, 'institution'])->name('institutions.show');
Route::get('/programs', [PublicController::class, 'programs'])->name('programs.index');
Route::get('/programs/{program}', [PublicController::class, 'program'])->name('programs.show');
Route::get('/courses', [PublicController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course}', [PublicController::class, 'course'])->name('courses.show');
Route::get('/instructors/{instructor}', [PublicController::class, 'instructor'])->name('instructors.show');

// Маршруты аутентификации
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Защищенные маршруты - требуют авторизации
Route::middleware(['auth'])->group(function () {
    // Главная панель управления
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Административные маршруты - требуют роль администратора
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Управление пользователями
        Route::resource('users', UserController::class);

        // Управление учебными заведениями
        Route::resource('institutions', InstitutionController::class);

        // Управление образовательными программами
        Route::resource('programs', ProgramController::class);

        // Управление курсами
        Route::resource('courses', CourseController::class);
    });
});
