<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Course;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для панели управления
 *
 * Отображает разные блоки в зависимости от роли пользователя:
 * - Студент: его курсы, программы, прогресс
 * - Преподаватель: его курсы, студенты, статистика
 * - Админ: полная статистика системы и управление
 */
class DashboardController extends Controller
{
    /**
     * Отобразить панель управления
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Проверяем, переключен ли пользователь
        $isSwitched = session('is_switched', false);
        $originalUserId = session('original_user_id');

        // Определяем роль пользователя
        // Если админ переключился на другого пользователя, показываем dashboard для роли этого пользователя
        if ($user->hasRole('admin') && !$isSwitched) {
            return $this->adminDashboard($user);
        } elseif ($user->hasRole('instructor')) {
            return $this->instructorDashboard($user);
        } elseif ($user->hasRole('student')) {
            return $this->studentDashboard($user);
        }

        // По умолчанию показываем студентский dashboard
        return $this->studentDashboard($user);
    }

    /**
     * Dashboard для администратора
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    private function adminDashboard(User $user)
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

        return view('admin.dashboard', [
            'stats' => $stats,
            'roleStats' => $roleStats,
            'recentUsers' => $recentUsers,
            'userRole' => 'admin',
        ]);
    }

    /**
     * Dashboard для преподавателя
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    private function instructorDashboard(User $user)
    {
        // Курсы, которые ведет преподаватель
        $myCourses = $user->taughtCourses()
            ->with(['program.institution', 'users'])
            ->get();

        // Статистика преподавателя
        $stats = [
            'my_courses' => $myCourses->count(),
            'total_students' => $myCourses->sum(function ($course) {
                return $course->users()->count();
            }),
            'active_courses' => $myCourses->where('is_active', true)->count(),
        ];

        // Студенты на курсах преподавателя
        $myStudents = User::whereHas('courses', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
        })->with('roles')->distinct()->take(10)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'myCourses' => $myCourses,
            'myStudents' => $myStudents,
            'userRole' => 'instructor',
        ]);
    }

    /**
     * Dashboard для студента
     *
     * @param User $user
     * @return \Illuminate\View\View
     */
    private function studentDashboard(User $user)
    {
        // Активные курсы студента
        $myCourses = $user->courses()
            ->with(['program.institution', 'instructor'])
            ->wherePivot('status', 'active')
            ->get();

        // Завершенные курсы
        $completedCourses = $user->courses()
            ->with(['program.institution', 'instructor'])
            ->wherePivot('status', 'completed')
            ->get();

        // Активные программы
        $myPrograms = $user->programs()
            ->with('institution')
            ->wherePivot('status', 'active')
            ->get();

        // Завершенные программы
        $completedPrograms = $user->programs()
            ->with('institution')
            ->wherePivot('status', 'completed')
            ->get();

        // Статистика студента
        $stats = [
            'active_courses' => $myCourses->count(),
            'completed_courses' => $completedCourses->count(),
            'active_programs' => $myPrograms->count(),
            'completed_programs' => $completedPrograms->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'myCourses' => $myCourses,
            'completedCourses' => $completedCourses,
            'myPrograms' => $myPrograms,
            'completedPrograms' => $completedPrograms,
            'userRole' => 'student',
        ]);
    }
}
