<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseActivity;
use App\Models\StudentActivityProgress;
use App\Models\StudentActivityHistory;
use App\Services\CourseActivitySyncService;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для аналитики курсов и студентов
 * 
 * Предоставляет преподавателям инструменты для анализа прогресса студентов
 */
class CourseAnalyticsController extends Controller
{
    /**
     * Сервис синхронизации элементов курса
     *
     * @var CourseActivitySyncService
     */
    protected CourseActivitySyncService $syncService;

    /**
     * Конструктор
     */
    public function __construct()
    {
        try {
            $this->syncService = new CourseActivitySyncService();
        } catch (\Exception $e) {
            Log::error('Ошибка инициализации CourseActivitySyncService в контроллере', [
                'error' => $e->getMessage()
            ]);
            $this->syncService = null;
        }
    }

    /**
     * Главная страница аналитики с фильтрами
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Инициализируем переменные по умолчанию для предотвращения ошибок
        $courses = collect();
        $students = collect();
        $filteredData = [
            'activities' => collect(),
            'students' => collect(),
            'filters' => [],
            'stats' => ['total' => 0, 'not_started' => 0, 'submitted' => 0, 'graded' => 0, 'completed' => 0],
        ];
        $hasNoData = false;
        $noDataMessage = null;
        $moodleApiService = null;
        $hasAutoSynced = false;
        
        try {
            $user = auth()->user();
            
            if (!$user) {
                abort(403, 'Необходима авторизация');
            }
            
            // Проверяем доступ: только администраторы и преподаватели могут видеть аналитику
            if (!$user->hasRole('admin') && !$user->hasRole('instructor')) {
                abort(403, 'Недостаточно прав доступа. Аналитика доступна только администраторам и преподавателям.');
            }
            
            // Получаем курсы преподавателя (если не админ)
            $coursesQuery = Course::with(['instructor', 'program.institution']);
            
            if (!$user->hasRole('admin')) {
                // Преподаватель видит только свои курсы
                $coursesQuery->where('instructor_id', $user->id);
            }
            
            $courses = $coursesQuery->get();
            
            // Получаем список студентов для фильтра (до применения фильтров)
            $studentsQuery = User::whereHas('courses');
            if (!$user->hasRole('admin')) {
                $studentsQuery->whereHas('courses', function ($q) use ($user) {
                    $q->where('instructor_id', $user->id);
                })
                ->where('id', '!=', $user->id); // Исключаем самого преподавателя
            }
            $students = $studentsQuery->get();
            
            // Проверяем наличие данных в базе
            $totalProgressCount = \App\Models\StudentActivityProgress::count();
            Log::info('Проверка данных аналитики', [
                'total_progress_records' => $totalProgressCount,
                'courses_count' => $courses->count(),
                'students_count' => $students->count(),
                'request_params' => $request->all()
            ]);
            
            // Автоматическая синхронизация для записей со статусом "submitted"
            // Ограничиваем количество синхронизаций для предотвращения замедления загрузки
            if ($this->syncService) {
                try {
                    $submittedProgress = StudentActivityProgress::where('status', 'submitted')
                        ->with(['user', 'course'])
                        ->whereHas('user', function($q) {
                            $q->whereNotNull('moodle_user_id');
                        })
                        ->whereHas('course', function($q) {
                            $q->whereNotNull('moodle_course_id');
                        })
                        ->limit(10) // Ограничиваем до 10 синхронизаций за раз
                        ->get();
                    
                    if ($submittedProgress->count() > 0) {
                        Log::info('Автоматическая синхронизация прогресса для submitted записей', [
                            'count' => $submittedProgress->count()
                        ]);
                        
                        foreach ($submittedProgress as $progress) {
                            try {
                                $this->syncService->syncStudentProgress($progress->course_id, $progress->user_id);
                            } catch (\Exception $e) {
                                Log::warning('Ошибка синхронизации прогресса студента', [
                                    'course_id' => $progress->course_id,
                                    'user_id' => $progress->user_id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Ошибка автоматической синхронизации', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Инициализируем MoodleApiService для генерации URL и получения cmid
            $moodleApiService = null;
            try {
                // Используем токен текущего пользователя
                $user = auth()->user();
                $userToken = $user ? $user->getMoodleToken() : null;
                $moodleApiService = new MoodleApiService(null, $userToken);
            } catch (\Exception $e) {
                Log::warning('Не удалось инициализировать MoodleApiService', [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Применяем фильтры (передаем moodleApiService для получения cmid)
            $filteredData = $this->applyFilters($request, $courses, null, null, $students, $moodleApiService);
            
            // Проверяем, есть ли данные для выбранного студента
            $hasNoData = false;
            $noDataMessage = null;
            if (!empty($request->get('user_id'))) {
                $selectedUserId = (int)$request->get('user_id');
                $hasProgress = StudentActivityProgress::where('user_id', $selectedUserId)->exists();
                if (!$hasProgress) {
                    $hasNoData = true;
                    $selectedUser = User::find($selectedUserId);
                    $noDataMessage = $selectedUser 
                        ? "Для студента \"{$selectedUser->name}\" нет данных о прогрессе. Запустите синхронизацию данных из Moodle."
                        : "Для выбранного студента нет данных о прогрессе. Запустите синхронизацию данных из Moodle.";
                }
            } elseif ($totalProgressCount == 0) {
                $hasNoData = true;
                $noDataMessage = "В системе нет данных о прогрессе студентов. Запустите синхронизацию данных из Moodle.";
            }
            
            // Проверяем, была ли выполнена автоматическая синхронизация
            $hasAutoSynced = false;
            if ($this->syncService) {
                $submittedCount = StudentActivityProgress::where('status', 'submitted')
                    ->whereHas('user', function($q) {
                        $q->whereNotNull('moodle_user_id');
                    })
                    ->whereHas('course', function($q) {
                        $q->whereNotNull('moodle_course_id');
                    })
                    ->count();
                $hasAutoSynced = $submittedCount > 0;
            }
            
            return view('admin.analytics.index', [
                'courses' => $courses,
                'activities' => $filteredData['activities'],
                'students' => $filteredData['students'],
                'filters' => $filteredData['filters'],
                'stats' => $filteredData['stats'],
                'hasNoData' => $hasNoData,
                'noDataMessage' => $noDataMessage,
                'moodleApiService' => $moodleApiService,
                'hasAutoSynced' => $hasAutoSynced,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка в методе index контроллера аналитики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return view('admin.analytics.index', [
                'courses' => collect(),
                'activities' => collect(),
                'students' => collect(),
                'filters' => [],
                'stats' => ['total' => 0, 'not_started' => 0, 'submitted' => 0, 'graded' => 0, 'completed' => 0],
                'error' => 'Произошла ошибка при загрузке данных: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Детальная аналитика по курсу
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\View\View
     */
    public function course(Request $request, Course $course)
    {
        $user = auth()->user();
        
        // Проверка прав доступа
        if (!$user->hasRole('admin') && $course->instructor_id !== $user->id) {
            abort(403, 'У вас нет доступа к этому курсу');
        }
        
        // Получаем всех студентов курса
        $students = $course->users()->get();
        
        // Применяем фильтры
        $filteredData = $this->applyFilters($request, collect([$course]), $course->id);
        
        // Получаем все элементы курса с прогрессом
        $activities = CourseActivity::where('course_id', $course->id)
            ->with(['studentProgress.user', 'studentProgress.gradedBy'])
            ->get();
        
        // Статистика по курсу
        $stats = $this->getCourseStats($course);
        
        return view('admin.analytics.course', [
            'course' => $course,
            'activities' => $activities,
            'students' => $filteredData['students'],
            'filters' => $filteredData['filters'],
            'stats' => $stats,
        ]);
    }

    /**
     * Аналитика по конкретному студенту
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function student(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Получаем курсы студента
        $studentCourses = $user->courses()
            ->with(['instructor', 'program.institution'])
            ->get();
        
        // Если преподаватель, показываем только свои курсы
        if (!$currentUser->hasRole('admin')) {
            $studentCourses = $studentCourses->filter(function ($course) use ($currentUser) {
                return $course->instructor_id === $currentUser->id;
            });
        }
        
        // Получаем прогресс студента по всем элементам курса
        $progressData = [];
        foreach ($studentCourses as $course) {
            $activities = CourseActivity::where('course_id', $course->id)->get();
            
            foreach ($activities as $activity) {
                $progress = StudentActivityProgress::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('activity_id', $activity->id)
                    ->first();
                
                $history = StudentActivityHistory::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('activity_id', $activity->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $progressData[] = [
                    'course' => $course,
                    'activity' => $activity,
                    'progress' => $progress,
                    'history' => $history,
                ];
            }
        }
        
        // Применяем фильтры
        $filteredData = $this->applyFilters($request, $studentCourses, null, $user->id);
        
        return view('admin.analytics.student', [
            'student' => $user,
            'courses' => $studentCourses,
            'progressData' => $filteredData['activities'],
            'filters' => $filteredData['filters'],
        ]);
    }

    /**
     * Синхронизация данных из Moodle
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sync(Request $request)
    {
        try {
            // Увеличиваем время выполнения для синхронизации
            set_time_limit(1800); // 30 минут
            ini_set('max_execution_time', '1800');
            ini_set('memory_limit', '512M');
            ignore_user_abort(true);
            
            if (!headers_sent()) {
                header('X-Accel-Buffering: no');
            }
            
            // Проверяем авторизацию
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходима авторизация'
                ], 401);
            }
            
            // Получаем параметры, преобразуя пустые строки в null
            $courseId = $request->input('course_id');
            $userId = $request->input('user_id');
            
            // Преобразуем пустые строки в null
            $courseId = $courseId === '' || $courseId === '0' ? null : (int)$courseId;
            $userId = $userId === '' || $userId === '0' ? null : (int)$userId;
            
            Log::info('Запрос синхронизации аналитики', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'user' => auth()->user()->id,
                'request_method' => $request->method(),
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson()
            ]);
            
            if (!$this->syncService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сервис синхронизации недоступен. Проверьте настройки Moodle.'
                ], 500);
            }
            
            $stats = [];
            
            if ($courseId && $userId) {
                // Синхронизация конкретного курса и студента
                $course = Course::find($courseId);
                $user = User::find($userId);
                
                if (!$course) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Курс не найден'
                    ], 404);
                }
                
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Студент не найден'
                    ], 404);
                }
                
                try {
                    $activityStats = $this->syncService->syncCourseActivities($courseId);
                    $progressStats = $this->syncService->syncStudentProgress($courseId, $userId);
                    
                    $stats = [
                        'activities' => $activityStats,
                        'progress' => $progressStats
                    ];
                    
                    return response()->json([
                        'success' => true,
                        'message' => sprintf(
                            'Синхронизация завершена. Элементов: создано %d, обновлено %d. Прогресс: создано %d, обновлено %d.',
                            $activityStats['created'] ?? 0,
                            $activityStats['updated'] ?? 0,
                            $progressStats['created'] ?? 0,
                            $progressStats['updated'] ?? 0
                        ),
                        'stats' => $stats
                    ]);
                } catch (\Exception $syncException) {
                    Log::error('Ошибка при синхронизации курса и студента', [
                        'course_id' => $courseId,
                        'user_id' => $userId,
                        'error' => $syncException->getMessage(),
                        'file' => $syncException->getFile(),
                        'line' => $syncException->getLine()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Ошибка при синхронизации: ' . $syncException->getMessage()
                    ], 500);
                }
            } elseif ($courseId) {
                // Синхронизация конкретного курса
                $course = Course::find($courseId);
                if (!$course) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Курс не найден'
                    ], 404);
                }
                
                $activityStats = $this->syncService->syncCourseActivities($courseId);
                
                // Синхронизация прогресса всех студентов курса
                $students = $course->users()->whereNotNull('moodle_user_id')->get();
                
                $totalProgress = ['created' => 0, 'updated' => 0, 'total' => 0, 'errors' => 0];
                
                foreach ($students as $student) {
                    $progressStats = $this->syncService->syncStudentProgress($courseId, $student->id);
                    $totalProgress['created'] += $progressStats['created'] ?? 0;
                    $totalProgress['updated'] += $progressStats['updated'] ?? 0;
                    $totalProgress['total'] += $progressStats['total'] ?? 0;
                    $totalProgress['errors'] += $progressStats['errors'] ?? 0;
                }
                
                return response()->json([
                    'success' => true,
                    'message' => sprintf(
                        'Синхронизация курса завершена. Элементов: создано %d, обновлено %d. Прогресс студентов: создано %d, обновлено %d.',
                        $activityStats['created'] ?? 0,
                        $activityStats['updated'] ?? 0,
                        $totalProgress['created'],
                        $totalProgress['updated']
                    ),
                    'stats' => ['activities' => $activityStats, 'progress' => $totalProgress]
                ]);
            } elseif ($userId) {
                // Синхронизация конкретного студента по всем курсам
                $user = User::find($userId);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Студент не найден'
                    ], 404);
                }
                
                $courses = $user->courses()->whereNotNull('moodle_course_id')->get();
                
                $totalActivities = ['created' => 0, 'updated' => 0, 'errors' => 0];
                $totalProgress = ['created' => 0, 'updated' => 0, 'total' => 0, 'errors' => 0];
                
                foreach ($courses as $course) {
                    $activityStats = $this->syncService->syncCourseActivities($course->id);
                    $totalActivities['created'] += $activityStats['created'] ?? 0;
                    $totalActivities['updated'] += $activityStats['updated'] ?? 0;
                    $totalActivities['errors'] += $activityStats['errors'] ?? 0;
                    
                    $progressStats = $this->syncService->syncStudentProgress($course->id, $userId);
                    $totalProgress['created'] += $progressStats['created'] ?? 0;
                    $totalProgress['updated'] += $progressStats['updated'] ?? 0;
                    $totalProgress['total'] += $progressStats['total'] ?? 0;
                    $totalProgress['errors'] += $progressStats['errors'] ?? 0;
                }
                
                return response()->json([
                    'success' => true,
                    'message' => sprintf(
                        'Синхронизация студента завершена. Элементов: создано %d, обновлено %d. Прогресс: создано %d, обновлено %d.',
                        $totalActivities['created'],
                        $totalActivities['updated'],
                        $totalProgress['created'],
                        $totalProgress['updated']
                    ),
                    'stats' => ['activities' => $totalActivities, 'progress' => $totalProgress]
                ]);
            } else {
                // Полная синхронизация - возвращаем список курсов для последовательной обработки
                $coursesWithMoodle = Course::whereNotNull('moodle_course_id')->get();
                
                Log::info('Подготовка к пошаговой синхронизации', [
                    'courses_count' => $coursesWithMoodle->count(),
                    'total_courses' => Course::count()
                ]);
                
                if ($coursesWithMoodle->count() == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Нет курсов с настроенным moodle_course_id. Сначала выполните синхронизацию курсов из Moodle.'
                    ], 400);
                }
                
                // Формируем список курсов для синхронизации
                $coursesList = $coursesWithMoodle->map(function($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'moodle_course_id' => $course->moodle_course_id
                    ];
                })->toArray();
                
                return response()->json([
                    'success' => true,
                    'sync_type' => 'full',
                    'total_steps' => count($coursesList),
                    'courses' => $coursesList,
                    'message' => 'Начинаем пошаговую синхронизацию. Всего курсов: ' . count($coursesList)
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации при синхронизации аналитики', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации в контроллере аналитики', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Всегда возвращаем JSON, даже при ошибках
            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Синхронизация одного курса или студента (для пошаговой синхронизации)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncChunk(Request $request)
    {
        try {
            // Проверяем авторизацию
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходима авторизация'
                ], 401);
            }
            
            if (!$this->syncService) {
                return response()->json([
                    'success' => false,
                    'message' => 'Сервис синхронизации недоступен. Проверьте настройки Moodle.'
                ], 500);
            }
            
            // Получаем параметры
            $courseId = $request->input('course_id');
            $userId = $request->input('user_id');
            $step = (int)$request->input('step', 1);
            $totalSteps = (int)$request->input('total_steps', 1);
            
            // Преобразуем пустые строки в null
            $courseId = $courseId === '' || $courseId === '0' ? null : (int)$courseId;
            $userId = $userId === '' || $userId === '0' ? null : (int)$userId;
            
            Log::info('Запрос синхронизации части данных', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'step' => $step,
                'total_steps' => $totalSteps
            ]);
            
            $currentItem = null;
            $stats = [
                'activities' => ['created' => 0, 'updated' => 0, 'errors' => 0, 'errors_list' => []],
                'progress' => ['created' => 0, 'updated' => 0, 'errors' => 0, 'errors_list' => []]
            ];
            
            if ($courseId) {
                // Синхронизация одного курса
                $course = Course::find($courseId);
                if (!$course) {
                    return response()->json([
                        'success' => false,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'message' => 'Курс не найден'
                    ], 404);
                }
                
                $currentItem = [
                    'type' => 'course',
                    'id' => $course->id,
                    'name' => $course->name
                ];
                
                try {
                    // Синхронизируем элементы курса
                    $activityStats = $this->syncService->syncCourseActivities($courseId);
                    $stats['activities'] = array_merge($stats['activities'], $activityStats);
                    
                    // Если указан студент, синхронизируем его прогресс
                    if ($userId) {
                        $user = User::find($userId);
                        if ($user) {
                            $progressStats = $this->syncService->syncStudentProgress($courseId, $userId);
                            $stats['progress'] = array_merge($stats['progress'], $progressStats);
                        }
                    } else {
                        // Синхронизируем прогресс всех студентов курса
                        $students = $course->users()->whereNotNull('moodle_user_id')->get();
                        $totalProgress = ['created' => 0, 'updated' => 0, 'total' => 0, 'errors' => 0, 'errors_list' => []];
                        
                        foreach ($students as $student) {
                            try {
                                $progressStats = $this->syncService->syncStudentProgress($courseId, $student->id);
                                $totalProgress['created'] += $progressStats['created'] ?? 0;
                                $totalProgress['updated'] += $progressStats['updated'] ?? 0;
                                $totalProgress['total'] += $progressStats['total'] ?? 0;
                                $totalProgress['errors'] += $progressStats['errors'] ?? 0;
                                
                                // Собираем список ошибок
                                if (isset($progressStats['errors_list']) && is_array($progressStats['errors_list'])) {
                                    $totalProgress['errors_list'] = array_merge($totalProgress['errors_list'], $progressStats['errors_list']);
                                }
                            } catch (\Exception $studentSyncException) {
                                Log::warning('Ошибка синхронизации прогресса студента', [
                                    'course_id' => $courseId,
                                    'student_id' => $student->id,
                                    'error' => $studentSyncException->getMessage()
                                ]);
                                $totalProgress['errors']++;
                                $totalProgress['errors_list'][] = [
                                    'student_id' => $student->id,
                                    'error' => $studentSyncException->getMessage()
                                ];
                            }
                        }
                        
                        $stats['progress'] = $totalProgress;
                    }
                    
                    // Формируем сообщение с учетом ошибок
                    $hasErrors = ($stats['activities']['errors'] ?? 0) > 0 || ($stats['progress']['errors'] ?? 0) > 0;
                    $message = sprintf(
                        'Синхронизирован курс: %s. Элементов: создано %d, обновлено %d%s. Прогресс: создано %d, обновлено %d%s.',
                        $course->name,
                        $stats['activities']['created'] ?? 0,
                        $stats['activities']['updated'] ?? 0,
                        ($stats['activities']['errors'] ?? 0) > 0 ? ', ошибок: ' . $stats['activities']['errors'] : '',
                        $stats['progress']['created'] ?? 0,
                        $stats['progress']['updated'] ?? 0,
                        ($stats['progress']['errors'] ?? 0) > 0 ? ', ошибок: ' . $stats['progress']['errors'] : ''
                    );
                    
                    if ($hasErrors) {
                        $message .= ' Внимание: обнаружены ошибки при синхронизации. Проверьте детали в таблице.';
                    }
                    
                    return response()->json([
                        'success' => true,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'current_item' => $currentItem,
                        'stats' => $stats,
                        'has_more' => $step < $totalSteps,
                        'message' => $message,
                        'has_errors' => $hasErrors
                    ]);
                } catch (\Exception $syncException) {
                    Log::error('Ошибка при синхронизации курса (chunk)', [
                        'course_id' => $courseId,
                        'step' => $step,
                        'error' => $syncException->getMessage(),
                        'file' => $syncException->getFile(),
                        'line' => $syncException->getLine()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'current_item' => $currentItem,
                        'has_more' => $step < $totalSteps,
                        'message' => 'Ошибка при синхронизации курса: ' . $syncException->getMessage(),
                        'stats' => $stats
                    ]);
                }
            } elseif ($userId) {
                // Синхронизация одного студента по всем его курсам
                $user = User::find($userId);
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'message' => 'Студент не найден'
                    ], 404);
                }
                
                $currentItem = [
                    'type' => 'student',
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
                
                try {
                    $courses = $user->courses()->whereNotNull('moodle_course_id')->get();
                    
                    $totalActivities = ['created' => 0, 'updated' => 0, 'errors' => 0];
                    $totalProgress = ['created' => 0, 'updated' => 0, 'total' => 0, 'errors' => 0];
                    
                    foreach ($courses as $course) {
                        try {
                            $activityStats = $this->syncService->syncCourseActivities($course->id);
                            $totalActivities['created'] += $activityStats['created'] ?? 0;
                            $totalActivities['updated'] += $activityStats['updated'] ?? 0;
                            $totalActivities['errors'] += $activityStats['errors'] ?? 0;
                            
                            $progressStats = $this->syncService->syncStudentProgress($course->id, $userId);
                            $totalProgress['created'] += $progressStats['created'] ?? 0;
                            $totalProgress['updated'] += $progressStats['updated'] ?? 0;
                            $totalProgress['total'] += $progressStats['total'] ?? 0;
                            $totalProgress['errors'] += $progressStats['errors'] ?? 0;
                        } catch (\Exception $courseSyncException) {
                            Log::warning('Ошибка синхронизации курса студента', [
                                'course_id' => $course->id,
                                'user_id' => $userId,
                                'error' => $courseSyncException->getMessage()
                            ]);
                            $totalActivities['errors']++;
                            $totalProgress['errors']++;
                        }
                    }
                    
                    $stats['activities'] = $totalActivities;
                    $stats['progress'] = $totalProgress;
                    
                    $message = sprintf(
                        'Синхронизирован студент: %s. Элементов: создано %d, обновлено %d. Прогресс: создано %d, обновлено %d.',
                        $user->name,
                        $totalActivities['created'],
                        $totalActivities['updated'],
                        $totalProgress['created'],
                        $totalProgress['updated']
                    );
                    
                    return response()->json([
                        'success' => true,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'current_item' => $currentItem,
                        'stats' => $stats,
                        'has_more' => $step < $totalSteps,
                        'message' => $message
                    ]);
                } catch (\Exception $syncException) {
                    Log::error('Ошибка при синхронизации студента (chunk)', [
                        'user_id' => $userId,
                        'step' => $step,
                        'error' => $syncException->getMessage()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'step' => $step,
                        'total_steps' => $totalSteps,
                        'current_item' => $currentItem,
                        'has_more' => $step < $totalSteps,
                        'message' => 'Ошибка при синхронизации студента: ' . $syncException->getMessage(),
                        'stats' => $stats
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'step' => $step,
                    'total_steps' => $totalSteps,
                    'message' => 'Не указан курс или студент для синхронизации'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации части данных', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'step' => $request->input('step', 1),
                'total_steps' => $request->input('total_steps', 1),
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Экспорт данных в Excel
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportExcel(Request $request)
    {
        $filteredData = $this->applyFilters($request);
        
        $filename = 'analytics_' . date('Y-m-d_His') . '.xlsx';
        
        // Генерируем XML формат Excel (SpreadsheetML)
        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        $xml .= '<Worksheet ss:Name="Аналитика">' . "\n";
        $xml .= '<Table>' . "\n";
        
        // Заголовки
        $headers = [
            'Студент',
            'Email',
            'Курс',
            'Элемент курса',
            'Тип элемента',
            'Статус',
            'Оценка',
            'Макс. оценка',
            'Дата сдачи',
            'Дата проверки',
            'Проверил'
        ];
        
        $xml .= '<Row>' . "\n";
        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($header, ENT_XML1) . '</Data></Cell>' . "\n";
        }
        $xml .= '</Row>' . "\n";
        
        // Данные
        foreach ($filteredData['activities'] as $item) {
            $xml .= '<Row>' . "\n";
            $rowData = [
                $item['student_name'] ?? '',
                $item['student_email'] ?? '',
                $item['course_name'] ?? '',
                $item['activity_name'] ?? '',
                $item['activity_type'] ?? '',
                $item['status_text'] ?? ($item['status'] ?? ''),
                $item['grade'] ?? '',
                $item['max_grade'] ?? '',
                $item['submitted_at'] ?? '',
                $item['graded_at'] ?? '',
                $item['graded_by'] ?? '',
            ];
            
            foreach ($rowData as $cellData) {
                $type = is_numeric($cellData) ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($cellData, ENT_XML1) . '</Data></Cell>' . "\n";
            }
            $xml .= '</Row>' . "\n";
        }
        
        $xml .= '</Table>' . "\n";
        $xml .= '</Worksheet>' . "\n";
        $xml .= '</Workbook>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Экспорт данных в CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCsv(Request $request)
    {
        $filteredData = $this->applyFilters($request);
        
        $filename = 'analytics_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($filteredData) {
            $file = fopen('php://output', 'w');
            
            // Заголовки CSV
            fputcsv($file, [
                'Студент',
                'Email',
                'Курс',
                'Элемент курса',
                'Тип элемента',
                'Статус',
                'Оценка',
                'Макс. оценка',
                'Дата сдачи',
                'Дата проверки',
                'Проверил'
            ]);
            
            // Данные
            foreach ($filteredData['activities'] as $item) {
                fputcsv($file, [
                    $item['student_name'] ?? '',
                    $item['student_email'] ?? '',
                    $item['course_name'] ?? '',
                    $item['activity_name'] ?? '',
                    $item['activity_type'] ?? '',
                    $item['status'] ?? '',
                    $item['grade'] ?? '',
                    $item['max_grade'] ?? '',
                    $item['submitted_at'] ?? '',
                    $item['graded_at'] ?? '',
                    $item['graded_by'] ?? '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Экспорт данных в PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $filteredData = $this->applyFilters($request);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Аналитика курсов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px;
        }
        h1 {
            font-size: 18pt;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Аналитика курсов</h1>
    <p><strong>Дата экспорта:</strong> ' . date('d.m.Y H:i') . '</p>
    <p><strong>Всего записей:</strong> ' . count($filteredData['activities']) . '</p>
    
    <table>
        <thead>
            <tr>
                <th>Студент</th>
                <th>Email</th>
                <th>Курс</th>
                <th>Элемент курса</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Оценка</th>
                <th>Дата сдачи</th>
                <th>Дата проверки</th>
                <th>Проверил</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($filteredData['activities'] as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['student_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['student_email'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['course_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['activity_name'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['activity_type'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['status_text'] ?? ($item['status'] ?? '')) . '</td>';
            $html .= '<td>' . ($item['grade'] !== null ? $item['grade'] . ($item['max_grade'] ? '/' . $item['max_grade'] : '') : '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['submitted_at'] ?? '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['graded_at'] ?? '—') . '</td>';
            $html .= '<td>' . htmlspecialchars($item['graded_by'] ?? '—') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Сгенерировано системой EduManage</p>
    </div>
</body>
</html>';
        
        // Используем простой подход - возвращаем HTML, который можно сохранить как PDF через браузер
        // Или используем встроенную библиотеку, если доступна
        $filename = 'analytics_' . date('Y-m-d_His') . '.html';
        
        // Если доступна функция для генерации PDF, используем её
        // Иначе возвращаем HTML, который пользователь может сохранить как PDF через браузер
        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Применить фильтры к данным
     *
     * @param Request $request
     * @param \Illuminate\Support\Collection|null $courses
     * @param int|null $courseId
     * @param int|null $userId
     * @return array
     */
    protected function applyFilters(Request $request, $courses = null, ?int $courseId = null, ?int $userId = null, $students = null, $moodleApiService = null): array
    {
        // Получаем фильтры из запроса, преобразуя строки в числа где нужно
        $courseIdParam = $request->get('course_id', $courseId);
        $userIdParam = $request->get('user_id', $userId);
        
        // Преобразуем пустые строки в null
        $courseIdParam = ($courseIdParam === '' || $courseIdParam === '0' || $courseIdParam === null) ? null : (int)$courseIdParam;
        $userIdParam = ($userIdParam === '' || $userIdParam === '0' || $userIdParam === null) ? null : (int)$userIdParam;
        
        $filters = [
            'course_id' => $courseIdParam,
            'user_id' => $userIdParam,
            'course_search' => $request->get('course_search'),
            'student_search' => $request->get('student_search'),
            'student_email_search' => $request->get('student_email_search'),
            'student_id_search' => $request->get('student_id_search'),
            'activity_type' => $request->get('activity_type'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'min_grade' => $request->get('min_grade'),
            'max_grade' => $request->get('max_grade'),
        ];
        
        Log::info('Применение фильтров аналитики', [
            'request_params' => $request->all(),
            'processed_filters' => $filters
        ]);
        
        // Проверяем существование таблиц перед запросом
        try {
            if (!\Schema::hasTable('student_activity_progress')) {
                Log::warning('Таблица student_activity_progress не существует');
                return [
                    'activities' => collect(),
                    'pagination' => new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 50),
                    'students' => $students ?? collect(),
                    'filters' => $filters,
                    'stats' => ['total' => 0, 'not_started' => 0, 'submitted' => 0, 'graded' => 0, 'completed' => 0],
                ];
            }
        } catch (\Exception $e) {
            Log::error('Ошибка проверки таблиц', ['error' => $e->getMessage()]);
        }
        
        // Строим запрос для получения данных
        try {
            $query = StudentActivityProgress::with(['user', 'course', 'activity', 'gradedBy'])
                ->join('users', 'student_activity_progress.user_id', '=', 'users.id')
                ->join('courses', 'student_activity_progress.course_id', '=', 'courses.id')
                ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id')
                ->join('user_courses', function($join) {
                    $join->on('student_activity_progress.user_id', '=', 'user_courses.user_id')
                         ->on('student_activity_progress.course_id', '=', 'user_courses.course_id');
                })
                ->select('student_activity_progress.*');
            
            // Если преподаватель, показываем только его курсы и исключаем самого преподавателя (применяем ДО фильтров)
            $currentUser = auth()->user();
            if (!$currentUser->hasRole('admin')) {
                $query->where('courses.instructor_id', $currentUser->id)
                      ->where('student_activity_progress.user_id', '!=', $currentUser->id); // Исключаем самого преподавателя
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при построении запроса аналитики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Возвращаем пустые данные в случае ошибки
            return [
                'activities' => collect(),
                'pagination' => new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 50),
                'students' => $students ?? collect(),
                'filters' => $filters,
                'stats' => ['total' => 0, 'not_started' => 0, 'submitted' => 0, 'graded' => 0, 'completed' => 0],
            ];
        }
        
        // Применяем фильтры (проверяем на пустоту и null)
        if (!empty($filters['course_id'])) {
            $query->where('student_activity_progress.course_id', (int)$filters['course_id']);
            Log::info('Применен фильтр по курсу', ['course_id' => $filters['course_id']]);
        }
        
        // Поиск по названию курса (текстовый поиск)
        if (!empty($filters['course_search'])) {
            $query->where('courses.name', 'LIKE', '%' . $filters['course_search'] . '%');
            Log::info('Применен поиск по названию курса', ['course_search' => $filters['course_search']]);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('student_activity_progress.user_id', (int)$filters['user_id']);
            Log::info('Применен фильтр по студенту', [
                'user_id' => $filters['user_id'],
                'user_id_type' => gettype($filters['user_id'])
            ]);
            
            // Диагностика: проверяем, есть ли записи для этого студента
            $studentProgressCount = StudentActivityProgress::where('user_id', (int)$filters['user_id'])->count();
            Log::info('Диагностика фильтра по студенту', [
                'user_id' => $filters['user_id'],
                'student_progress_count' => $studentProgressCount,
                'user_exists' => User::where('id', (int)$filters['user_id'])->exists()
            ]);
        }
        
        // Поиск по имени студента (текстовый поиск)
        if (!empty($filters['student_search'])) {
            $query->where('users.name', 'LIKE', '%' . $filters['student_search'] . '%');
            Log::info('Применен поиск по имени студента', ['student_search' => $filters['student_search']]);
        }
        
        // Поиск по email студента
        if (!empty($filters['student_email_search'])) {
            $query->where('users.email', 'LIKE', '%' . $filters['student_email_search'] . '%');
            Log::info('Применен поиск по email студента', ['student_email_search' => $filters['student_email_search']]);
        }
        
        // Поиск по ID студента
        if (!empty($filters['student_id_search'])) {
            $query->where('student_activity_progress.user_id', (int)$filters['student_id_search']);
            Log::info('Применен поиск по ID студента', ['student_id_search' => $filters['student_id_search']]);
        }
        
        if (!empty($filters['activity_type'])) {
            $query->where('course_activities.activity_type', $filters['activity_type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('student_activity_progress.status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('student_activity_progress.submitted_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('student_activity_progress.submitted_at', '<=', $filters['date_to']);
        }
        
        if ($filters['min_grade'] !== null && $filters['min_grade'] !== '') {
            $query->where('student_activity_progress.grade', '>=', $filters['min_grade']);
        }
        
        if ($filters['max_grade'] !== null && $filters['max_grade'] !== '') {
            $query->where('student_activity_progress.grade', '<=', $filters['max_grade']);
        }
        
        // Логируем запрос для отладки
        try {
            Log::info('Запрос аналитики с фильтрами', [
                'filters' => $filters,
                'user_id' => $currentUser->id,
                'is_admin' => $currentUser->hasRole('admin'),
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            $activities = $query->paginate(50);
            
            Log::info('Результаты запроса аналитики', [
                'total' => $activities->total(),
                'count' => $activities->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка выполнения запроса аналитики', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Возвращаем пустые данные в случае ошибки
            $activities = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 50);
        }
        
        // Форматируем данные для отображения
        $formattedActivities = $activities->map(function ($progress) use ($moodleApiService) {
            // Извлекаем cmid из meta поля активности
            $cmid = null;
            if ($progress->activity && $progress->activity->meta) {
                $meta = is_array($progress->activity->meta) ? $progress->activity->meta : json_decode($progress->activity->meta, true);
                $cmid = $meta['cmid'] ?? null;
            }
            
            // Если cmid не найден в meta, пытаемся получить его через Moodle API
            if (!$cmid && $moodleApiService && $progress->activity && $progress->activity->moodle_activity_id && $progress->activity->activity_type && $progress->course && $progress->course->moodle_course_id) {
                try {
                    $moduleName = $progress->activity->activity_type;
                    // Преобразуем тип активности в название модуля Moodle
                    $moduleMap = [
                        'assign' => 'assign',
                        'quiz' => 'quiz',
                        'forum' => 'forum',
                    ];
                    
                    if (isset($moduleMap[$moduleName])) {
                        // Пытаемся получить cmid через core_course_get_course_module_by_instance
                        $cmResult = $moodleApiService->call('core_course_get_course_module_by_instance', [
                            'module' => $moduleMap[$moduleName],
                            'instance' => $progress->activity->moodle_activity_id
                        ]);
                        
                        if ($cmResult !== false && !isset($cmResult['exception']) && isset($cmResult['cm']['id'])) {
                            $cmid = $cmResult['cm']['id'];
                            
                            // Сохраняем cmid в meta для будущего использования
                            if ($progress->activity) {
                                $meta = is_array($progress->activity->meta) ? $progress->activity->meta : json_decode($progress->activity->meta, true);
                                if (!is_array($meta)) {
                                    $meta = [];
                                }
                                $meta['cmid'] = $cmid;
                                $progress->activity->meta = $meta;
                                $progress->activity->save();
                            }
                        } else {
                            // Альтернативный способ: используем getCourseAssignmentsWithStatus для заданий
                            if ($moduleName === 'assign' && $progress->user && $progress->user->moodle_user_id) {
                                try {
                                    $assignments = $moodleApiService->getCourseAssignmentsWithStatus(
                                        $progress->course->moodle_course_id,
                                        $progress->user->moodle_user_id,
                                        'ПОСЛЕ СЕССИИ'
                                    );
                                    
                                    if ($assignments !== false && is_array($assignments)) {
                                        foreach ($assignments as $assignment) {
                                            if (isset($assignment['id']) && $assignment['id'] == $progress->activity->moodle_activity_id) {
                                                if (isset($assignment['cmid']) && $assignment['cmid']) {
                                                    $cmid = $assignment['cmid'];
                                                    
                                                    // Сохраняем cmid в meta
                                                    if ($progress->activity) {
                                                        $meta = is_array($progress->activity->meta) ? $progress->activity->meta : json_decode($progress->activity->meta, true);
                                                        if (!is_array($meta)) {
                                                            $meta = [];
                                                        }
                                                        $meta['cmid'] = $cmid;
                                                        $progress->activity->meta = $meta;
                                                        $progress->activity->save();
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Игнорируем ошибки
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Игнорируем ошибки получения cmid
                }
            }
            
            return [
                'id' => $progress->id,
                'user_id' => $progress->user_id,
                'activity_id' => $progress->activity_id,
                'student_name' => $progress->user->name ?? '',
                'student_email' => $progress->user->email ?? '',
                'course_name' => $progress->course->name ?? '',
                'activity_name' => $progress->activity->name ?? '',
                'activity_type' => $progress->activity->activity_type ?? '',
                'moodle_activity_id' => $progress->activity->moodle_activity_id ?? null,
                'cmid' => $cmid,
                'moodle_user_id' => $progress->user->moodle_user_id ?? null,
                'moodle_course_id' => $progress->course->moodle_course_id ?? null,
                'status' => $progress->status,
                'status_text' => $this->getStatusText($progress->status),
                'grade' => $progress->grade,
                'max_grade' => $progress->max_grade,
                'submitted_at' => $progress->submitted_at ? $progress->submitted_at->format('d.m.Y H:i') : null,
                'graded_at' => $progress->graded_at ? $progress->graded_at->format('d.m.Y H:i') : null,
                'graded_by' => $progress->gradedBy->name ?? '',
            ];
        });
        
        // Получаем список студентов для фильтра
        $studentsQuery = User::whereHas('courses');
        if (!$currentUser->hasRole('admin')) {
            $studentsQuery->whereHas('courses', function ($q) use ($currentUser) {
                $q->where('instructor_id', $currentUser->id);
            })
            ->where('id', '!=', $currentUser->id); // Исключаем самого преподавателя
        }
        $students = $studentsQuery->get();
        
        // Статистика - создаем отдельный запрос без select для агрегации
        $statsQuery = StudentActivityProgress::query()
            ->join('users', 'student_activity_progress.user_id', '=', 'users.id')
            ->join('courses', 'student_activity_progress.course_id', '=', 'courses.id')
            ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id')
            ->join('user_courses', function($join) {
                $join->on('student_activity_progress.user_id', '=', 'user_courses.user_id')
                     ->on('student_activity_progress.course_id', '=', 'user_courses.course_id');
            });
        
        // Если преподаватель, показываем только его курсы и исключаем самого преподавателя (применяем ДО фильтров)
        if (!$currentUser->hasRole('admin')) {
            $statsQuery->where('courses.instructor_id', $currentUser->id)
                       ->where('student_activity_progress.user_id', '!=', $currentUser->id); // Исключаем преподавателя
        }
        
        // Применяем те же фильтры к запросу статистики (проверяем на пустоту и null)
        if (!empty($filters['course_id'])) {
            $statsQuery->where('student_activity_progress.course_id', (int)$filters['course_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $statsQuery->where('student_activity_progress.user_id', (int)$filters['user_id']);
        }
        
        if (!empty($filters['activity_type'])) {
            $statsQuery->where('course_activities.activity_type', $filters['activity_type']);
        }
        
        if (!empty($filters['status'])) {
            $statsQuery->where('student_activity_progress.status', $filters['status']);
        }
        
        if (!empty($filters['date_from'])) {
            $statsQuery->where('student_activity_progress.submitted_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $statsQuery->where('student_activity_progress.submitted_at', '<=', $filters['date_to']);
        }
        
        if ($filters['min_grade'] !== null && $filters['min_grade'] !== '') {
            $statsQuery->where('student_activity_progress.grade', '>=', $filters['min_grade']);
        }
        
        if ($filters['max_grade'] !== null && $filters['max_grade'] !== '') {
            $statsQuery->where('student_activity_progress.grade', '<=', $filters['max_grade']);
        }
        
        $stats = $this->calculateStats($statsQuery);
        
        return [
            'activities' => $formattedActivities,
            'pagination' => $activities,
            'students' => $students,
            'filters' => $filters,
            'stats' => $stats,
        ];
    }

    /**
     * Получить статистику по курсу
     *
     * @param Course $course
     * @return array
     */
    protected function getCourseStats(Course $course): array
    {
        $totalStudents = $course->users()->count();
        $totalActivities = CourseActivity::where('course_id', $course->id)->count();
        
        $progressStats = StudentActivityProgress::where('course_id', $course->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "not_started" THEN 1 ELSE 0 END) as not_started,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = "graded" THEN 1 ELSE 0 END) as graded,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                AVG(grade) as avg_grade
            ')
            ->first();
        
        return [
            'total_students' => $totalStudents,
            'total_activities' => $totalActivities,
            'not_started' => $progressStats->not_started ?? 0,
            'submitted' => $progressStats->submitted ?? 0,
            'graded' => $progressStats->graded ?? 0,
            'completed' => $progressStats->completed ?? 0,
            'avg_grade' => round($progressStats->avg_grade ?? 0, 2),
        ];
    }

    /**
     * Рассчитать общую статистику
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    protected function calculateStats($query): array
    {
        // Убираем select('student_activity_progress.*') и используем только агрегатные функции
        $baseQuery = clone $query;
        $baseQuery->getQuery()->columns = []; // Очищаем колонки
        
        $stats = $baseQuery->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN student_activity_progress.status = "not_started" THEN 1 ELSE 0 END) as not_started,
            SUM(CASE WHEN student_activity_progress.status = "submitted" THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN student_activity_progress.status = "graded" THEN 1 ELSE 0 END) as graded,
            SUM(CASE WHEN student_activity_progress.status = "completed" THEN 1 ELSE 0 END) as completed
        ')->first();
        
        return [
            'total' => $stats->total ?? 0,
            'not_started' => $stats->not_started ?? 0,
            'submitted' => $stats->submitted ?? 0,
            'graded' => $stats->graded ?? 0,
            'completed' => $stats->completed ?? 0,
        ];
    }

    /**
     * Получить текстовое представление статуса
     *
     * @param string $status
     * @return string
     */
    protected function getStatusText(string $status): string
    {
        $statusMap = [
            'not_started' => __('messages.not_started'),
            'in_progress' => __('messages.in_progress'),
            'pending' => __('messages.not_graded'),
            'submitted' => __('messages.submitted'),
            'graded' => __('messages.graded'),
            'completed' => __('messages.completed'),
        ];
        
        return $statusMap[$status] ?? $status;
    }
}

