<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SettingsController;
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

// Тестовые маршруты админки (временно без авторизации)
Route::get('/admin/test-dashboard', function () {
    try {
        $stats = [
            'users' => \App\Models\User::count(),
            'institutions' => \App\Models\Institution::count(),
            'programs' => \App\Models\Program::count(),
            'courses' => \App\Models\Course::count(),
        ];
        return view('admin.dashboard-simple', compact('stats'));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('admin.test-dashboard');

Route::get('/admin/test-users', function () {
    try {
        $users = \App\Models\User::with('roles')->paginate(15);
        return view('admin.users.index', compact('users'));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('admin.test-users');

Route::get('/admin/test-institutions', function () {
    try {
        $institutions = \App\Models\Institution::paginate(15);
        return view('admin.institutions.index', compact('institutions'));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('admin.test-institutions');

Route::get('/admin/test-programs', function () {
    try {
        $programs = \App\Models\Program::with('institution')->paginate(15);
        return view('admin.programs.index', compact('programs'));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('admin.test-programs');

Route::get('/admin/test-courses', function () {
    try {
        $courses = \App\Models\Course::with(['program', 'instructor'])->paginate(15);
        return view('admin.courses.index', compact('courses'));
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
})->name('admin.test-courses');

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

    // Тестовый маршрут для админки (временно без middleware)
    Route::get('/admin/test', function () {
        $dashboardData = \App\Http\Controllers\Admin\UserController::getDashboardStats();
        return view('admin.dashboard', $dashboardData);
    })->name('admin.test');

    // Простой тест для проверки авторизации
    Route::get('/admin/simple', function () {
        return response()->json([
            'user' => auth()->user() ? auth()->user()->name : 'Not authenticated',
            'roles' => auth()->user() ? auth()->user()->roles->pluck('name') : [],
            'has_admin_role' => auth()->user() ? auth()->user()->hasRole('admin') : false
        ]);
    })->name('admin.simple');

    // Простой маршрут для админки (без middleware для тестирования)
    Route::get('/admin/dashboard', function () {
        try {
            // Простая статистика без сложных запросов
            $stats = [
                'users' => \App\Models\User::count(),
                'institutions' => \App\Models\Institution::count(),
                'programs' => \App\Models\Program::count(),
                'courses' => \App\Models\Course::count(),
            ];

            return view('admin.dashboard-simple', compact('stats'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка загрузки данных',
                'message' => $e->getMessage(),
                'user' => auth()->user() ? auth()->user()->name : 'Not authenticated'
            ]);
        }
    })->name('admin.dashboard.simple');

    // Простой маршрут для списка пользователей
    Route::get('/admin/users', function () {
        try {
            $users = \App\Models\User::with('roles')->paginate(15);
            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка загрузки пользователей',
                'message' => $e->getMessage()
            ]);
        }
    })->name('admin.users.simple');

    // Административные маршруты - требуют роль администратора
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // Главная панель администратора
        Route::get('/dashboard', function () {
            $dashboardData = \App\Http\Controllers\Admin\UserController::getDashboardStats();
            return view('admin.dashboard', $dashboardData);
        })->name('dashboard');

        // Управление пользователями
        Route::resource('users', UserController::class);

        // Управление учебными заведениями
        Route::resource('institutions', InstitutionController::class);

        // Управление образовательными программами
        Route::resource('programs', ProgramController::class);

        // Управление курсами
        Route::resource('courses', CourseController::class);

        // Настройки пользователя
        Route::post('/save-theme-preference', [SettingsController::class, 'saveThemePreference'])->name('save-theme-preference');
        Route::get('/user-settings', [SettingsController::class, 'getUserSettings'])->name('user-settings');
        Route::post('/save-interface-settings', [SettingsController::class, 'saveInterfaceSettings'])->name('save-interface-settings');
    });
});
