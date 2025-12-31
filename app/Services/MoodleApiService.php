<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с Moodle REST API
 * 
 * Предоставляет методы для взаимодействия с Moodle через REST API
 * Использует токен для аутентификации и выполняет запросы к различным функциям Moodle
 */
class MoodleApiService
{
    /**
     * URL сайта Moodle (например: https://class.dekan.pro)
     * 
     * @var string
     */
    private string $url;

    /**
     * Токен для доступа к Moodle REST API
     * 
     * @var string
     */
    private string $token;

    /**
     * Конструктор класса
     * Инициализирует URL и токен для работы с Moodle API
     * 
     * @param string|null $url URL сайта Moodle (если не указан, берется из конфига)
     * @param string|null $token Токен для доступа к REST API (если не указан, берется из конфига)
     * @throws \InvalidArgumentException Если URL или токен не настроены корректно
     */
    public function __construct(?string $url = null, ?string $token = null)
    {
        $this->url = rtrim($url ?? config('services.moodle.url', ''), '/');
        $this->token = $token ?? config('services.moodle.token', '');
        
        // Валидация конфигурации
        $this->validateConfiguration();
    }
    
    /**
     * Проверка корректности конфигурации Moodle API
     * 
     * @throws \InvalidArgumentException Если конфигурация некорректна
     */
    private function validateConfiguration(): void
    {
        // Проверяем наличие URL
        if (empty($this->url)) {
            Log::error('Moodle API: URL не настроен. Проверьте MOODLE_URL в .env файле.');
            throw new \InvalidArgumentException('Moodle URL не настроен. Установите MOODLE_URL в .env файле.');
        }
        
        // Проверяем наличие протокола в URL
        if (!preg_match('/^https?:\/\//i', $this->url)) {
            Log::error('Moodle API: URL должен содержать протокол (http:// или https://)', [
                'url' => $this->url,
                'hint' => 'Убедитесь, что MOODLE_URL в .env файле содержит полный URL, например: https://class.dekan.pro'
            ]);
            throw new \InvalidArgumentException(
                "Moodle URL должен содержать протокол (http:// или https://). " .
                "Текущее значение: '{$this->url}'. " .
                "Пример правильного значения: https://class.dekan.pro"
            );
        }
        
        // Проверяем наличие токена
        if (empty($this->token)) {
            Log::error('Moodle API: Токен не настроен. Проверьте MOODLE_TOKEN в .env файле.');
            throw new \InvalidArgumentException('Moodle токен не настроен. Установите MOODLE_TOKEN в .env файле.');
        }
        
        Log::info('Moodle API: Конфигурация проверена', [
            'url' => $this->url,
            'token_set' => !empty($this->token)
        ]);
    }

    /**
     * Выполнение запроса к Moodle REST API
     * Универсальный метод для вызова любых функций Moodle через REST API
     * 
     * @param string $function Название функции Moodle API (например: 'core_user_create_users')
     * @param array $params Дополнительные параметры для запроса
     * @return array|false Массив данных в формате JSON или false в случае ошибки
     */
    private function call(string $function, array $params = [])
    {
        // Формируем URL для запроса к Moodle REST API
        $url = $this->url . '/webservice/rest/server.php';

        // Добавляем обязательные параметры для REST API запроса
        $params['wstoken'] = $this->token;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = 'json';

        // Логируем запрос (без пароля для безопасности)
        $logParams = $params;
        if (isset($logParams['users']) && is_array($logParams['users'])) {
            foreach ($logParams['users'] as &$user) {
                if (isset($user['password'])) {
                    $user['password'] = '***скрыто***';
                }
            }
        }
        Log::info('Moodle API Call', [
            'url' => $url,
            'function' => $function,
            'params' => $logParams
        ]);

        try {
            // Выполняем POST запрос к Moodle API
            $response = Http::timeout(30)->asForm()->post($url, $params);

            // Проверяем статус ответа
            if (!$response->successful()) {
                Log::error('Moodle API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            $data = $response->json();

            // Проверяем, вернул ли Moodle исключение (ошибку)
            if (isset($data['exception'])) {
                Log::error('Moodle API Exception', [
                    'exception' => $data['exception'] ?? 'unknown',
                    'message' => $data['message'] ?? 'неизвестная ошибка',
                    'errorcode' => $data['errorcode'] ?? '',
                    'debuginfo' => $data['debuginfo'] ?? ''
                ]);
                return [
                    'exception' => $data['exception'],
                    'message' => $data['message'] ?? '',
                    'errorcode' => $data['errorcode'] ?? ''
                ];
            }

            return $data;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Ошибка подключения (например, не может разрешить хост)
            Log::error('Moodle API Connection Error', [
                'url' => $url,
                'message' => $e->getMessage(),
                'hint' => 'Проверьте, что MOODLE_URL в .env файле содержит правильный домен. ' .
                          'Ошибка указывает на проблему с DNS или неправильный URL.'
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Moodle API Exception', [
                'url' => $url,
                'function' => $function,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Создать пользователя в Moodle
     * 
     * @param array $userData Данные пользователя:
     *                       - username (обязательно) - логин пользователя
     *                       - password (обязательно) - пароль (незахэшированный)
     *                       - firstname (обязательно) - имя
     *                       - lastname (обязательно) - фамилия (минимум дефис)
     *                       - email (обязательно) - email адрес
     * @return array|false Массив с данными созданного пользователя или false в случае ошибки
     */
    public function createUser(array $userData)
    {
        // Проверяем обязательные поля
        $required = ['username', 'password', 'firstname', 'lastname', 'email'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                Log::error('Moodle API: Отсутствует обязательное поле', ['field' => $field]);
                return false;
            }
        }

        // Модифицируем пароль для соответствия требованиям Moodle
        // Moodle требует: хотя бы один специальный символ (*, -, или #) И хотя бы одну цифру
        $password = $userData['password'];
        if (!preg_match('/[*\-#]/', $password)) {
            $password = $password . '-';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $password = $password . '1';
        }

        // Подготавливаем данные для создания пользователя
        $moodleUserData = [
            'users' => [
                [
                    'username' => $userData['username'],
                    'password' => $password,
                    'firstname' => $userData['firstname'],
                    'lastname' => !empty($userData['lastname']) && trim($userData['lastname']) !== '' 
                        ? trim($userData['lastname']) 
                        : '-',
                    'email' => $userData['email'],
                ]
            ]
        ];

        // Вызываем функцию Moodle API для создания пользователя
        $result = $this->call('core_user_create_users', $moodleUserData);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        // Moodle возвращает массив с данными созданного пользователя
        if (isset($result[0]['id'])) {
            Log::info('Moodle API: Пользователь успешно создан', [
                'moodle_id' => $result[0]['id'],
                'username' => $userData['username'],
                'email' => $userData['email']
            ]);
            return $result[0];
        }

        return false;
    }

    /**
     * Получить пользователя из Moodle по email
     * 
     * @param string $email Email адрес пользователя
     * @return array|false Массив с данными пользователя или false если не найден
     */
    public function getUserByEmail(string $email)
    {
        $result = $this->call('core_user_get_users_by_field', [
            'field' => 'email',
            'values' => [$email]
        ]);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        // Если пользователь найден, возвращаем первый элемент
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    /**
     * Обновить пользователя в Moodle
     * 
     * @param int $moodleUserId ID пользователя в Moodle
     * @param array $userData Данные для обновления
     * @return array|false Массив с обновленными данными или false в случае ошибки
     */
    public function updateUser(int $moodleUserId, array $userData)
    {
        $updateData = [
            'users' => [
                array_merge(['id' => $moodleUserId], $userData)
            ]
        ];

        $result = $this->call('core_user_update_users', $updateData);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        return $result;
    }

    /**
     * Обновить пароль пользователя в Moodle
     * 
     * @param int $moodleUserId ID пользователя в Moodle
     * @param string $password Новый пароль (незахэшированный)
     * @return bool true если успешно, false в случае ошибки
     */
    public function updateUserPassword(int $moodleUserId, string $password): bool
    {
        // Модифицируем пароль для соответствия требованиям Moodle
        if (!preg_match('/[*\-#]/', $password)) {
            $password = $password . '-';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $password = $password . '1';
        }

        $result = $this->updateUser($moodleUserId, ['password' => $password]);
        return $result !== false;
    }

    /**
     * Получить содержимое курса (разделы и модули)
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с разделами курса или false в случае ошибки
     */
    public function getCourseContents(int $courseId)
    {
        try {
            Log::info('getCourseContents: запрос содержимого курса', [
                'course_id' => $courseId
            ]);
            
            $result = $this->call('core_course_get_contents', [
                'courseid' => $courseId
            ]);

            if ($result === false) {
                Log::warning('getCourseContents: запрос вернул false', [
                    'course_id' => $courseId
                ]);
                return false;
            }

            if (isset($result['exception'])) {
                Log::error('getCourseContents: Moodle вернул исключение', [
                    'course_id' => $courseId,
                    'exception' => $result['exception'] ?? 'unknown',
                    'message' => $result['message'] ?? 'неизвестная ошибка',
                    'errorcode' => $result['errorcode'] ?? ''
                ]);
                return false;
            }

            Log::info('getCourseContents: успешно получено содержимое курса', [
                'course_id' => $courseId,
                'sections_count' => is_array($result) ? count($result) : 0
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('getCourseContents: исключение при получении содержимого курса', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Получить задания курса
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с заданиями курса или false в случае ошибки
     */
    public function getCourseAssignments(int $courseId)
    {
        try {
            Log::info('getCourseAssignments: запрос заданий курса', [
                'course_id' => $courseId
            ]);
            
            $result = $this->call('mod_assign_get_assignments', [
                'courseids' => [$courseId]
            ]);

            if ($result === false) {
                Log::warning('getCourseAssignments: запрос вернул false', [
                    'course_id' => $courseId
                ]);
                return false;
            }

            if (isset($result['exception'])) {
                Log::error('getCourseAssignments: Moodle вернул исключение', [
                    'course_id' => $courseId,
                    'exception' => $result['exception'] ?? 'unknown',
                    'message' => $result['message'] ?? 'неизвестная ошибка',
                    'errorcode' => $result['errorcode'] ?? null,
                    'debuginfo' => $result['debuginfo'] ?? null,
                    'full_result' => $result
                ]);
                return false;
            }

            // Логируем структуру ответа для отладки
            Log::info('getCourseAssignments: структура ответа Moodle', [
                'course_id' => $courseId,
                'has_courses' => isset($result['courses']),
                'courses_count' => isset($result['courses']) ? count($result['courses']) : 0,
                'result_keys' => array_keys($result ?? []),
                'first_course_keys' => isset($result['courses'][0]) ? array_keys($result['courses'][0]) : null,
                'has_assignments' => isset($result['courses'][0]['assignments']),
                'assignments_count' => isset($result['courses'][0]['assignments']) ? count($result['courses'][0]['assignments']) : 0
            ]);

            // Возвращаем задания из первого курса
            if (isset($result['courses'][0]['assignments'])) {
                $assignments = $result['courses'][0]['assignments'];
                Log::info('getCourseAssignments: успешно получены задания', [
                    'course_id' => $courseId,
                    'assignments_count' => count($assignments)
                ]);
                return $assignments;
            }

            Log::info('getCourseAssignments: заданий не найдено', [
                'course_id' => $courseId,
                'result_structure' => array_keys($result ?? []),
                'full_result' => $result
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('getCourseAssignments: исключение', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Получить сдачи студента по заданиям курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с сдачами или false в случае ошибки
     */
    public function getStudentSubmissions(int $courseId, int $studentMoodleId)
    {
        // Сначала получаем список заданий курса
        $assignments = $this->getCourseAssignments($courseId);
        
        if ($assignments === false || empty($assignments)) {
            return [];
        }

        $assignmentIds = array_column($assignments, 'id');
        
        $result = $this->call('mod_assign_get_submissions', [
            'assignmentids' => $assignmentIds,
            'status' => '',
            'since' => 0,
            'before' => 0
        ]);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        // Фильтруем сдачи только для нужного студента
        $studentSubmissions = [];
        if (isset($result['assignments'])) {
            foreach ($result['assignments'] as $assignment) {
                if (isset($assignment['submissions'])) {
                    foreach ($assignment['submissions'] as $submission) {
                        if (isset($submission['userid']) && $submission['userid'] == $studentMoodleId) {
                            $studentSubmissions[$assignment['assignmentid']] = $submission;
                            break;
                        }
                    }
                }
            }
        }

        return $studentSubmissions;
    }

    /**
     * Получить оценки студента по заданиям курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с оценками или false в случае ошибки
     */
    public function getStudentGrades(int $courseId, int $studentMoodleId)
    {
        // Сначала получаем список заданий курса
        $assignments = $this->getCourseAssignments($courseId);
        
        if ($assignments === false || empty($assignments)) {
            return [];
        }

        $assignmentIds = array_column($assignments, 'id');
        
        $result = $this->call('mod_assign_get_grades', [
            'assignmentids' => $assignmentIds
        ]);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        // Фильтруем оценки только для нужного студента
        $studentGrades = [];
        if (isset($result['assignments'])) {
            foreach ($result['assignments'] as $assignment) {
                if (isset($assignment['grades'])) {
                    foreach ($assignment['grades'] as $grade) {
                        if (isset($grade['userid']) && $grade['userid'] == $studentMoodleId) {
                            $studentGrades[$assignment['assignmentid']] = $grade;
                            break;
                        }
                    }
                }
            }
        }

        return $studentGrades;
    }

    /**
     * Получить информацию о заданиях курса с их статусами для студента
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @param string|null $sectionName Название раздела для фильтрации (например, "ПОСЛЕ СЕССИИ")
     * @return array|false Массив с заданиями и их статусами или false в случае ошибки
     */
    public function getCourseAssignmentsWithStatus(int $courseId, int $studentMoodleId, ?string $sectionName = null)
    {
        // Получаем содержимое курса для поиска раздела
        $courseContents = $this->getCourseContents($courseId);
        
        if ($courseContents === false) {
            return false;
        }

        // Получаем задания курса
        $assignments = $this->getCourseAssignments($courseId);
        
        if ($assignments === false) {
            return false;
        }

        // Получаем сдачи студента
        $submissions = $this->getStudentSubmissions($courseId, $studentMoodleId);
        
        if ($submissions === false) {
            $submissions = [];
        }

        // Получаем оценки студента
        $grades = $this->getStudentGrades($courseId, $studentMoodleId);
        
        if ($grades === false) {
            $grades = [];
        }

        // Создаем массив заданий с их статусами
        $assignmentsWithStatus = [];
        
        // Находим нужный раздел
        $targetSection = null;
        if ($sectionName) {
            foreach ($courseContents as $section) {
                if (isset($section['name']) && stripos($section['name'], $sectionName) !== false) {
                    $targetSection = $section;
                    break;
                }
            }
        }

        // Если раздел не найден, используем все разделы
        $sectionsToProcess = $targetSection ? [$targetSection] : $courseContents;

        foreach ($sectionsToProcess as $section) {
            if (!isset($section['modules'])) {
                continue;
            }

            foreach ($section['modules'] as $module) {
                // Проверяем, является ли модуль заданием
                if ($module['modname'] !== 'assign') {
                    continue;
                }

                $assignmentId = $module['instance'] ?? null;
                
                if (!$assignmentId) {
                    continue;
                }

                // Находим задание в списке заданий
                $assignment = null;
                foreach ($assignments as $assign) {
                    if ($assign['id'] == $assignmentId) {
                        $assignment = $assign;
                        break;
                    }
                }

                if (!$assignment) {
                    continue;
                }

                // Определяем статус задания
                $submission = $submissions[$assignmentId] ?? null;
                $grade = $grades[$assignmentId] ?? null;

                $status = 'not_submitted'; // По умолчанию - не сдано
                $statusText = 'Не сдано';
                $gradeValue = null;

                // Если есть сдача (файл загружен)
                if ($submission) {
                    // Проверяем статус сдачи
                    $submissionStatus = $submission['status'] ?? null;
                    $submissionSubmitted = isset($submission['status']) && $submission['status'] === 'submitted';
                    
                    // Проверяем, есть ли оценка (grade !== null и >= 0 означает, что преподаватель проверил)
                    if ($grade && isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '' && $grade['grade'] >= 0) {
                        // Есть оценка - задание проверено преподавателем
                        $status = 'graded';
                        $statusText = (string)$grade['grade'];
                        $gradeValue = (float)$grade['grade'];
                        
                        Log::debug('Задание проверено преподавателем', [
                            'assignment_id' => $assignmentId,
                            'assignment_name' => $assignment['name'] ?? 'Без названия',
                            'grade' => $gradeValue,
                            'graded_at' => $grade['timecreated'] ?? null
                        ]);
                    } elseif ($submissionSubmitted || isset($submission['filesubmissions']) || isset($submission['onlinetext'])) {
                        // Есть сдача (файл или текст загружен), но нет оценки - не проверено преподавателем
                        $status = 'pending';
                        $statusText = 'Не проверено';
                        
                        Log::debug('Задание сдано, но не проверено', [
                            'assignment_id' => $assignmentId,
                            'assignment_name' => $assignment['name'] ?? 'Без названия',
                            'submission_status' => $submissionStatus,
                            'submitted_at' => $submission['timecreated'] ?? null
                        ]);
                    }
                } else {
                    Log::debug('Задание не сдано', [
                        'assignment_id' => $assignmentId,
                        'assignment_name' => $assignment['name'] ?? 'Без названия'
                    ]);
                }

                $assignmentsWithStatus[] = [
                    'id' => $assignmentId,
                    'name' => $assignment['name'] ?? $module['name'] ?? 'Без названия',
                    'section_name' => $section['name'] ?? '',
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'submission' => $submission,
                    'submitted_at' => $submission['timecreated'] ?? null,
                    'graded_at' => $grade['timecreated'] ?? null,
                ];
            }
        }

        return $assignmentsWithStatus;
    }

    /**
     * Получить тесты/квизы курса
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с тестами курса или false в случае ошибки
     */
    public function getCourseQuizzes(int $courseId)
    {
        try {
            Log::info('getCourseQuizzes: запрос тестов курса', [
                'course_id' => $courseId
            ]);
            
            $result = $this->call('mod_quiz_get_quizzes_by_courses', [
                'courseids' => [$courseId]
            ]);

            if ($result === false) {
                Log::warning('getCourseQuizzes: запрос вернул false', [
                    'course_id' => $courseId
                ]);
                return false;
            }

            if (isset($result['exception'])) {
                Log::error('getCourseQuizzes: Moodle вернул исключение', [
                    'course_id' => $courseId,
                    'exception' => $result['exception'] ?? 'unknown',
                    'message' => $result['message'] ?? 'неизвестная ошибка',
                    'errorcode' => $result['errorcode'] ?? null,
                    'debuginfo' => $result['debuginfo'] ?? null,
                    'full_result' => $result
                ]);
                return false;
            }

            // Логируем структуру ответа для отладки
            Log::info('getCourseQuizzes: структура ответа Moodle', [
                'course_id' => $courseId,
                'has_quizzes' => isset($result['quizzes']),
                'quizzes_count' => isset($result['quizzes']) ? count($result['quizzes']) : 0,
                'result_keys' => array_keys($result ?? []),
                'full_result' => $result
            ]);

            // Возвращаем тесты
            if (isset($result['quizzes'])) {
                Log::info('getCourseQuizzes: успешно получены тесты', [
                    'course_id' => $courseId,
                    'quizzes_count' => count($result['quizzes'])
                ]);
                return $result['quizzes'];
            }

            Log::info('getCourseQuizzes: тестов не найдено', [
                'course_id' => $courseId,
                'result_structure' => array_keys($result ?? []),
                'full_result' => $result
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('getCourseQuizzes: исключение', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Получить попытки студента по тестам курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с попытками или false в случае ошибки
     */
    public function getStudentQuizAttempts(int $courseId, int $studentMoodleId)
    {
        // Сначала получаем список тестов курса
        $quizzes = $this->getCourseQuizzes($courseId);
        
        if ($quizzes === false || empty($quizzes)) {
            return [];
        }

        $quizIds = array_column($quizzes, 'id');
        $allAttempts = [];

        foreach ($quizIds as $quizId) {
            $result = $this->call('mod_quiz_get_user_attempts', [
                'quizid' => $quizId,
                'userid' => $studentMoodleId,
                'status' => 'all'
            ]);

            if ($result !== false && !isset($result['exception']) && isset($result['attempts'])) {
                foreach ($result['attempts'] as $attempt) {
                    $allAttempts[$quizId][] = $attempt;
                }
            }
        }

        return $allAttempts;
    }

    /**
     * Получить оценки студента за тесты курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с оценками или false в случае ошибки
     */
    public function getStudentQuizGrades(int $courseId, int $studentMoodleId)
    {
        // Сначала получаем список тестов курса
        $quizzes = $this->getCourseQuizzes($courseId);
        
        if ($quizzes === false || empty($quizzes)) {
            return [];
        }

        $quizIds = array_column($quizzes, 'id');
        $grades = [];

        foreach ($quizIds as $quizId) {
            $result = $this->call('mod_quiz_get_user_best_grade', [
                'quizid' => $quizId,
                'userid' => $studentMoodleId
            ]);

            if ($result !== false && !isset($result['exception']) && isset($result['grade'])) {
                $grades[$quizId] = $result;
            }
        }

        return $grades;
    }

    /**
     * Получить форумы курса
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с форумами курса или false в случае ошибки
     */
    public function getCourseForums(int $courseId)
    {
        $result = $this->call('mod_forum_get_forums_by_courses', [
            'courseids' => [$courseId]
        ]);

        if ($result === false || isset($result['exception'])) {
            Log::error('Ошибка получения форумов из Moodle', [
                'course_id' => $courseId,
                'exception' => $result['exception'] ?? null,
                'message' => $result['message'] ?? null
            ]);
            return false;
        }

        // Возвращаем форумы из первого курса
        if (isset($result['forums'])) {
            return $result['forums'];
        }

        return [];
    }

    /**
     * Получить посты студента в форумах курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с постами или false в случае ошибки
     */
    public function getStudentForumPosts(int $courseId, int $studentMoodleId)
    {
        // Сначала получаем список форумов курса
        $forums = $this->getCourseForums($courseId);
        
        if ($forums === false || empty($forums)) {
            return [];
        }

        $allPosts = [];

        foreach ($forums as $forum) {
            $forumId = $forum['id'] ?? null;
            if (!$forumId) {
                continue;
            }

            // Получаем обсуждения форума
            $discussionsResult = $this->call('mod_forum_get_forum_discussions', [
                'forumid' => $forumId
            ]);

            if ($discussionsResult === false || isset($discussionsResult['exception'])) {
                continue;
            }

            $discussions = $discussionsResult['discussions'] ?? [];
            
            foreach ($discussions as $discussion) {
                $discussionId = $discussion['discussion'] ?? null;
                if (!$discussionId) {
                    continue;
                }

                // Получаем посты в обсуждении
                $postsResult = $this->call('mod_forum_get_discussion_posts', [
                    'discussionid' => $discussionId
                ]);

                if ($postsResult !== false && !isset($postsResult['exception']) && isset($postsResult['posts'])) {
                    foreach ($postsResult['posts'] as $post) {
                        // Фильтруем только посты студента
                        if (isset($post['author']['id']) && $post['author']['id'] == $studentMoodleId) {
                            $allPosts[$forumId][] = $post;
                        }
                    }
                }
            }
        }

        return $allPosts;
    }

    /**
     * Получить материалы курса (resources)
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с материалами курса или false в случае ошибки
     */
    public function getCourseResources(int $courseId)
    {
        // Получаем содержимое курса, которое включает все модули
        $courseContents = $this->getCourseContents($courseId);
        
        if ($courseContents === false) {
            return false;
        }

        $resources = [];

        foreach ($courseContents as $section) {
            if (!isset($section['modules'])) {
                continue;
            }

            foreach ($section['modules'] as $module) {
                // Типы ресурсов в Moodle: resource, file, folder, page, url и т.д.
                $resourceTypes = ['resource', 'file', 'folder', 'page', 'url', 'book'];
                
                if (in_array($module['modname'] ?? '', $resourceTypes)) {
                    $resources[] = [
                        'id' => $module['id'] ?? null,
                        'instance' => $module['instance'] ?? null,
                        'name' => $module['name'] ?? '',
                        'modname' => $module['modname'] ?? '',
                        'section_name' => $section['name'] ?? '',
                        'section_id' => $section['id'] ?? null,
                        'url' => $module['url'] ?? null,
                        'description' => $module['description'] ?? null,
                    ];
                }
            }
        }

        return $resources;
    }

    /**
     * Получить просмотры материалов студентом
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с просмотрами или false в случае ошибки
     */
    public function getStudentResourceViews(int $courseId, int $studentMoodleId)
    {
        // Получаем материалы курса
        $resources = $this->getCourseResources($courseId);
        
        if ($resources === false || empty($resources)) {
            return [];
        }

        $views = [];

        // Для получения просмотров используем core_course_get_contents с параметром userid
        // Но это может не работать для всех типов ресурсов
        // Альтернатива: использовать logstore для получения просмотров
        
        // Пока возвращаем пустой массив, так как Moodle API не предоставляет прямой способ
        // получить просмотры ресурсов для конкретного студента через REST API
        // Это потребует использования logstore или других методов
        
        return $views;
    }

    /**
     * Получить все активности курса с их статусами для студента
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив со всеми активностями и их статусами или false в случае ошибки
     */
    public function getAllCourseActivities(int $courseId, int $studentMoodleId)
    {
        $activities = [];

        Log::info('getAllCourseActivities: начало', [
            'course_id' => $courseId,
            'student_moodle_id' => $studentMoodleId
        ]);

        // Получаем задания курса напрямую (без использования getCourseContents)
        $assignments = $this->getCourseAssignments($courseId);
        
        Log::info('getAllCourseActivities: получены задания курса', [
            'course_id' => $courseId,
            'assignments' => $assignments !== false ? count($assignments) : 'false',
            'assignments_data' => $assignments !== false ? $assignments : 'false'
        ]);
        
        $submissions = $this->getStudentSubmissions($courseId, $studentMoodleId);
        
        Log::info('getAllCourseActivities: получены сдачи студента', [
            'course_id' => $courseId,
            'student_moodle_id' => $studentMoodleId,
            'submissions' => $submissions !== false ? count($submissions) : 'false'
        ]);
        
        $grades = $this->getStudentGrades($courseId, $studentMoodleId);
        
        Log::info('getAllCourseActivities: получены оценки студента', [
            'course_id' => $courseId,
            'student_moodle_id' => $studentMoodleId,
            'grades' => $grades !== false ? count($grades) : 'false'
        ]);
        
        if ($assignments !== false) {
            foreach ($assignments as $assignment) {
                $assignmentId = $assignment['id'];
                $submission = $submissions[$assignmentId] ?? null;
                $grade = $grades[$assignmentId] ?? null;
                
                $status = 'not_submitted';
                $statusText = 'Не сдано';
                $gradeValue = null;
                $submittedAt = null;
                $gradedAt = null;
                
                if ($submission) {
                    $submissionStatus = $submission['status'] ?? null;
                    $submissionSubmitted = isset($submission['status']) && $submission['status'] === 'submitted';
                    
                    if ($grade && isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '' && $grade['grade'] >= 0) {
                        $status = 'graded';
                        $statusText = (string)$grade['grade'];
                        $gradeValue = (float)$grade['grade'];
                        $gradedAt = isset($grade['timecreated']) ? $grade['timecreated'] : null;
                    } elseif ($submissionSubmitted || isset($submission['filesubmissions']) || isset($submission['onlinetext'])) {
                        $status = 'pending';
                        $statusText = 'Не проверено';
                    }
                    $submittedAt = isset($submission['timecreated']) ? $submission['timecreated'] : null;
                }
                
                $activities[] = [
                    'type' => 'assign',
                    'moodle_id' => $assignmentId,
                    'name' => $assignment['name'] ?? 'Без названия',
                    'section_name' => '', // Не можем получить без getCourseContents
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'max_grade' => $assignment['grade'] ?? null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => $gradedAt,
                ];
            }
        }
        
        Log::info('getAllCourseActivities: получены задания', [
            'course_id' => $courseId,
            'assignments_count' => count($activities)
        ]);

        // Получаем тесты с попытками и оценками
        $quizzes = $this->getCourseQuizzes($courseId);
        $quizAttempts = $this->getStudentQuizAttempts($courseId, $studentMoodleId);
        $quizGrades = $this->getStudentQuizGrades($courseId, $studentMoodleId);
        
        if ($quizzes !== false) {
            foreach ($quizzes as $quiz) {
                $quizId = $quiz['id'];
                $attempts = $quizAttempts[$quizId] ?? [];
                $grade = $quizGrades[$quizId] ?? null;
                
                $status = 'not_started';
                $statusText = 'Не начато';
                $gradeValue = null;
                
                if (!empty($attempts)) {
                    $latestAttempt = end($attempts);
                    $attemptStatus = $latestAttempt['state'] ?? '';
                    
                    if ($attemptStatus === 'finished') {
                        if ($grade && isset($grade['grade'])) {
                            $status = 'graded';
                            $statusText = (string)$grade['grade'];
                            $gradeValue = (float)$grade['grade'];
                        } else {
                            $status = 'submitted';
                            $statusText = 'Сдано';
                        }
                    } else {
                        $status = 'submitted';
                        $statusText = 'Сдано';
                    }
                }
                
                $submittedAt = null;
                $gradedAt = null;
                if (!empty($attempts)) {
                    $latestAttempt = end($attempts);
                    $submittedAt = $latestAttempt['timefinish'] ?? null;
                    if ($status === 'graded') {
                        $gradedAt = $submittedAt;
                    }
                }
                
                $activities[] = [
                    'type' => 'quiz',
                    'moodle_id' => $quizId,
                    'name' => $quiz['name'] ?? 'Без названия',
                    'section_name' => '', // Не можем получить без getCourseContents
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'max_grade' => $quiz['grade'] ?? null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => $gradedAt,
                    'attempts_count' => count($attempts),
                ];
            }
        }

        // Получаем форумы с постами студента (пропускаем, если есть ошибки доступа)
        try {
            $forums = $this->getCourseForums($courseId);
            $forumPosts = $this->getStudentForumPosts($courseId, $studentMoodleId);
        } catch (\Exception $e) {
            try {
                Log::warning('getAllCourseActivities: ошибка получения форумов, пропускаем', [
                    'course_id' => $courseId,
                    'error' => $e->getMessage()
                ]);
            } catch (\Exception $logError) {
                // Игнорируем ошибки логирования
            }
            $forums = false;
            $forumPosts = [];
        }
        
        if ($forums !== false) {
            foreach ($forums as $forum) {
                $forumId = $forum['id'];
                $posts = $forumPosts[$forumId] ?? [];
                
                $status = empty($posts) ? 'not_started' : 'completed';
                $statusText = empty($posts) ? 'Не участвовал' : 'Участвовал';
                $submittedAt = !empty($posts) ? max(array_column($posts, 'timecreated')) : null;
                
                $activities[] = [
                    'type' => 'forum',
                    'moodle_id' => $forumId,
                    'name' => $forum['name'] ?? 'Без названия',
                    'section_name' => '', // Не можем получить без getCourseContents
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => null,
                    'max_grade' => null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => null,
                ];
            }
        }

        // Получаем материалы курса (resources) - этот метод тоже использует getCourseContents
        // Пока пропускаем, так как он требует getCourseContents
        // Можно добавить позже, если будет доступ к другим методам API
        
        Log::info('getAllCourseActivities: завершено', [
            'course_id' => $courseId,
            'total_activities' => count($activities)
        ]);

        return $activities;
    }

    /**
     * Получить все курсы из Moodle
     * 
     * @param array $options Опции фильтрации:
     *                       - 'ids' - массив ID курсов для получения (если пусто - все курсы)
     * @return array|false Массив курсов или false в случае ошибки
     */
    public function getAllCourses(array $options = [])
    {
        // Для получения всех курсов передаем пустой массив или без параметров
        // Moodle API core_course_get_courses возвращает все курсы, если не указаны фильтры
        $params = [];
        
        // Если указаны конкретные ID курсов, используем другой подход
        if (!empty($options['ids']) && is_array($options['ids'])) {
            // Для получения конкретных курсов можно использовать фильтр
            // Но проще получить все и отфильтровать
        }
        
        $result = $this->call('core_course_get_courses', $params);
        
        if ($result === false || isset($result['exception'])) {
            Log::error('Ошибка получения курсов из Moodle', [
                'exception' => $result['exception'] ?? null,
                'message' => $result['message'] ?? null
            ]);
            return false;
        }
        
        // Возвращаем массив курсов (исключаем системный курс с id=1)
        if (is_array($result)) {
            $courses = array_values(array_filter($result, function($course) {
                return isset($course['id']) && $course['id'] > 1;
            }));
            
            // Если указаны конкретные ID, фильтруем по ним
            if (!empty($options['ids']) && is_array($options['ids'])) {
                $courses = array_filter($courses, function($course) use ($options) {
                    return in_array($course['id'], $options['ids']);
                });
                $courses = array_values($courses);
            }
            
            return $courses;
        }
        
        return [];
    }

    /**
     * Получить список пользователей, записанных на курс
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив пользователей или false в случае ошибки
     */
    public function getCourseEnrolledUsers(int $courseId)
    {
        $result = $this->call('core_enrol_get_enrolled_users', [
            'courseid' => $courseId
        ]);
        
        if ($result === false || isset($result['exception'])) {
            return false;
        }
        
        // Возвращаем массив пользователей
        if (is_array($result)) {
            return $result;
        }
        
        return [];
    }

    /**
     * Получить преподавателей курса (пользователи с ролью editingteacher или teacher)
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив преподавателей или false в случае ошибки
     */
    public function getCourseTeachers(int $courseId)
    {
        // Получаем всех пользователей курса
        $enrolledUsers = $this->getCourseEnrolledUsers($courseId);
        
        if ($enrolledUsers === false) {
            return false;
        }
        
        // Фильтруем только преподавателей (роли editingteacher или teacher)
        $teachers = [];
        foreach ($enrolledUsers as $user) {
            if (isset($user['roles']) && is_array($user['roles'])) {
                foreach ($user['roles'] as $role) {
                    $roleShortname = $role['shortname'] ?? '';
                    // В Moodle роли преподавателей: editingteacher (редактор курса) или teacher (преподаватель)
                    if (in_array($roleShortname, ['editingteacher', 'teacher', 'manager'])) {
                        $teachers[] = $user;
                        break; // Не добавляем пользователя дважды
                    }
                }
            }
        }
        
        Log::info('Преподаватели курса из Moodle', [
            'course_id' => $courseId,
            'teachers_count' => count($teachers),
            'teachers' => array_map(function($t) {
                return [
                    'id' => $t['id'] ?? null,
                    'email' => $t['email'] ?? null,
                    'fullname' => $t['fullname'] ?? null,
                    'roles' => array_column($t['roles'] ?? [], 'shortname')
                ];
            }, $teachers)
        ]);
        
        return $teachers;
    }

    /**
     * Получить информацию о конкретном курсе
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с данными курса или false в случае ошибки
     */
    public function getCourse(int $courseId)
    {
        $result = $this->getAllCourses(['ids' => [$courseId]]);
        
        if ($result === false || empty($result)) {
            return false;
        }
        
        // Возвращаем первый курс
        return $result[0] ?? false;
    }
}

