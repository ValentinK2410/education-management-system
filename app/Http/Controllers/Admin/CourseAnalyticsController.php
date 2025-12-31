<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseActivity;
use App\Models\StudentActivityProgress;
use App\Models\StudentActivityHistory;
use App\Services\CourseActivitySyncService;
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
        $user = auth()->user();
        
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
            });
        }
        $students = $studentsQuery->get();
        
        // Применяем фильтры
        $filteredData = $this->applyFilters($request, $courses, null, null, $students);
        
        return view('admin.analytics.index', [
            'courses' => $courses,
            'activities' => $filteredData['activities'],
            'students' => $filteredData['students'],
            'filters' => $filteredData['filters'],
            'stats' => $filteredData['stats'],
        ]);
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
            // Получаем параметры, преобразуя пустые строки в null
            $courseId = $request->input('course_id');
            $userId = $request->input('user_id');
            
            // Преобразуем пустые строки в null
            $courseId = $courseId === '' || $courseId === '0' ? null : (int)$courseId;
            $userId = $userId === '' || $userId === '0' ? null : (int)$userId;
            
            Log::info('Запрос синхронизации аналитики', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'user' => auth()->user()->id
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
            } elseif ($courseId) {
                // Синхронизация конкретного курса
                $activityStats = $this->syncService->syncCourseActivities($courseId);
                
                // Синхронизация прогресса всех студентов курса
                $course = Course::find($courseId);
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
                // Полная синхронизация
                $stats = $this->syncService->syncAll();
                
                return response()->json([
                    'success' => true,
                    'message' => sprintf(
                        'Полная синхронизация завершена. Элементов: создано %d, обновлено %d. Прогресс: создано %d, обновлено %d.',
                        $stats['activities']['created'] ?? 0,
                        $stats['activities']['updated'] ?? 0,
                        $stats['progress']['created'] ?? 0,
                        $stats['progress']['updated'] ?? 0
                    ),
                    'stats' => $stats
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации в контроллере аналитики', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Экспорт данных в Excel
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel(Request $request)
    {
        // TODO: Реализовать экспорт в Excel после установки пакета maatwebsite/excel
        return redirect()->back()->with('error', 'Экспорт в Excel будет реализован после установки необходимых пакетов');
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
        // TODO: Реализовать экспорт в PDF после установки пакета barryvdh/laravel-dompdf
        return redirect()->back()->with('error', 'Экспорт в PDF будет реализован после установки необходимых пакетов');
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
    protected function applyFilters(Request $request, $courses = null, ?int $courseId = null, ?int $userId = null, $students = null): array
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
        
        // Строим запрос для получения данных
        $query = StudentActivityProgress::with(['user', 'course', 'activity', 'gradedBy'])
            ->join('users', 'student_activity_progress.user_id', '=', 'users.id')
            ->join('courses', 'student_activity_progress.course_id', '=', 'courses.id')
            ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id')
            ->select('student_activity_progress.*');
        
        // Если преподаватель, показываем только его курсы (применяем ДО фильтров)
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('admin')) {
            $query->where('courses.instructor_id', $currentUser->id);
        }
        
        // Применяем фильтры (проверяем на пустоту и null)
        if ($filters['course_id'] !== null && $filters['course_id'] !== '' && $filters['course_id'] !== '0') {
            $query->where('student_activity_progress.course_id', (int)$filters['course_id']);
        }
        
        if ($filters['user_id'] !== null && $filters['user_id'] !== '' && $filters['user_id'] !== '0') {
            $query->where('student_activity_progress.user_id', (int)$filters['user_id']);
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
        
        // Форматируем данные для отображения
        $formattedActivities = $activities->map(function ($progress) {
            return [
                'id' => $progress->id,
                'user_id' => $progress->user_id,
                'student_name' => $progress->user->name ?? '',
                'student_email' => $progress->user->email ?? '',
                'course_name' => $progress->course->name ?? '',
                'activity_name' => $progress->activity->name ?? '',
                'activity_type' => $progress->activity->activity_type ?? '',
                'status' => $progress->status,
                'status_text' => $this->getStatusText($progress->status),
                'grade' => $progress->grade,
                'max_grade' => $progress->max_grade,
                'submitted_at' => $progress->submitted_at?->format('d.m.Y H:i'),
                'graded_at' => $progress->graded_at?->format('d.m.Y H:i'),
                'graded_by' => $progress->gradedBy->name ?? '',
            ];
        });
        
        // Получаем список студентов для фильтра
        $studentsQuery = User::whereHas('courses');
        if (!$currentUser->hasRole('admin')) {
            $studentsQuery->whereHas('courses', function ($q) use ($currentUser) {
                $q->where('instructor_id', $currentUser->id);
            });
        }
        $students = $studentsQuery->get();
        
        // Статистика - создаем отдельный запрос без select для агрегации
        $statsQuery = StudentActivityProgress::query()
            ->join('users', 'student_activity_progress.user_id', '=', 'users.id')
            ->join('courses', 'student_activity_progress.course_id', '=', 'courses.id')
            ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id');
        
        // Если преподаватель, показываем только его курсы (применяем ДО фильтров)
        if (!$currentUser->hasRole('admin')) {
            $statsQuery->where('courses.instructor_id', $currentUser->id);
        }
        
        // Применяем те же фильтры к запросу статистики (проверяем на пустоту и null)
        if ($filters['course_id'] !== null && $filters['course_id'] !== '' && $filters['course_id'] !== '0') {
            $statsQuery->where('student_activity_progress.course_id', (int)$filters['course_id']);
        }
        
        if ($filters['user_id'] !== null && $filters['user_id'] !== '' && $filters['user_id'] !== '0') {
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
            'not_started' => 'Не начато',
            'in_progress' => 'В процессе',
            'submitted' => 'Сдано',
            'graded' => 'Проверено',
            'completed' => 'Завершено',
        ];
        
        return $statusMap[$status] ?? $status;
    }
}

