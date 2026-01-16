<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserArchiveController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserSwitchController;
use App\Http\Controllers\Admin\InstructorStatsController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CourseAnalyticsController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\Api\CourseSyncController;
use Illuminate\Support\Facades\Route;

// Главная страница (современный дизайн)
Route::get('/modern', function () {
    return view('welcome-modern');
})->name('welcome.modern');

// Страница в стиле семинарии
Route::get('/seminary-style', function () {
    return view('seminary-style');
})->name('welcome.seminary-style');

// Публичные маршруты - доступны всем пользователям
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/institutions', [PublicController::class, 'institutions'])->name('institutions.index');
Route::get('/institutions/{institution}', [PublicController::class, 'institution'])->name('institutions.show');
Route::get('/programs', [PublicController::class, 'programs'])->name('programs.index');
Route::get('/programs/{program}', [PublicController::class, 'program'])->name('programs.show');
Route::get('/courses', [PublicController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course}', [PublicController::class, 'course'])->name('courses.show');
Route::get('/instructors/{instructor}', [PublicController::class, 'instructor'])->name('instructors.show');

// Маршруты для отзывов (требуют авторизации)
Route::middleware('auth')->group(function () {
    Route::get('/courses/{course}/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Сертификаты
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::post('/courses/{course}/generate-certificate', [CertificateController::class, 'generateForCourse'])->name('certificates.generate.course');
    Route::post('/programs/{program}/generate-certificate', [CertificateController::class, 'generateForProgram'])->name('certificates.generate.program');

    // Профиль пользователя (публичный маршрут)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

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

// API маршруты для синхронизации с WordPress (без CSRF защиты)
Route::prefix('api')->group(function () {
    Route::post('/users/sync-from-wordpress', [\App\Http\Controllers\Api\UserSyncController::class, 'createFromWordPress'])
        ->name('api.users.sync-from-wordpress');
    Route::post('/courses/sync-from-wordpress', [CourseSyncController::class, 'syncFromWordPress'])
        ->name('api.courses.sync-from-wordpress');
});

// SSO маршруты (без CSRF защиты) - должны быть в начале файла
Route::get('/sso/login', [SsoController::class, 'login'])
    ->name('sso.login');

// Moodle SSO маршруты (требуют авторизации)
Route::middleware(['auth'])->group(function () {
    Route::get('/moodle/sso/redirect', [\App\Http\Controllers\Auth\MoodleSsoController::class, 'redirect'])
        ->name('moodle.sso.redirect');
});

// Маршруты аутентификации
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Защищенные маршруты - требуют авторизации
Route::middleware(['auth'])->group(function () {
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

    // Тестовый маршрут без CSRF защиты для простого тестирования
    Route::post('/admin/test-save-price-simple', function (Request $request) {
        try {
            $program = \App\Models\Program::find(3);
            if (!$program) {
                return response()->json(['error' => 'Программа не найдена']);
            }

            // Логируем входящие данные
            \Log::info('Test save price data (simple):', $request->all());

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
                'message' => 'Цена программы сохранена (простой тест)',
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
    })->name('admin.test-save-price-simple');

    // Простой тестовый маршрут через GET для проверки сохранения цены
    Route::get('/admin/test-save-price-get', function (Request $request) {
        try {
            $program = \App\Models\Program::find(3);
            if (!$program) {
                return response()->json(['error' => 'Программа не найдена']);
            }

            // Получаем параметры из URL
            $isPaid = $request->get('is_paid', '0') === '1';
            $price = $request->get('price');
            $currency = $request->get('currency', 'RUB');

            // Логируем входящие данные
            \Log::info('Test save price data (GET):', [
                'is_paid' => $isPaid,
                'price' => $price,
                'currency' => $currency
            ]);

            // Подготавливаем данные для сохранения
            $data = [
                'is_paid' => $isPaid,
                'price' => $isPaid ? $price : null,
                'currency' => $currency,
            ];

            // Сохраняем данные
            $program->update($data);

            // Получаем обновленные данные
            $updatedProgram = $program->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Цена программы сохранена (GET тест)',
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
                'input_data' => $data,
                'url_params' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('admin.test-save-price-get');

    // Тестовая страница для отправки POST запроса
    Route::get('/test-price-form', function () {
        return response()->file(public_path('test-price.html'));
    })->name('test-price-form');

    // Главная панель администратора - доступна всем авторизованным пользователям
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/dashboard/sync', [DashboardController::class, 'sync'])->name('admin.dashboard.sync');

    // Маршруты для переключения пользователей и ролей (только для админов)
    Route::middleware(['check.role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/user-switch/users', [UserSwitchController::class, 'getUsers'])->name('user-switch.users');
        Route::get('/user-switch/switch/{user}', [UserSwitchController::class, 'switchToUser'])->name('user-switch.switch');
        Route::get('/role-switch/switch/{role}', [UserSwitchController::class, 'switchToRole'])->name('role-switch.switch');
    });

    // Маршруты возврата (без middleware check.role:admin, так как пользователь может иметь временно другую роль)
    // Проверка безопасности выполняется внутри контроллеров через проверку сессий
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/user-switch/back', [UserSwitchController::class, 'switchBack'])->name('user-switch.back');
        Route::get('/role-switch/back', [UserSwitchController::class, 'switchRoleBack'])->name('role-switch.back');
    });

    // Административные маршруты - требуют роль администратора
    // ВАЖНО: Конкретные маршруты (create, edit) должны быть определены ПЕРЕД параметрическими ({user})
    Route::middleware(['check.role:admin'])->prefix('admin')->name('admin.')->group(function () {

        // Управление пользователями (создание, редактирование, удаление - только для администраторов)
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Управление ролями
        Route::resource('roles', RoleController::class);

        // Управление учебными заведениями
        Route::resource('institutions', InstitutionController::class);

        // Управление образовательными программами
        Route::post('programs/{program}/duplicate', [ProgramController::class, 'duplicate'])->name('programs.duplicate');
        Route::post('programs/{program}/courses/{course}/move-up', [ProgramController::class, 'moveCourseUp'])->name('programs.courses.move-up')->where(['course' => '[0-9]+']);
        Route::post('programs/{program}/courses/{course}/move-down', [ProgramController::class, 'moveCourseDown'])->name('programs.courses.move-down')->where(['course' => '[0-9]+']);
        Route::post('programs/{program}/subjects/{subject}/attach', [ProgramController::class, 'attachSubject'])->name('programs.subjects.attach');
        Route::post('programs/{program}/subjects/{subject}/detach', [ProgramController::class, 'detachSubject'])->name('programs.subjects.detach');
        Route::post('programs/{program}/subjects/{subject}/move-up', [ProgramController::class, 'moveSubjectUp'])->name('programs.subjects.move-up');
        Route::post('programs/{program}/subjects/{subject}/move-down', [ProgramController::class, 'moveSubjectDown'])->name('programs.subjects.move-down');
        Route::resource('programs', ProgramController::class);

        // Управление предметами (глобальными курсами)
        Route::post('subjects/{subject}/programs/attach', [SubjectController::class, 'attachProgram'])->name('subjects.programs.attach');
        Route::post('subjects/{subject}/programs/{program}/detach', [SubjectController::class, 'detachProgram'])->name('subjects.programs.detach');
        Route::resource('subjects', SubjectController::class);

        // Управление курсами
        Route::post('courses/{course}/duplicate', [CourseController::class, 'duplicate'])->name('courses.duplicate');
        Route::post('courses/bulk-destroy', [CourseController::class, 'bulkDestroy'])->name('courses.bulk-destroy');
        Route::resource('courses', CourseController::class);

        // События
        Route::resource('events', AdminEventController::class);
        Route::post('events/{event}/toggle-published', [AdminEventController::class, 'togglePublished'])->name('events.toggle-published');
        Route::post('events/{event}/toggle-featured', [AdminEventController::class, 'toggleFeatured'])->name('events.toggle-featured');

        // Отзывы
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::get('reviews/pending', [AdminReviewController::class, 'pending'])->name('reviews.pending');
        Route::get('reviews/approved', [AdminReviewController::class, 'approved'])->name('reviews.approved');
        Route::get('reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
        Route::post('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

        // Шаблоны сертификатов
        Route::resource('certificate-templates', \App\Http\Controllers\Admin\CertificateTemplateController::class);

        // Архив пользователей
        Route::get('user-archive', [UserArchiveController::class, 'index'])->name('user-archive.index');
        Route::get('user-archive/{user}', [UserArchiveController::class, 'show'])->name('user-archive.show');
        Route::get('user-archive/{user}/certificates/{certificate}/download', [UserArchiveController::class, 'downloadCertificate'])->name('user-archive.download-certificate');

        // Аналитика (только для администраторов)
        Route::get('analytics', [CourseAnalyticsController::class, 'index'])->name('analytics.index');
        Route::post('analytics/sync', [CourseAnalyticsController::class, 'sync'])->name('analytics.sync');
        Route::post('analytics/sync-chunk', [CourseAnalyticsController::class, 'syncChunk'])->name('analytics.sync-chunk');
        Route::get('analytics/export/csv', [CourseAnalyticsController::class, 'exportCsv'])->name('analytics.export.csv');
        Route::get('analytics/export/excel', [CourseAnalyticsController::class, 'exportExcel'])->name('analytics.export.excel');
        Route::get('analytics/export/pdf', [CourseAnalyticsController::class, 'exportPdf'])->name('analytics.export.pdf');

        // Статистика преподавателей (только для администраторов)
        Route::get('instructor-stats', [InstructorStatsController::class, 'index'])->name('instructor-stats.index');
        Route::get('instructor-stats/{instructor}', [InstructorStatsController::class, 'show'])->name('instructor-stats.show');

        // Синхронизация с Moodle (требует право sync_moodle)
        Route::middleware(['check.permission:sync_moodle'])->group(function () {
            Route::get('moodle-sync', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'index'])->name('moodle-sync.index');
            Route::post('moodle-sync/courses', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'syncCourses'])->name('moodle-sync.sync-courses');
            Route::post('moodle-sync/sync-chunk', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'syncChunk'])->name('moodle-sync.sync-chunk');
            Route::post('moodle-sync/enrollments/{course}', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'syncCourseEnrollments'])->name('moodle-sync.sync-enrollments');
            Route::post('moodle-sync/activities/{course}', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'syncCourseActivities'])->name('moodle-sync.sync-activities');
            Route::post('moodle-sync/all', [\App\Http\Controllers\Admin\MoodleSyncController::class, 'syncAll'])->name('moodle-sync.sync-all');
        });

        // Системные настройки (только для администраторов)
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'store'])->name('settings.store');

        // Управление резервными копиями
        Route::resource('backups', BackupController::class);
        Route::post('backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('backups/{filename}/restore-table/{table}', [BackupController::class, 'restoreTable'])->name('backups.restore-table');
        Route::get('backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/clear-tables', [BackupController::class, 'clearTables'])->name('backups.clear-tables');

        // Настройки пользователя
        Route::post('/save-theme-preference', [\App\Http\Controllers\Admin\SettingsController::class, 'saveThemePreference'])->name('save-theme-preference');
        Route::get('/user-settings', [\App\Http\Controllers\Admin\SettingsController::class, 'getUserSettings'])->name('user-settings');
        Route::post('/save-interface-settings', [\App\Http\Controllers\Admin\SettingsController::class, 'saveInterfaceSettings'])->name('save-interface-settings');
    });
});
