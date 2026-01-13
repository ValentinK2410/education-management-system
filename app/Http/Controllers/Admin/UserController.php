<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Контроллер для управления пользователями
 *
 * Обеспечивает CRUD операции для пользователей в административной панели.
 * Управляет ролями пользователей и их правами доступа.
 */
class UserController extends Controller
{
    /**
     * Отобразить список всех пользователей
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser) {
            abort(403, 'Необходима авторизация');
        }
        
        // Проверяем, является ли пользователь преподавателем (но не администратором)
        $isInstructor = $currentUser->hasRole('instructor') && !$currentUser->hasRole('admin');
        
        $search = $request->input('search', '');
        $roleFilter = $request->input('role', '');
        $statusFilter = $request->input('status', '');

        // Загружаем пользователей с ролями
        // Примечание: убрали select() для избежания проблем с отношениями
        $query = User::with(['roles' => function($q) {
                $q->select('id', 'name', 'slug');
            }]);
        
        // Если пользователь - преподаватель, показываем только студентов его курсов
        if ($isInstructor) {
            $instructorCourseIds = $currentUser->taughtCourses()->pluck('id');
            
            // Получаем только пользователей, которые записаны на курсы преподавателя
            $query->whereHas('courses', function ($q) use ($instructorCourseIds) {
                $q->whereIn('courses.id', $instructorCourseIds);
            });
        }

        // Поиск по имени, email или телефону
        // Оптимизация: используем индексы для быстрого поиска
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Проверяем, содержит ли поисковый запрос символ @ (вероятно email)
                $isEmailSearch = strpos($search, '@') !== false;

                if ($isEmailSearch) {
                    // Если есть @, ищем по email (частичное совпадение)
                    $q->where('email', 'like', "%{$search}%");
                } else {
                    // Для имени и телефона используем LIKE с началом строки (более эффективно)
                    $q->where('name', 'like', "{$search}%")
                      ->orWhere('name', 'like', "% {$search}%") // Поиск по словам
                      ->orWhere('phone', 'like', "{$search}%");
                }

                // Всегда добавляем поиск по email (на случай частичного ввода без @)
                // Например, если пользователь вводит "gmail", мы найдем "user@gmail.com"
                $q->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Фильтр по роли
        if ($roleFilter) {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('slug', $roleFilter);
            });
        }

        // Фильтр по статусу (только если выбран конкретный статус, не "Все")
        if (!empty($statusFilter) && in_array($statusFilter, ['active', 'inactive'])) {
            $query->where('is_active', $statusFilter === 'active');
        }

        // Сортировка по имени с использованием индекса
        $users = $query->orderBy('name')
            ->orderBy('id') // Дополнительная сортировка для стабильности
            ->paginate(15)
            ->withQueryString();

        // Получаем список ролей для фильтра
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'search', 'roles', 'roleFilter', 'statusFilter'));
    }

    /**
     * Показать форму для создания нового пользователя
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Сохранить нового пользователя в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'city' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'church' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:255',
            'about_me' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'bio' => $request->bio,
            'city' => $request->city,
            'religion' => $request->religion,
            'church' => $request->church,
            'marital_status' => $request->marital_status,
            'education' => $request->education,
            'about_me' => $request->about_me,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('avatars', 'public');
            $data['photo'] = $path;
            \Log::info('Photo uploaded for new user', ['path' => $path]);
        }

        // Создание нового пользователя
        $user = User::create($data);

        // Назначение ролей пользователю
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан.');
    }

    /**
     * Отобразить конкретного пользователя
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        // Проверяем, является ли пользователь преподавателем (но не администратором)
        $isInstructor = $currentUser->hasRole('instructor') && !$currentUser->hasRole('admin');
        
        // Если преподаватель, проверяем, что студент записан на его курсы
        if ($isInstructor) {
            $instructorCourseIds = $currentUser->taughtCourses()->pluck('id');
            $userCourseIds = $user->courses()->pluck('courses.id');
            
            // Проверяем, есть ли пересечение курсов
            $hasAccess = $instructorCourseIds->intersect($userCourseIds)->isNotEmpty();
            
            if (!$hasAccess) {
                abort(403, 'Недостаточно прав доступа. Вы можете просматривать только студентов своих курсов.');
            }
        }
        
        $user->load([
            'roles',
            'taughtCourses.program.institution',
            'programs.institution',
            'courses.program.institution',
            'courses.instructor',
            'institutions'
        ]);
        
        // Получаем курсы для отображения (уже отфильтрованы для преподавателей)
        $instructorCourseIds = $isInstructor ? $currentUser->taughtCourses()->pluck('id') : collect();
        $coursesToShow = $isInstructor 
            ? $user->courses->filter(function($course) use ($instructorCourseIds) {
                return $instructorCourseIds->contains($course->id);
            })
            : $user->courses;
        
        // Получаем задания из Moodle для каждого курса студента
        $coursesWithAssignments = [];
        $moodleApiService = null;
        
        if ($user->moodle_user_id) {
            try {
                $moodleApiService = new \App\Services\MoodleApiService();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ошибка инициализации MoodleApiService в UserController', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        foreach ($coursesToShow as $course) {
            $assignmentsData = null;
            
            // Получаем задания только если есть moodle_user_id и moodle_course_id
            if ($moodleApiService && $user->moodle_user_id && $course->moodle_course_id) {
                try {
                    $assignments = $moodleApiService->getCourseAssignmentsWithStatus(
                        $course->moodle_course_id,
                        $user->moodle_user_id,
                        'ПОСЛЕ СЕССИИ'
                    );
                    
                    if ($assignments !== false && !empty($assignments)) {
                        $assignmentsData = $assignments;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Ошибка при получении заданий из Moodle в UserController', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $course->moodle_course_id,
                        'moodle_user_id' => $user->moodle_user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $coursesWithAssignments[$course->id] = $assignmentsData;
        }
        
        // Получаем детальную аналитику по всем элементам курса
        $detailedAnalytics = [];
        
        foreach ($coursesToShow as $course) {
            if (!$course->moodle_course_id) {
                continue;
            }
            
            // Получаем все элементы курса
            $activities = \App\Models\CourseActivity::where('course_id', $course->id)->get();
            
            foreach ($activities as $activity) {
                $progress = \App\Models\StudentActivityProgress::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('activity_id', $activity->id)
                    ->first();
                
                $history = \App\Models\StudentActivityHistory::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('activity_id', $activity->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $detailedAnalytics[] = [
                    'course' => $course,
                    'activity' => $activity,
                    'progress' => $progress,
                    'history' => $history,
                ];
            }
        }
        
        // Проверяем доступ к аналитике (только для администраторов и преподавателей)
        $hasAnalyticsAccess = $currentUser->hasRole('admin') || $currentUser->hasRole('instructor');
        
        return view('admin.users.show', compact('user', 'coursesWithAssignments', 'detailedAnalytics', 'hasAnalyticsAccess'));
    }

    /**
     * Показать форму для редактирования пользователя
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Обновить пользователя в базе данных
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'city' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'church' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:255',
            'about_me' => 'nullable|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bio' => $request->bio,
            'city' => $request->city,
            'religion' => $request->religion,
            'church' => $request->church,
            'marital_status' => $request->marital_status,
            'education' => $request->education,
            'about_me' => $request->about_me,
            'is_active' => $request->boolean('is_active'),
        ];

        // Обновление пароля только если он указан
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото, если есть
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $path = $request->file('photo')->store('avatars', 'public');
            $data['photo'] = $path;
            \Log::info('Photo uploaded for user', ['user_id' => $user->id, 'path' => $path]);
        }

        $user->update($data);
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен.');
    }

    /**
     * Удалить пользователя из базы данных
     *
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Проверяем зависимости перед удалением
        $canDelete = $user->canBeDeleted();
        
        if (!$canDelete['can_delete']) {
            return redirect()->back()
                ->with('error', $canDelete['message'])
                ->with('dependencies', $canDelete['dependencies']);
        }

        // Используем транзакцию для безопасности
        \DB::transaction(function () use ($user) {
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
    }

    /**
     * Массовое удаление пользователей
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:users,id',
        ]);

        $ids = $request->input('ids');
        $deletedCount = 0;
        $errors = [];

        foreach ($ids as $id) {
            try {
                // Нельзя удалить самого себя
                if ($id == auth()->id()) {
                    $errors[] = "Нельзя удалить самого себя";
                    continue;
                }

                $user = User::findOrFail($id);
                $user->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ошибка при удалении пользователя', [
                    'user_id' => $id,
                    'error' => $e->getMessage()
                ]);
                $errors[] = "Ошибка при удалении пользователя ID: {$id}";
            }
        }

        $message = "Успешно удалено пользователей: {$deletedCount} из " . count($ids);
        if (!empty($errors)) {
            $message .= "\nОшибки: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => $deletedCount > 0,
            'message' => $message
        ]);
    }

    /**
     * Получить статистику для панели управления
     *
     * @return array
     */
    public static function getDashboardStats()
    {
        $stats = [
            'users' => User::count(),
            'institutions' => Institution::count(),
            'programs' => Program::count(),
            'courses' => Course::count(),
        ];

        // Статистика по ролям
        $roleStats = [];
        $roles = Role::withCount('users')->get();
        foreach ($roles as $role) {
            $roleStats[$role->name] = $role->users_count;
        }

        // Последние пользователи
        $recentUsers = User::with('roles')->latest()->take(5)->get();

        return [
            'stats' => $stats,
            'roleStats' => $roleStats,
            'recentUsers' => $recentUsers,
        ];
    }
}
