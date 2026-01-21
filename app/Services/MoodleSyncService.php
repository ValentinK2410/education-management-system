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
     * @throws \InvalidArgumentException Если конфигурация Moodle некорректна
     */
    public function __construct(?MoodleApiService $moodleApi = null)
    {
        try {
            $this->moodleApi = $moodleApi ?? new MoodleApiService();
        } catch (\InvalidArgumentException $e) {
            Log::error('Ошибка инициализации MoodleApiService', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Проверка конфигурации Moodle перед синхронизацией
     * 
     * @return bool true если конфигурация корректна
     * @throws \InvalidArgumentException Если конфигурация некорректна
     */
    public function validateConfiguration(): bool
    {
        $url = config('services.moodle.url', '');
        $token = config('services.moodle.token', '');
        
        if (empty($url)) {
            throw new \InvalidArgumentException(
                'MOODLE_URL не настроен в .env файле. ' .
                'Установите полный URL Moodle, например: MOODLE_URL=https://class.dekan.pro'
            );
        }
        
        if (!preg_match('/^https?:\/\//i', $url)) {
            throw new \InvalidArgumentException(
                "MOODLE_URL должен содержать протокол (http:// или https://). " .
                "Текущее значение: '{$url}'. " .
                "Пример правильного значения: https://class.dekan.pro"
            );
        }
        
        if (empty($token)) {
            throw new \InvalidArgumentException(
                'MOODLE_TOKEN не настроен в .env файле. ' .
                'Получите токен в Moodle: Site administration → Plugins → Web services → Manage tokens'
            );
        }
        
        return true;
    }

    /**
     * Получить список курсов из Moodle (без синхронизации)
     * 
     * @return array|false Массив курсов или false в случае ошибки
     */
    public function getMoodleCoursesList()
    {
        // Проверяем конфигурацию перед началом
        try {
            $this->validateConfiguration();
        } catch (\InvalidArgumentException $e) {
            Log::error('Ошибка конфигурации Moodle', [
                'error' => $e->getMessage()
            ]);
            return false;
        }

        try {
            // Получаем все курсы из Moodle
            $moodleCourses = $this->moodleApi->getAllCourses();

            if ($moodleCourses === false) {
                Log::error('Ошибка получения курсов из Moodle API', [
                    'hint' => 'Проверьте логи Moodle API для деталей ошибки.'
                ]);
                return false;
            }
            
            if (empty($moodleCourses)) {
                Log::warning('Список курсов из Moodle пуст');
                return [];
            }

            return $moodleCourses;
        } catch (\Exception $e) {
            Log::error('Критическая ошибка при получении списка курсов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
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
        
        // Проверяем конфигурацию перед началом синхронизации
        try {
            $this->validateConfiguration();
        } catch (\InvalidArgumentException $e) {
            Log::error('Ошибка конфигурации Moodle', [
                'error' => $e->getMessage()
            ]);
            $stats['errors']++;
            $stats['errors_list'][] = [
                'type' => 'configuration',
                'error' => $e->getMessage()
            ];
            return $stats;
        }

        try {
            // Получаем все курсы из Moodle
            $moodleCourses = $this->moodleApi->getAllCourses();

            Log::info('Результат getAllCourses', [
                'result_type' => gettype($moodleCourses),
                'is_array' => is_array($moodleCourses),
                'is_false' => $moodleCourses === false,
                'count' => is_array($moodleCourses) ? count($moodleCourses) : 0,
                'first_course' => is_array($moodleCourses) && !empty($moodleCourses) ? $moodleCourses[0] : null
            ]);

            if ($moodleCourses === false) {
                Log::error('Ошибка получения курсов из Moodle API', [
                    'result' => $moodleCourses,
                    'hint' => 'Проверьте логи Moodle API для деталей ошибки. Возможные причины: неправильный URL или токен, отсутствие прав у токена, проблемы с подключением к Moodle.'
                ]);
                $stats['errors']++;
                $stats['errors_list'][] = [
                    'type' => 'api_error',
                    'error' => 'Не удалось получить курсы из Moodle API. Проверьте логи для деталей. Возможные причины: неправильный URL или токен в .env, отсутствие прав у токена в Moodle, проблемы с подключением к Moodle.'
                ];
                return $stats;
            }
            
            if (empty($moodleCourses)) {
                Log::warning('Список курсов из Moodle пуст', [
                    'result' => $moodleCourses,
                    'hint' => 'Возможные причины: в Moodle нет курсов (кроме системного курса с id=1), токен не имеет прав на получение курсов, или все курсы были отфильтрованы. Проверьте права токена в Moodle: Site administration → Plugins → Web services → Manage tokens → [ваш токен] → Capabilities.'
                ]);
                // Не считаем это ошибкой, если это просто пустой результат
                // Возможно, все курсы уже синхронизированы или в Moodle действительно нет курсов
                $stats['errors_list'][] = [
                    'type' => 'empty_result',
                    'error' => 'В Moodle не найдено курсов для синхронизации. Убедитесь, что в Moodle есть курсы (кроме системного с id=1) и токен имеет права на их получение. Проверьте права токена в Moodle.'
                ];
                // Не возвращаем ошибку, просто возвращаем пустую статистику
                return $stats;
            }

            $stats['total'] = count($moodleCourses);

            foreach ($moodleCourses as $moodleCourse) {
                try {
                    Log::info('Синхронизация курса', [
                        'moodle_course_id' => $moodleCourse['id'] ?? null,
                        'course_name' => $moodleCourse['fullname'] ?? 'Без названия',
                        'course_data' => $moodleCourse
                    ]);
                    
                    $result = $this->syncCourse($moodleCourse);
                    
                    Log::info('Результат синхронизации курса', [
                        'moodle_course_id' => $moodleCourse['id'] ?? null,
                        'created' => $result['created'] ?? false,
                        'updated' => $result['updated'] ?? false,
                        'course_id' => $result['course']->id ?? null
                    ]);
                    
                    if (isset($result['created']) && $result['created']) {
                        $stats['created']++;
                        Log::info('Курс создан', [
                            'local_course_id' => $result['course']->id ?? null,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'course_name' => $moodleCourse['fullname'] ?? 'Без названия'
                        ]);
                    } elseif (isset($result['updated']) && $result['updated']) {
                        $stats['updated']++;
                        Log::info('Курс обновлен', [
                            'local_course_id' => $result['course']->id ?? null,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'course_name' => $moodleCourse['fullname'] ?? 'Без названия'
                        ]);
                    } else {
                        // Курс не изменился или был пропущен
                        Log::debug('Курс не изменился или уже синхронизирован', [
                            'local_course_id' => $result['course']->id ?? null,
                            'moodle_course_id' => $moodleCourse['id'] ?? null,
                            'course_name' => $moodleCourse['fullname'] ?? 'Без названия',
                            'result' => $result
                        ]);
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $stats['errors_list'][] = [
                        'course_id' => $moodleCourse['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ];
                    
                    Log::error('Ошибка синхронизации курса', [
                        'moodle_course_id' => $moodleCourse['id'] ?? null,
                        'course_name' => $moodleCourse['fullname'] ?? 'Без названия',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
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
            'program_id' => null, // Курсы из Moodle могут не иметь программы
            'is_active' => true,
        ];

        // Конвертируем даты из timestamp
        if (!empty($moodleCourse['startdate']) && $moodleCourse['startdate'] > 0) {
            try {
                $courseData['start_date'] = date('Y-m-d', $moodleCourse['startdate']);
            } catch (\Exception $e) {
                Log::warning('Ошибка конвертации startdate', [
                    'startdate' => $moodleCourse['startdate'] ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if (!empty($moodleCourse['enddate']) && $moodleCourse['enddate'] > 0) {
            try {
                $courseData['end_date'] = date('Y-m-d', $moodleCourse['enddate']);
            } catch (\Exception $e) {
                Log::warning('Ошибка конвертации enddate', [
                    'enddate' => $moodleCourse['enddate'] ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Получаем преподавателей курса из Moodle (может быть несколько)
        $resolvedInstructors = []; // [ ['user_id' => int, 'moodle_role_shortname' => string|null], ... ]
        $primaryInstructorId = null;
        try {
            $teachers = $this->moodleApi->getCourseTeachers($moodleCourseId);
            
            if ($teachers !== false && !empty($teachers)) {
                // Приоритет ролей для определения "основного" преподавателя.
                // По требованиям проекта НЕ учитываем ассистентов/не-редактирующих преподавателей,
                // поэтому здесь ключевой ролью считается editingteacher.
                $rolePriority = [
                    'editingteacher' => 3,
                ];

                foreach ($teachers as $moodleTeacher) {
                    $teacherEmail = $moodleTeacher['email'] ?? null;
                    $teacherMoodleId = $moodleTeacher['id'] ?? null;

                    if (!$teacherEmail && !$teacherMoodleId) {
                        continue;
                    }

                    // Определяем лучшую "преподавательскую" роль для этого пользователя (если roles есть)
                    $bestRole = null;
                    $bestRoleScore = 0;
                    if (isset($moodleTeacher['roles']) && is_array($moodleTeacher['roles'])) {
                        foreach ($moodleTeacher['roles'] as $role) {
                            $shortname = $role['shortname'] ?? null;
                            if (!$shortname) {
                                continue;
                            }
                            $score = $rolePriority[$shortname] ?? 0;
                            if ($score > $bestRoleScore) {
                                $bestRoleScore = $score;
                                $bestRole = $shortname;
                            }
                        }
                    }

                    // Ищем преподавателя в локальной БД по email или moodle_user_id
                    $instructorQuery = \App\Models\User::query();
                    if ($teacherEmail) {
                        $instructorQuery->where('email', $teacherEmail);
                    }
                    if ($teacherMoodleId) {
                        $instructorQuery->orWhere('moodle_user_id', $teacherMoodleId);
                    }
                    $instructor = $instructorQuery->first();

                    if (!$instructor) {
                        // Если преподаватель есть в Moodle, но отсутствует в системе — создаём пользователя (только для преподавателей).
                        // Требуем email, чтобы не создать дубликаты без идентификатора.
                        if (!$teacherEmail) {
                            Log::warning('Преподаватель курса не найден в локальной БД и отсутствует email (пропуск)', [
                                'course_id' => $moodleCourseId,
                                'teacher_moodle_id' => $teacherMoodleId
                            ]);
                            continue;
                        }

                        $teacherName = $moodleTeacher['fullname']
                            ?? trim(($moodleTeacher['firstname'] ?? '') . ' ' . ($moodleTeacher['lastname'] ?? ''))
                            ?: $teacherEmail;

                        try {
                            $instructor = \App\Models\User::create([
                                'name' => $teacherName,
                                'email' => $teacherEmail,
                                'password' => bin2hex(random_bytes(16)), // будет захеширован через cast
                                'is_active' => true,
                                'moodle_user_id' => $teacherMoodleId,
                            ]);

                            Log::info('Создан пользователь-преподаватель из Moodle', [
                                'user_id' => $instructor->id,
                                'email' => $teacherEmail,
                                'moodle_user_id' => $teacherMoodleId,
                                'course_id' => $moodleCourseId,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Не удалось создать пользователя-преподавателя из Moodle', [
                                'course_id' => $moodleCourseId,
                                'teacher_email' => $teacherEmail,
                                'teacher_moodle_id' => $teacherMoodleId,
                                'error' => $e->getMessage(),
                            ]);
                            continue;
                        }
                    }

                    // Проверяем, есть ли у пользователя роль преподавателя в системе
                    if (!$instructor->hasRole('instructor')) {
                        $instructorRole = \App\Models\Role::where('slug', 'instructor')->first();
                        if ($instructorRole) {
                            $instructor->roles()->syncWithoutDetaching([$instructorRole->id]);
                            Log::info('Добавлена роль преподавателя пользователю', [
                                'user_id' => $instructor->id,
                                'email' => $instructor->email
                            ]);
                        }
                    }

                    // Обновляем moodle_user_id если его не было
                    if (!$instructor->moodle_user_id && $teacherMoodleId) {
                        $instructor->update(['moodle_user_id' => $teacherMoodleId]);
                    }

                    $resolvedInstructors[] = [
                        'user_id' => $instructor->id,
                        'moodle_role_shortname' => $bestRole,
                        'role_score' => $bestRoleScore,
                    ];
                }

                // Определяем основного преподавателя:
                // - если курс уже существует и instructor_id входит в список найденных — сохраняем его
                // - иначе выбираем по приоритету роли (editingteacher > teacher > manager), при равенстве — первый
                if ($course && $course->instructor_id) {
                    foreach ($resolvedInstructors as $ri) {
                        if (($ri['user_id'] ?? null) == $course->instructor_id) {
                            $primaryInstructorId = $course->instructor_id;
                            break;
                        }
                    }
                }

                if (!$primaryInstructorId && !empty($resolvedInstructors)) {
                    usort($resolvedInstructors, function ($a, $b) {
                        return ($b['role_score'] ?? 0) <=> ($a['role_score'] ?? 0);
                    });
                    $primaryInstructorId = $resolvedInstructors[0]['user_id'] ?? null;
                }
            } else {
                Log::info('Преподаватели курса не найдены в Moodle', [
                    'course_id' => $moodleCourseId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при получении преподавателей курса', [
                'course_id' => $moodleCourseId,
                'error' => $e->getMessage()
            ]);
        }
        
        // Добавляем instructor_id в данные курса (основной преподаватель)
        if ($primaryInstructorId) {
            $courseData['instructor_id'] = $primaryInstructorId;
        }

        Log::info('Данные для синхронизации курса', [
            'moodle_course_id' => $moodleCourseId,
            'course_data' => $courseData,
            'course_exists' => $course ? true : false,
            'instructor_id' => $primaryInstructorId,
            'resolved_instructors_count' => count($resolvedInstructors),
        ]);

        if ($course) {
            // Обновляем существующий курс
            try {
                // Проверяем, были ли изменения перед обновлением
                $hasChanges = false;
                $changedFields = [];
                foreach ($courseData as $key => $value) {
                    $oldValue = $course->getAttribute($key);
                    if ($oldValue != $value) {
                        $hasChanges = true;
                        $changedFields[$key] = [
                            'old' => $oldValue,
                            'new' => $value
                        ];
                    }
                }
                
                if ($hasChanges) {
                    $course->update($courseData);
                    $this->syncCourseInstructors($course, $resolvedInstructors, $primaryInstructorId);
                    Log::info('Курс обновлен из Moodle', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $moodleCourseId,
                        'name' => $course->name,
                        'changed_fields' => array_keys($changedFields)
                    ]);
                    return ['created' => false, 'updated' => true, 'course' => $course];
                } else {
                    // Даже если курс не изменился, синхронизируем список преподавателей (из Moodle) при наличии данных
                    $this->syncCourseInstructors($course, $resolvedInstructors, $primaryInstructorId);
                    Log::debug('Курс не изменился (данные идентичны)', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $moodleCourseId,
                        'name' => $course->name,
                        'course_data' => $courseData
                    ]);
                    return ['created' => false, 'updated' => false, 'course' => $course];
                }
            } catch (\Exception $e) {
                Log::error('Ошибка обновления курса', [
                    'course_id' => $course->id,
                    'moodle_course_id' => $moodleCourseId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } else {
            // Создаем новый курс
            try {
                $course = Course::create($courseData);
                $this->syncCourseInstructors($course, $resolvedInstructors, $primaryInstructorId);
                
                Log::info('Курс создан из Moodle', [
                    'course_id' => $course->id,
                    'moodle_course_id' => $moodleCourseId,
                    'name' => $course->name
                ]);
                
                return ['created' => true, 'updated' => false, 'course' => $course];
            } catch (\Exception $e) {
                Log::error('Ошибка создания курса', [
                    'moodle_course_id' => $moodleCourseId,
                    'course_data' => $courseData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        }
    }

    /**
     * Синхронизировать список преподавателей курса (из Moodle) в таблицу course_instructors
     *
     * @param Course $course
     * @param array $resolvedInstructors
     * @param int|null $primaryInstructorId
     * @return void
     */
    protected function syncCourseInstructors(Course $course, array $resolvedInstructors, ?int $primaryInstructorId): void
    {
        // Если преподаватели не определены — ничего не делаем (не очищаем вручную назначенных)
        if (empty($resolvedInstructors)) {
            return;
        }

        // Убираем дубликаты по user_id, оставляя запись с максимальным role_score
        $byUserId = [];
        foreach ($resolvedInstructors as $ri) {
            $uid = $ri['user_id'] ?? null;
            if (!$uid) {
                continue;
            }
            if (!isset($byUserId[$uid]) || (($ri['role_score'] ?? 0) > ($byUserId[$uid]['role_score'] ?? 0))) {
                $byUserId[$uid] = $ri;
            }
        }

        // Сбрасываем is_primary для moodle-источника
        DB::table('course_instructors')
            ->where('course_id', $course->id)
            ->where('source', 'moodle')
            ->update(['is_primary' => false, 'updated_at' => now()]);

        $rows = [];
        foreach ($byUserId as $uid => $ri) {
            $rows[] = [
                'course_id' => $course->id,
                'user_id' => $uid,
                'source' => 'moodle',
                'moodle_role_shortname' => $ri['moodle_role_shortname'] ?? null,
                'is_primary' => ($primaryInstructorId && $uid == $primaryInstructorId),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Upsert по (course_id, user_id)
        DB::table('course_instructors')->upsert(
            $rows,
            ['course_id', 'user_id'],
            ['source', 'moodle_role_shortname', 'is_primary', 'updated_at']
        );
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

            Log::info('Результат getCourseEnrolledUsers', [
                'course_id' => $courseId,
                'moodle_course_id' => $course->moodle_course_id,
                'result_type' => gettype($moodleUsers),
                'is_array' => is_array($moodleUsers),
                'is_false' => $moodleUsers === false,
                'count' => is_array($moodleUsers) ? count($moodleUsers) : 0,
                'first_user' => is_array($moodleUsers) && !empty($moodleUsers) ? $moodleUsers[0] : null
            ]);

            if ($moodleUsers === false) {
                Log::error('Ошибка получения записей студентов из Moodle API', [
                    'course_id' => $courseId,
                    'moodle_course_id' => $course->moodle_course_id,
                    'course_name' => $course->name,
                    'hint' => 'Проверьте логи Moodle API для деталей ошибки. Возможные причины: токен не имеет прав на получение записей студентов для этого курса, проблемы с подключением к Moodle.'
                ]);
                $stats['errors']++;
                $stats['errors_list'][] = [
                    'type' => 'api_error',
                    'error' => 'Не удалось получить записи студентов из Moodle API для курса ' . $course->name . '. Проверьте права токена в Moodle: токен должен иметь права на выполнение функции core_enrol_get_enrolled_users для курса с ID ' . $course->moodle_course_id
                ];
                return $stats;
            }
            
            if (empty($moodleUsers)) {
                Log::info('Список записей студентов из Moodle пуст (это нормально, если на курс не записаны студенты)', [
                    'course_id' => $courseId,
                    'moodle_course_id' => $course->moodle_course_id,
                    'course_name' => $course->name,
                    'hint' => 'На курс не записаны студенты или токен не имеет прав на получение записей. Если студентов должно быть, проверьте права токена в Moodle.'
                ]);
                // Не считаем это ошибкой - возможно, на курс действительно не записаны студенты
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

        Log::info('Поиск пользователя для синхронизации', [
            'moodle_user_id' => $moodleUserId,
            'email' => $moodleUser['email'] ?? null,
            'user_found' => $user ? true : false,
            'user_id' => $user->id ?? null,
            'user_moodle_id' => $user->moodle_user_id ?? null
        ]);

        if (!$user) {
            // Пользователь не найден - пропускаем
            Log::warning('Пользователь не найден в локальной БД, пропускаем запись', [
                'moodle_user_id' => $moodleUserId,
                'email' => $moodleUser['email'] ?? null,
                'moodle_user_data' => $moodleUser
            ]);
            
            return ['created' => false, 'updated' => false, 'skipped' => true];
        }
        
        // Обновляем moodle_user_id если его не было
        if (!$user->moodle_user_id && $moodleUserId) {
            $user->update(['moodle_user_id' => $moodleUserId]);
            Log::info('Обновлен moodle_user_id для пользователя', [
                'user_id' => $user->id,
                'moodle_user_id' => $moodleUserId
            ]);
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
        
        // Проверяем конфигурацию перед началом синхронизации
        try {
            $this->validateConfiguration();
        } catch (\InvalidArgumentException $e) {
            Log::error('Ошибка конфигурации Moodle', [
                'error' => $e->getMessage()
            ]);
            $stats['courses'] = [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'errors' => 1,
                'errors_list' => [
                    [
                        'type' => 'configuration',
                        'error' => $e->getMessage()
                    ]
                ]
            ];
            $stats['enrollments'] = [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0
            ];
            return $stats;
        }

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

