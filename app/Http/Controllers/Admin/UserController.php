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
        $search = $request->input('search', '');
        $roleFilter = $request->input('role', '');
        $statusFilter = $request->input('status', '');

        // Используем select для оптимизации - загружаем только нужные поля
        $query = User::with('roles:id,name,slug')
            ->select('id', 'name', 'email', 'phone', 'is_active', 'created_at');

        // Поиск по имени, email или телефону
        // Оптимизация: используем индексы для быстрого поиска
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Для email используем точное совпадение начала (использует индекс)
                if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
                    $q->where('email', 'like', "{$search}%");
                } else {
                    // Для имени и телефона используем LIKE с началом строки (более эффективно)
                    $q->where('name', 'like', "{$search}%")
                      ->orWhere('name', 'like', "% {$search}%") // Поиск по словам
                      ->orWhere('phone', 'like', "{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                }
            });
        }

        // Фильтр по роли
        if ($roleFilter) {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('slug', $roleFilter);
            });
        }

        // Фильтр по статусу
        if ($statusFilter !== '') {
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
        $user->load([
            'roles',
            'taughtCourses.program.institution',
            'programs.institution',
            'courses.program.institution',
            'institutions'
        ]);
        return view('admin.users.show', compact('user'));
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
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
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
