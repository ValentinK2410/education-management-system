<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ProfileController;
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

    // Маршруты профиля пользователя
    Route::get('/admin/profile', [ProfileController::class, 'show'])->name('admin.profile.show');
    Route::get('/admin/profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');

    // Тестовые маршруты профиля (временно без контроллера)
    Route::get('/admin/test-profile', function () {
        try {
            $user = auth()->user();
            return view('admin.profile.show', compact('user'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-profile');

    Route::get('/admin/test-profile-edit', function () {
        try {
            $user = auth()->user();
            return view('admin.profile.edit', compact('user'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-profile-edit');

    // Тестовый маршрут для проверки сохранения программы
    Route::post('/admin/test-program-update', function (Request $request) {
        try {
            $program = \App\Models\Program::find(3);
            if (!$program) {
                return response()->json(['error' => 'Программа не найдена']);
            }

            $data = $request->all();

            // Логируем входящие данные
            \Log::info('Test program update data:', $data);

            // Если программа не платная, обнуляем цену
            if (!$request->boolean('is_paid')) {
                $data['price'] = null;
            }

            $program->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Программа обновлена',
                'data' => $program->fresh()->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-program-update');

    // Тестовый маршрут для проверки структуры таблицы programs
    Route::get('/admin/test-program-structure', function () {
        try {
            $program = \App\Models\Program::find(3);
            if (!$program) {
                return response()->json(['error' => 'Программа не найдена']);
            }

            // Проверяем наличие полей оплаты (исправленная логика)
            $hasPaymentFields = [
                'is_paid' => array_key_exists('is_paid', $program->getAttributes()),
                'price' => array_key_exists('price', $program->getAttributes()),
                'currency' => array_key_exists('currency', $program->getAttributes()),
            ];

            // Дополнительная проверка через raw SQL
            $rawData = \DB::table('programs')->where('id', 3)->first();
            $rawFields = [
                'is_paid' => property_exists($rawData, 'is_paid'),
                'price' => property_exists($rawData, 'price'),
                'currency' => property_exists($rawData, 'currency'),
            ];

            return response()->json([
                'program_id' => $program->id,
                'program_name' => $program->name,
                'current_values' => [
                    'is_paid' => $program->is_paid,
                    'price' => $program->price,
                    'currency' => $program->currency,
                ],
                'has_payment_fields' => $hasPaymentFields,
                'raw_fields_check' => $rawFields,
                'raw_data' => $rawData,
                'fillable_fields' => $program->getFillable(),
                'table_columns' => \Schema::getColumnListing('programs'),
                'model_attributes' => $program->getAttributes(),
                'casts' => $program->getCasts()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-program-structure');

    // Тестовый маршрут для выполнения миграции исправления поля price
    Route::get('/admin/fix-price-field', function () {
        try {
            // Выполняем миграцию исправления поля price
            \Artisan::call('migrate', ['--path' => 'database/migrations/2024_01_01_000010_fix_price_field_in_programs_table.php']);

            $output = \Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Миграция исправления поля price выполнена',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.fix-price-field');

    // Тестовый маршрут для проверки сохранения цены программы
    Route::post('/admin/test-save-price', function (Request $request) {
        try {
            $program = \App\Models\Program::find(3);
            if (!$program) {
                return response()->json(['error' => 'Программа не найдена']);
            }

            // Логируем входящие данные
            \Log::info('Test save price data:', $request->all());

            // Подготавливаем данные для сохранения
            $data = [
                'is_paid' => $request->boolean('is_paid'),
                'price' => $request->input('price'),
                'currency' => $request->input('currency', 'RUB'),
            ];

            // Если программа не платная, обнуляем цену
            if (!$data['is_paid']) {
                $data['price'] = null;
            }

            // Сохраняем данные
            $program->update($data);

            // Получаем обновленные данные
            $updatedProgram = $program->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Цена программы сохранена',
                'before' => [
                    'is_paid' => $program->is_paid,
                    'price' => $program->price,
                    'currency' => $program->currency,
                ],
                'after' => [
                    'is_paid' => $updatedProgram->is_paid,
                    'price' => $updatedProgram->price,
                    'currency' => $updatedProgram->currency,
                ],
                'input_data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-save-price');

    // Тестовая страница для отправки POST запроса
    Route::get('/test-price-form', function () {
        return response()->file(public_path('test-price.html'));
    })->name('test-price-form');

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
