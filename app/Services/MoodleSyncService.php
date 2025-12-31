<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для синхронизации курсов и записей студентов из Moodle
 * 
 * Синхронизирует данные из Moodle в локальную базу данных Laravel
 */
class MoodleSyncService
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
        $this->moodleApi = $moodleApi ?? new MoodleApiService();
    }

    /**
     * Синхронизировать все курсы из Moodle
     * 
     * @return array Статистика синхронизации
     */
    public function syncCourses(): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'errors_list' => []
        ];

        Log::info('Начало синхронизации курсов из Moodle');

        try {
            // Получаем все курсы из Moodle
            $moodleCourses = $this->moodleApi->getAllCourses();

            if ($moodleCourses === false || empty($moodleCourses)) {
                Log::warning('Не удалось получить курсы из Moodle или список пуст');
                return $stats;
            }

            $stats['total'] = count($moodleCourses);

            foreach ($moodleCourses as $moodleCourse) {
                try {
                    $result = $this->syncCourse($moodleCourse);
                    
                    if ($result['created']) {
                        $stats['created']++;
                    } elseif ($result['updated']) {
                        $stats['updated']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'course_id' => $moodleCourse['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Ошибка синхронизации курса', [
                        'moodle_course_id' => $moodleCourse['id'] ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Синхронизация курсов завершена', $stats);

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при синхронизации курсов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Синхронизировать один курс из Moodle
     * 
     * @param array $moodleCourse Данные курса из Moodle
     * @return array Результат синхронизации
     */
    public function syncCourse(array $moodleCourse): array
    {
        $moodleCourseId = $moodleCourse['id'] ?? null;
        
        if (!$moodleCourseId) {
            throw new \Exception('Отсутствует ID курса в данных Moodle');
        }

        // Ищем существующий курс по moodle_course_id
        $course = Course::where('moodle_course_id', $moodleCourseId)->first();

        // Подготавливаем данные для создания/обновления
        $courseData = [
            'moodle_course_id' => $moodleCourseId,
            'name' => $moodleCourse['fullname'] ?? 'Без названия',
            'code' => $moodleCourse['shortname'] ?? null,
            'description' => $moodleCourse['summary'] ?? null,
            'category_id' => $moodleCourse['categoryid'] ?? null,
            'category_name' => $moodleCourse['categoryname'] ?? null,
            'is_active' => true,
        ];

        // Конвертируем даты из timestamp
        if (!empty($moodleCourse['startdate'])) {
            $courseData['start_date'] = date('Y-m-d', $moodleCourse['startdate']);
        }
        
        if (!empty($moodleCourse['enddate'])) {
            $courseData['end_date'] = date('Y-m-d', $moodleCourse['enddate']);
        }

        if ($course) {
            // Обновляем существующий курс
            $course->update($courseData);
            
            Log::info('Курс обновлен из Moodle', [
                'course_id' => $course->id,
                'moodle_course_id' => $moodleCourseId,
                'name' => $course->name
            ]);
            
            return ['created' => false, 'updated' => true, 'course' => $course];
        } else {
            // Создаем новый курс
            $course = Course::create($courseData);
            
            Log::info('Курс создан из Moodle', [
                'course_id' => $course->id,
                'moodle_course_id' => $moodleCourseId,
                'name' => $course->name
            ]);
            
            return ['created' => true, 'updated' => false, 'course' => $course];
        }
    }

    /**
     * Синхронизировать записи студентов на курс
     * 
     * @param int $courseId ID курса в локальной БД
     * @return array Статистика синхронизации
     */
    public function syncCourseEnrollments(int $courseId): array
    {
        $stats = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'errors_list' => []
        ];

        $course = Course::find($courseId);
        
        if (!$course) {
            throw new \Exception("Курс с ID {$courseId} не найден");
        }

        if (!$course->moodle_course_id) {
            throw new \Exception("У курса отсутствует moodle_course_id");
        }

        Log::info('Начало синхронизации записей студентов на курс', [
            'course_id' => $courseId,
            'moodle_course_id' => $course->moodle_course_id
        ]);

        try {
            // Получаем список пользователей, записанных на курс в Moodle
            $moodleUsers = $this->moodleApi->getCourseEnrolledUsers($course->moodle_course_id);

            if ($moodleUsers === false || empty($moodleUsers)) {
                Log::warning('Не удалось получить пользователей курса из Moodle или список пуст', [
                    'moodle_course_id' => $course->moodle_course_id
                ]);
                return $stats;
            }

            $stats['total'] = count($moodleUsers);

            foreach ($moodleUsers as $moodleUser) {
                try {
                    $result = $this->syncUserEnrollment($course, $moodleUser);
                    
                    if ($result['created']) {
                        $stats['created']++;
                    } elseif ($result['updated']) {
                        $stats['updated']++;
                    } elseif ($result['skipped']) {
                        $stats['skipped']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'moodle_user_id' => $moodleUser['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('Ошибка синхронизации записи студента', [
                        'course_id' => $courseId,
                        'moodle_user_id' => $moodleUser['id'] ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Синхронизация записей студентов завершена', $stats);

        } catch (\Exception $e) {
            Log::error('Критическая ошибка при синхронизации записей студентов', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Синхронизировать запись одного пользователя на курс
     * 
     * @param Course $course Курс
     * @param array $moodleUser Данные пользователя из Moodle
     * @return array Результат синхронизации
     */
    public function syncUserEnrollment(Course $course, array $moodleUser): array
    {
        $moodleUserId = $moodleUser['id'] ?? null;
        
        if (!$moodleUserId) {
            throw new \Exception('Отсутствует ID пользователя в данных Moodle');
        }

        // Ищем пользователя по moodle_user_id или email
        $user = User::where('moodle_user_id', $moodleUserId)
            ->orWhere('email', $moodleUser['email'] ?? '')
            ->first();

        if (!$user) {
            // Пользователь не найден - пропускаем
            Log::warning('Пользователь не найден в локальной БД, пропускаем запись', [
                'moodle_user_id' => $moodleUserId,
                'email' => $moodleUser['email'] ?? null
            ]);
            
            return ['created' => false, 'updated' => false, 'skipped' => true];
        }

        // Проверяем, существует ли уже запись в user_courses
        $enrollment = DB::table('user_courses')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            // Запись уже существует - обновляем статус если нужно
            // По умолчанию не обновляем, только если статус был 'cancelled'
            if ($enrollment->status === 'cancelled') {
                DB::table('user_courses')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->update([
                        'status' => 'enrolled',
                        'updated_at' => now()
                    ]);
                
                Log::info('Запись студента на курс обновлена', [
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ]);
                
                return ['created' => false, 'updated' => true, 'skipped' => false];
            }
            
            return ['created' => false, 'updated' => false, 'skipped' => false];
        } else {
            // Создаем новую запись
            DB::table('user_courses')->insert([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'status' => 'enrolled',
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Запись студента на курс создана', [
                'user_id' => $user->id,
                'course_id' => $course->id
            ]);
            
            return ['created' => true, 'updated' => false, 'skipped' => false];
        }
    }

    /**
     * Полная синхронизация (курсы + записи студентов)
     * 
     * @return array Статистика синхронизации
     */
    public function syncAll(): array
    {
        $stats = [
            'courses' => [],
            'enrollments' => []
        ];

        Log::info('Начало полной синхронизации из Moodle');

        // Синхронизируем курсы
        $stats['courses'] = $this->syncCourses();

        // Синхронизируем записи студентов для всех курсов
        $courses = Course::whereNotNull('moodle_course_id')->get();
        
        $totalEnrollments = [
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        foreach ($courses as $course) {
            try {
                $enrollmentStats = $this->syncCourseEnrollments($course->id);
                
                $totalEnrollments['total'] += $enrollmentStats['total'];
                $totalEnrollments['created'] += $enrollmentStats['created'];
                $totalEnrollments['updated'] += $enrollmentStats['updated'];
                $totalEnrollments['skipped'] += $enrollmentStats['skipped'];
                $totalEnrollments['errors'] += $enrollmentStats['errors'];
            } catch (\Exception $e) {
                Log::error('Ошибка синхронизации записей для курса', [
                    'course_id' => $course->id,
                    'error' => $e->getMessage()
                ]);
                $totalEnrollments['errors']++;
            }
        }

        $stats['enrollments'] = $totalEnrollments;

        Log::info('Полная синхронизация завершена', $stats);

        return $stats;
    }
}

