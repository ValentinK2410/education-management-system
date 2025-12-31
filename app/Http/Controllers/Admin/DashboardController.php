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
        $roleSwitched = session('role_switched', false);

        // Если переключены на роль, используем эту роль для определения dashboard
        if ($roleSwitched && session('switched_role_slug')) {
            $switchedRoleSlug = session('switched_role_slug');

            if ($switchedRoleSlug === 'admin') {
                return $this->adminDashboard($user);
            } elseif ($switchedRoleSlug === 'instructor') {
                return $this->instructorDashboard($user);
            } elseif ($switchedRoleSlug === 'student') {
                return $this->studentDashboard($user);
            }
        }

        // Определяем роль пользователя
        // Если админ переключился на другого пользователя, показываем dashboard для роли этого пользователя
        if ($user->hasRole('admin') && !$isSwitched && !$roleSwitched) {
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
        // Получаем все курсы студента (без фильтрации по статусу)
        $allCourses = $user->courses()
            ->with(['program.institution', 'instructor'])
            ->get();

        // Логируем для отладки
        \Illuminate\Support\Facades\Log::info('Dashboard: Курсы студента', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'total_courses' => $allCourses->count(),
            'course_ids' => $allCourses->pluck('id')->toArray()
        ]);

        // Разделяем на активные (enrolled или active) и завершенные
        $myCourses = $allCourses->filter(function ($course) {
            $status = $course->pivot->status ?? 'enrolled';
            return in_array($status, ['enrolled', 'active']);
        });

        $completedCourses = $allCourses->filter(function ($course) {
            return ($course->pivot->status ?? 'enrolled') === 'completed';
        });

        // Получаем задания из Moodle для каждого активного курса
        $coursesWithAssignments = [];
        $moodleApiService = null;
        
        if ($user->moodle_user_id) {
            try {
                $moodleApiService = new \App\Services\MoodleApiService();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ошибка инициализации MoodleApiService в Dashboard', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        foreach ($myCourses as $course) {
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
                    \Illuminate\Support\Facades\Log::error('Ошибка при получении заданий из Moodle в Dashboard', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $course->moodle_course_id,
                        'moodle_user_id' => $user->moodle_user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $coursesWithAssignments[$course->id] = $assignmentsData;
        }

        // Получаем все программы студента
        $allPrograms = $user->programs()
            ->with('institution')
            ->get();

        // Разделяем на активные (enrolled или active) и завершенные
        $myPrograms = $allPrograms->filter(function ($program) {
            $status = $program->pivot->status ?? 'enrolled';
            return in_array($status, ['enrolled', 'active']);
        });

        $completedPrograms = $allPrograms->filter(function ($program) {
            return ($program->pivot->status ?? 'enrolled') === 'completed';
        });

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
            'coursesWithAssignments' => $coursesWithAssignments,
            'userRole' => 'student',
        ]);
    }
}
