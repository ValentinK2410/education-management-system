<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Course;
use App\Models\Role;
use App\Services\MoodleSyncService;
use App\Services\CourseActivitySyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        // Синхронизация теперь происходит асинхронно через AJAX
        // чтобы не блокировать загрузку страницы

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

        // Студенты на курсах преподавателя (только студенты, исключаем преподавателей и администраторов)
        $myStudents = User::whereHas('courses', function ($query) use ($user) {
            $query->where('instructor_id', $user->id);
        })
        ->whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })
        ->with('roles')
        ->distinct()
        ->take(10)
        ->get();

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

    /**
     * Синхронизировать данные пользователя из Moodle
     *
     * @param User $user
     * @return void
     */
    private function syncUserDataFromMoodle(User $user): void
    {
        // Проверяем, есть ли у пользователя moodle_user_id
        if (!$user->moodle_user_id) {
            Log::info('Пользователь не имеет moodle_user_id, пропускаем синхронизацию', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return;
        }

        try {
            $syncService = new MoodleSyncService();
            $moodleApi = new \App\Services\MoodleApiService();
            
            // Синхронизируем все курсы из Moodle (чтобы убедиться, что все курсы есть в локальной БД)
            Log::info('Начало синхронизации курсов из Moodle для пользователя', [
                'user_id' => $user->id,
                'moodle_user_id' => $user->moodle_user_id
            ]);
            
            $coursesStats = $syncService->syncCourses();
            
            Log::info('Синхронизация курсов завершена', [
                'user_id' => $user->id,
                'stats' => $coursesStats
            ]);

            // Получаем курсы пользователя из Moodle через API
            // Используем core_enrol_get_users_courses для получения курсов конкретного пользователя
            $userMoodleCourses = $moodleApi->call('core_enrol_get_users_courses', [
                'userid' => $user->moodle_user_id
            ]);
            
            if ($userMoodleCourses && is_array($userMoodleCourses) && !isset($userMoodleCourses['exception'])) {
                Log::info('Получены курсы пользователя из Moodle', [
                    'user_id' => $user->id,
                    'moodle_user_id' => $user->moodle_user_id,
                    'courses_count' => count($userMoodleCourses)
                ]);
                
                // Синхронизируем записи пользователя на каждый курс
                foreach ($userMoodleCourses as $moodleCourse) {
                    try {
                        $moodleCourseId = $moodleCourse['id'] ?? null;
                        if (!$moodleCourseId) {
                            continue;
                        }
                        
                        // Находим локальный курс
                        $localCourse = Course::where('moodle_course_id', $moodleCourseId)->first();
                        
                        if ($localCourse) {
                            // Синхронизируем запись пользователя на курс
                            $syncService->syncCourseEnrollments($localCourse->id);
                        } else {
                            Log::warning('Локальный курс не найден для синхронизации записи', [
                                'user_id' => $user->id,
                                'moodle_course_id' => $moodleCourseId,
                                'course_name' => $moodleCourse['fullname'] ?? 'Неизвестно'
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Ошибка при синхронизации записи на курс', [
                            'user_id' => $user->id,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                // Если метод не работает, используем альтернативный способ
                Log::info('Используем альтернативный способ синхронизации записей пользователя', [
                    'user_id' => $user->id,
                    'moodle_user_id' => $user->moodle_user_id
                ]);
                
                // Получаем все курсы и синхронизируем записи для тех, где есть пользователь
                $allMoodleCourses = $moodleApi->getAllCourses();
                
                if ($allMoodleCourses && is_array($allMoodleCourses)) {
                    foreach ($allMoodleCourses as $moodleCourse) {
                        try {
                            $moodleCourseId = $moodleCourse['id'] ?? null;
                            if (!$moodleCourseId) {
                                continue;
                            }
                            
                            // Получаем список пользователей курса
                            $enrolledUsers = $moodleApi->getCourseEnrolledUsers($moodleCourseId);
                            
                            // Проверяем, записан ли текущий пользователь на этот курс
                            $isEnrolled = false;
                            if ($enrolledUsers && is_array($enrolledUsers)) {
                                foreach ($enrolledUsers as $enrolledUser) {
                                    if (isset($enrolledUser['id']) && $enrolledUser['id'] == $user->moodle_user_id) {
                                        $isEnrolled = true;
                                        break;
                                    }
                                }
                            }
                            
                            // Если пользователь записан, синхронизируем запись
                            if ($isEnrolled) {
                                $localCourse = Course::where('moodle_course_id', $moodleCourseId)->first();
                                if ($localCourse) {
                                    $syncService->syncCourseEnrollments($localCourse->id);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Ошибка при синхронизации записи на курс (альтернативный способ)', [
                                'user_id' => $user->id,
                                'moodle_course_id' => $moodleCourse['id'] ?? null,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }

            // Синхронизируем прогресс по элементам курса для студента
            if ($user->hasRole('student')) {
                try {
                    $activitySyncService = new CourseActivitySyncService();
                    $userCourses = $user->courses()->whereNotNull('moodle_course_id')->get();
                    
                    foreach ($userCourses as $course) {
                        try {
                            $activitySyncService->syncStudentProgress($course->id, $user->id);
                        } catch (\Exception $e) {
                            Log::error('Ошибка синхронизации прогресса студента', [
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка инициализации CourseActivitySyncService', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\InvalidArgumentException $e) {
            // Конфигурация Moodle не настроена - это нормально, просто пропускаем синхронизацию
            Log::info('Moodle не настроен, пропускаем синхронизацию', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации данных пользователя из Moodle', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Асинхронная синхронизация данных пользователя из Moodle (AJAX)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync()
    {
        // Увеличиваем лимит времени выполнения для синхронизации
        set_time_limit(120); // 2 минуты
        
        $user = Auth::user();

        if (!$user->moodle_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не имеет moodle_user_id',
                'progress' => 100
            ]);
        }

        try {
            // Оптимизированная синхронизация - только для курсов пользователя
            $moodleApi = new \App\Services\MoodleApiService();
            $syncService = new MoodleSyncService();
            
            // Пытаемся получить курсы пользователя из Moodle напрямую
            $userMoodleCourses = $moodleApi->call('core_enrol_get_users_courses', [
                'userid' => $user->moodle_user_id
            ]);
            
            // Проверяем результат - если ошибка доступа, используем альтернативный способ
            if ($userMoodleCourses === false || isset($userMoodleCourses['exception'])) {
                $errorCode = $userMoodleCourses['errorcode'] ?? '';
                $errorMessage = $userMoodleCourses['message'] ?? 'Не удалось получить курсы пользователя из Moodle';
                
                Log::warning('Не удалось получить курсы пользователя напрямую, используем альтернативный способ', [
                    'user_id' => $user->id,
                    'moodle_user_id' => $user->moodle_user_id,
                    'error' => $errorMessage,
                    'errorcode' => $errorCode
                ]);
                
                // Альтернативный способ: синхронизируем все курсы и затем фильтруем по пользователю
                // Сначала синхронизируем все курсы
                $syncService->syncCourses();
                
                // Затем синхронизируем записи для всех курсов
                // Это обновит записи пользователя на курсы, на которые он записан
                $allCourses = Course::whereNotNull('moodle_course_id')->get();
                foreach ($allCourses as $course) {
                    try {
                        $syncService->syncCourseEnrollments($course->id);
                    } catch (\Exception $e) {
                        Log::error('Ошибка синхронизации записей на курс', [
                            'course_id' => $course->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Получаем курсы пользователя из локальной БД
                $user->load('courses');
                $userCourses = $user->courses()->whereNotNull('moodle_course_id')->get();
                
                if ($userCourses->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'У пользователя нет курсов в Moodle. Возможно, требуется настроить права доступа для токена Moodle API.',
                        'progress' => 100
                    ]);
                }
                
                // Продолжаем синхронизацию прогресса для найденных курсов
                $userMoodleCourses = []; // Пустой массив, так как используем локальные курсы
            } else {
                // Фильтруем системный курс с id=1
                if (is_array($userMoodleCourses)) {
                    $userMoodleCourses = array_values(array_filter($userMoodleCourses, function($course) {
                        return isset($course['id']) && $course['id'] > 1;
                    }));
                }
                
                if (empty($userMoodleCourses)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'У пользователя нет курсов в Moodle',
                        'progress' => 100
                    ]);
                }
            }
            
            // Синхронизируем только курсы пользователя (если они были получены напрямую)
            if (!empty($userMoodleCourses)) {
                foreach ($userMoodleCourses as $moodleCourse) {
                    try {
                        $syncService->syncCourse($moodleCourse);
                    } catch (\Exception $e) {
                        Log::error('Ошибка синхронизации курса', [
                            'user_id' => $user->id,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Синхронизируем записи пользователя на курсы
                foreach ($userMoodleCourses as $moodleCourse) {
                    try {
                        $localCourse = Course::where('moodle_course_id', $moodleCourse['id'])->first();
                        if ($localCourse) {
                            $syncService->syncUserEnrollment($localCourse, [
                                'id' => $user->moodle_user_id,
                                'email' => $user->email
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Ошибка синхронизации записи на курс', [
                            'user_id' => $user->id,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Синхронизируем прогресс студента только по его курсам
            if ($user->hasRole('student')) {
                try {
                    $activitySyncService = new CourseActivitySyncService();
                    $user->load('courses');
                    
                    foreach ($user->courses as $course) {
                        if ($course->moodle_course_id) {
                            try {
                                $activitySyncService->syncStudentProgress($course->id, $user->id);
                            } catch (\Exception $e) {
                                Log::error('Ошибка синхронизации прогресса студента', [
                                    'user_id' => $user->id,
                                    'course_id' => $course->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка инициализации CourseActivitySyncService', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Синхронизация завершена успешно',
                'progress' => 100
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при асинхронной синхронизации данных пользователя', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage(),
                'progress' => 100
            ], 500);
        }
    }
}
