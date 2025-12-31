<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseActivity;
use App\Models\StudentActivityProgress;
use App\Models\StudentActivityHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для синхронизации элементов курса и прогресса студентов из Moodle
 * 
 * Синхронизирует все элементы курса (assignments, quizzes, forums, resources)
 * и прогресс студентов по этим элементам
 */
class CourseActivitySyncService
{
    /**
     * Сервис для работы с Moodle API
     * 
     * @var MoodleApiService
     */
    protected MoodleApiService $moodleApi;

    /**
     * Конструктор
     * 
     * @param MoodleApiService|null $moodleApi
     */
    public function __construct(?MoodleApiService $moodleApi = null)
    {
        try {
            $this->moodleApi = $moodleApi ?? new MoodleApiService();
        } catch (\Exception $e) {
            Log::error('Ошибка инициализации MoodleApiService в CourseActivitySyncService', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Синхронизировать все элементы курса из Moodle
     * 
     * @param int $courseId ID курса в локальной БД
     * @return array Статистика синхронизации
     */
    public function syncCourseActivities(int $courseId): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'errors_list' => []
        ];

        $course = Course::find($courseId);
        
        if (!$course) {
            throw new \Exception("Курс с ID {$courseId} не найден");
        }

        if (!$course->moodle_course_id) {
            Log::warning('У курса отсутствует moodle_course_id, пропускаем синхронизацию элементов', [
                'course_id' => $courseId
            ]);
            return $stats;
        }

        Log::info('Начало синхронизации элементов курса из Moodle', [
            'course_id' => $courseId,
            'moodle_course_id' => $course->moodle_course_id
        ]);

        try {
            // Получаем все активности курса из Moodle
            $activities = $this->moodleApi->getAllCourseActivities($course->moodle_course_id, 0);
            
            if ($activities === false) {
                Log::warning('Не удалось получить активности курса из Moodle', [
                    'moodle_course_id' => $course->moodle_course_id
                ]);
                return $stats;
            }

            $stats['total'] = count($activities);

            foreach ($activities as $activity) {
                try {
                    $result = $this->syncActivity($course, $activity);
                    
                    if ($result['created']) {
                        $stats['created']++;
                    } elseif ($result['updated']) {
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'activity_type' => $activity['type'] ?? 'unknown',
                        'moodle_id' => $activity['moodle_id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Ошибка синхронизации элемента курса', [
                        'course_id' => $courseId,
                        'activity' => $activity,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Синхронизация элементов курса завершена', $stats);

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при синхронизации элементов курса', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Синхронизировать один элемент курса
     * 
     * @param Course $course Курс
     * @param array $activityData Данные элемента из Moodle
     * @return array Результат синхронизации
     */
    protected function syncActivity(Course $course, array $activityData): array
    {
        $moodleActivityId = $activityData['moodle_id'] ?? null;
        $activityType = $activityData['type'] ?? 'other';
        
        if (!$moodleActivityId) {
            throw new \Exception('Отсутствует ID элемента в данных Moodle');
        }

        // Ищем существующий элемент по moodle_activity_id и типу
        $activity = CourseActivity::where('course_id', $course->id)
            ->where('moodle_activity_id', $moodleActivityId)
            ->where('activity_type', $activityType)
            ->first();

        // Подготавливаем данные для создания/обновления
        $activityDataToSave = [
            'course_id' => $course->id,
            'moodle_activity_id' => $moodleActivityId,
            'activity_type' => $activityType,
            'name' => $activityData['name'] ?? 'Без названия',
            'section_name' => $activityData['section_name'] ?? null,
            'max_grade' => $activityData['max_grade'] ?? $activityData['grade'] ?? null,
            'description' => $activityData['description'] ?? null,
            'meta' => $activityData,
        ];

        if ($activity) {
            // Обновляем существующий элемент
            $activity->update($activityDataToSave);
            return ['created' => false, 'updated' => true, 'activity' => $activity];
        } else {
            // Создаем новый элемент
            $activity = CourseActivity::create($activityDataToSave);
            return ['created' => true, 'updated' => false, 'activity' => $activity];
        }
    }

    /**
     * Синхронизировать прогресс студента по элементам курса
     * 
     * @param int $courseId ID курса в локальной БД
     * @param int $userId ID студента в локальной БД
     * @return array Статистика синхронизации
     */
    public function syncStudentProgress(int $courseId, int $userId): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'errors_list' => []
        ];

        $course = Course::find($courseId);
        $user = User::find($userId);
        
        if (!$course) {
            throw new \Exception("Курс с ID {$courseId} не найден");
        }

        if (!$user) {
            throw new \Exception("Пользователь с ID {$userId} не найден");
        }

        if (!$course->moodle_course_id || !$user->moodle_user_id) {
            Log::warning('Отсутствует moodle_course_id или moodle_user_id, пропускаем синхронизацию прогресса', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'has_moodle_course_id' => !empty($course->moodle_course_id),
                'has_moodle_user_id' => !empty($user->moodle_user_id)
            ]);
            return $stats;
        }

        Log::info('Начало синхронизации прогресса студента', [
            'course_id' => $courseId,
            'user_id' => $userId,
            'moodle_course_id' => $course->moodle_course_id,
            'moodle_user_id' => $user->moodle_user_id
        ]);

        try {
            // Получаем все активности курса с их статусами для студента
            Log::info('Запрос активностей курса из Moodle', [
                'moodle_course_id' => $course->moodle_course_id,
                'moodle_user_id' => $user->moodle_user_id,
                'course_id' => $course->id,
                'user_id' => $user->id
            ]);
            
            $activities = $this->moodleApi->getAllCourseActivities($course->moodle_course_id, $user->moodle_user_id);
            
            if ($activities === false) {
                Log::warning('Не удалось получить активности курса из Moodle (вернул false)', [
                    'moodle_course_id' => $course->moodle_course_id,
                    'moodle_user_id' => $user->moodle_user_id
                ]);
                return $stats;
            }

            Log::info('Получены активности из Moodle', [
                'moodle_course_id' => $course->moodle_course_id,
                'moodle_user_id' => $user->moodle_user_id,
                'activities_count' => count($activities),
                'activities_types' => array_unique(array_column($activities, 'type'))
            ]);

            $stats['total'] = count($activities);
            
            if (empty($activities)) {
                Log::warning('Moodle API вернул пустой массив активностей', [
                    'moodle_course_id' => $course->moodle_course_id,
                    'moodle_user_id' => $user->moodle_user_id,
                    'course_id' => $course->id,
                    'user_id' => $user->id
                ]);
            }

            foreach ($activities as $activityData) {
                try {
                    $moodleActivityId = $activityData['moodle_id'] ?? null;
                    $activityType = $activityData['type'] ?? 'other';
                    
                    if (!$moodleActivityId) {
                        continue;
                    }

                    // Находим элемент курса в локальной БД
                    $activity = CourseActivity::where('course_id', $course->id)
                        ->where('moodle_activity_id', $moodleActivityId)
                        ->where('activity_type', $activityType)
                        ->first();

                    if (!$activity) {
                        // Если элемента нет, создаем его
                        $activity = $this->syncActivity($course, $activityData);
                        $activity = $activity['activity'];
                    }

                    // Определяем статус прогресса
                    $status = $this->mapStatus($activityData['status'] ?? 'not_started');
                    $grade = $activityData['grade'] ?? null;
                    $maxGrade = $activityData['max_grade'] ?? $activity->max_grade;
                    
                    // Преобразуем timestamp в datetime для submitted_at и graded_at
                    $submittedAt = null;
                    if (isset($activityData['submitted_at']) && $activityData['submitted_at']) {
                        $submittedAt = is_numeric($activityData['submitted_at']) 
                            ? \Carbon\Carbon::createFromTimestamp($activityData['submitted_at'])
                            : $activityData['submitted_at'];
                    }
                    
                    $gradedAt = null;
                    if (isset($activityData['graded_at']) && $activityData['graded_at']) {
                        $gradedAt = is_numeric($activityData['graded_at'])
                            ? \Carbon\Carbon::createFromTimestamp($activityData['graded_at'])
                            : $activityData['graded_at'];
                    }

                    // Ищем существующий прогресс
                    $progress = StudentActivityProgress::where('user_id', $user->id)
                        ->where('course_id', $course->id)
                        ->where('activity_id', $activity->id)
                        ->first();

                    $progressData = [
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'activity_id' => $activity->id,
                        'status' => $status,
                        'grade' => $grade,
                        'max_grade' => $maxGrade,
                        'submitted_at' => $submittedAt,
                        'graded_at' => $gradedAt,
                        'progress_data' => $activityData,
                    ];

                    if ($progress) {
                        // Обновляем существующий прогресс
                        $progress->update($progressData);
                        $stats['updated']++;
                        
                        // Создаем запись в истории, если статус изменился
                        if ($progress->status !== $status) {
                            $this->createHistoryRecord($user, $course, $activity, $status, $activityData);
                        }
                    } else {
                        // Создаем новый прогресс
                        $progressData['started_at'] = $activityData['submitted_at'] ?? now();
                        StudentActivityProgress::create($progressData);
                        $stats['created']++;
                        
                        // Создаем запись в истории
                        $this->createHistoryRecord($user, $course, $activity, $status, $activityData);
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'activity_type' => $activityData['type'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Ошибка синхронизации прогресса студента', [
                        'course_id' => $courseId,
                        'user_id' => $userId,
                        'activity' => $activityData,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Синхронизация прогресса студента завершена', $stats);

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при синхронизации прогресса студента', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Синхронизировать историю действий студента
     * 
     * @param int $courseId ID курса в локальной БД
     * @param int $userId ID студента в локальной БД
     * @return array Статистика синхронизации
     */
    public function syncStudentHistory(int $courseId, int $userId): array
    {
        // История действий создается автоматически при синхронизации прогресса
        // Этот метод можно использовать для дополнительной синхронизации из Moodle logs
        // Пока возвращаем пустую статистику
        
        return [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'errors_list' => []
        ];
    }

    /**
     * Полная синхронизация для всех курсов и студентов
     * 
     * @return array Статистика синхронизации
     */
    public function syncAll(): array
    {
        $stats = [
            'activities' => [],
            'progress' => []
        ];

        Log::info('Начало полной синхронизации элементов курса и прогресса студентов');

        // Синхронизируем элементы курсов
        $courses = Course::whereNotNull('moodle_course_id')->get();
        
        $totalActivities = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0
        ];

        foreach ($courses as $course) {
            try {
                $activityStats = $this->syncCourseActivities($course->id);
                
                $totalActivities['total'] += $activityStats['total'];
                $totalActivities['created'] += $activityStats['created'];
                $totalActivities['updated'] += $activityStats['updated'];
                $totalActivities['errors'] += $activityStats['errors'];
            } catch (\Exception $e) {
                Log::error('Ошибка синхронизации элементов курса', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                $totalActivities['errors']++;
            }
        }

        $stats['activities'] = $totalActivities;

        // Синхронизируем прогресс студентов
        $totalProgress = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0
        ];

        foreach ($courses as $course) {
            // Получаем всех студентов курса
            $students = $course->users()->whereNotNull('moodle_user_id')->get();
            
            foreach ($students as $student) {
                try {
                    $progressStats = $this->syncStudentProgress($course->id, $student->id);
                    
                    $totalProgress['total'] += $progressStats['total'];
                    $totalProgress['created'] += $progressStats['created'];
                    $totalProgress['updated'] += $progressStats['updated'];
                    $totalProgress['errors'] += $progressStats['errors'];
                } catch (\Exception $e) {
                    Log::error('Ошибка синхронизации прогресса студента', [
                        'course_id' => $course->id,
                        'user_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                    $totalProgress['errors']++;
                }
            }
        }

        $stats['progress'] = $totalProgress;

        Log::info('Полная синхронизация завершена', $stats);

        return $stats;
    }

    /**
     * Преобразовать статус из Moodle в локальный формат
     * 
     * @param string $moodleStatus Статус из Moodle
     * @return string Локальный статус
     */
    protected function mapStatus(string $moodleStatus): string
    {
        $statusMap = [
            'not_started' => 'not_started',
            'not_submitted' => 'not_started',
            'in_progress' => 'in_progress',
            'pending' => 'submitted',
            'submitted' => 'submitted',
            'graded' => 'graded',
            'completed' => 'completed',
            'not_participated' => 'not_started',
            'participated' => 'completed',
            'available' => 'not_started',
        ];

        return $statusMap[$moodleStatus] ?? 'not_started';
    }

    /**
     * Создать запись в истории действий
     * 
     * @param User $user Студент
     * @param Course $course Курс
     * @param CourseActivity $activity Элемент курса
     * @param string $status Статус
     * @param array $activityData Данные активности
     * @return void
     */
    protected function createHistoryRecord(User $user, Course $course, CourseActivity $activity, string $status, array $activityData): void
    {
        $actionType = $this->mapStatusToActionType($status, $activityData);
        
        StudentActivityHistory::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'activity_id' => $activity->id,
            'action_type' => $actionType,
            'action_data' => $activityData,
            'performed_by_user_id' => $activityData['graded_by_user_id'] ?? null,
            'description' => $this->generateHistoryDescription($actionType, $activityData),
            'created_at' => $activityData['submitted_at'] ?? $activityData['graded_at'] ?? now(),
        ]);
    }

    /**
     * Преобразовать статус в тип действия для истории
     * 
     * @param string $status Статус
     * @param array $activityData Данные активности
     * @return string Тип действия
     */
    protected function mapStatusToActionType(string $status, array $activityData): string
    {
        if ($status === 'graded' && isset($activityData['graded_at'])) {
            return 'graded';
        }
        
        if ($status === 'submitted' && isset($activityData['submitted_at'])) {
            return 'submitted';
        }
        
        if ($status === 'completed') {
            return 'completed';
        }
        
        if ($status === 'in_progress') {
            return 'started';
        }
        
        return 'updated';
    }

    /**
     * Сгенерировать описание для записи истории
     * 
     * @param string $actionType Тип действия
     * @param array $activityData Данные активности
     * @return string Описание
     */
    protected function generateHistoryDescription(string $actionType, array $activityData): string
    {
        $descriptions = [
            'started' => 'Начато выполнение',
            'submitted' => 'Работа сдана',
            'graded' => 'Работа проверена' . (isset($activityData['grade']) ? ', оценка: ' . $activityData['grade'] : ''),
            'completed' => 'Завершено',
            'viewed' => 'Просмотрено',
            'commented' => 'Добавлен комментарий',
            'updated' => 'Обновлено',
        ];

        return $descriptions[$actionType] ?? 'Действие выполнено';
    }
}

