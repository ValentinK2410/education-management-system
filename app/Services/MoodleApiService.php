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
    public function call(string $function, array $params = [])
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

            // Проверяем наличие предупреждений о правах доступа
            if (isset($result['warnings']) && is_array($result['warnings'])) {
                foreach ($result['warnings'] as $warning) {
                    if (isset($warning['warningcode']) && $warning['warningcode'] == '2') {
                        Log::error('getCourseAssignments: ОШИБКА ПРАВ ДОСТУПА', [
                            'course_id' => $courseId,
                            'warning' => $warning,
                            'message' => 'Токен Moodle API не имеет прав доступа к курсу. ' .
                                        'Пользователь токена должен быть зачислен на курс и иметь права mod/assign:view. ' .
                                        'Проверьте настройки токена в Moodle: ' .
                                        'Site administration → Server → Web services → Manage tokens → ' .
                                        'найдите ваш токен и убедитесь, что пользователь зачислен на курс ' . $courseId,
                            'solution' => [
                                '1. Зайдите в Moodle как администратор',
                                '2. Перейдите: Site administration → Server → Web services → Manage tokens',
                                '3. Найдите токен, используемый в системе',
                                '4. Убедитесь, что пользователь токена зачислен на курс ' . $courseId,
                                '5. Убедитесь, что пользователь имеет роль с правами mod/assign:view',
                                '6. Или создайте токен для пользователя с ролью "Преподаватель" или "Менеджер"',
                            ]
                        ]);
                    }
                }
            }

            // Логируем структуру ответа для отладки
            Log::info('getCourseAssignments: структура ответа Moodle', [
                'course_id' => $courseId,
                'has_courses' => isset($result['courses']),
                'courses_count' => isset($result['courses']) ? count($result['courses']) : 0,
                'has_warnings' => isset($result['warnings']),
                'warnings_count' => isset($result['warnings']) ? count($result['warnings']) : 0,
                'warnings' => $result['warnings'] ?? [],
                'result_keys' => array_keys($result ?? []),
                'first_course_keys' => isset($result['courses'][0]) ? array_keys($result['courses'][0]) : null,
                'has_assignments' => isset($result['courses'][0]['assignments'] ?? null),
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
     * @param array|null $assignments Массив заданий (если уже получены, чтобы избежать повторного запроса)
     * @return array|false Массив с сдачами или false в случае ошибки
     */
    public function getStudentSubmissions(int $courseId, int $studentMoodleId, ?array $assignments = null)
    {
        // Если задания не переданы, получаем их
        if ($assignments === null) {
            $assignments = $this->getCourseAssignments($courseId);
        }

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
     * @param array|null $assignments Массив заданий (если уже получены, чтобы избежать повторного запроса)
     * @return array|false Массив с оценками или false в случае ошибки
     */
    public function getStudentGrades(int $courseId, int $studentMoodleId, ?array $assignments = null)
    {
        // Если задания не переданы, получаем их
        if ($assignments === null) {
            $assignments = $this->getCourseAssignments($courseId);
        }

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
                $submittedAt = null;
                if ($submission) {
                    // Определяем дату сдачи: приоритет timesubmitted, затем timemodified, затем timecreated
                    if (isset($submission['timesubmitted']) && $submission['timesubmitted'] > 0) {
                        $submittedAt = $submission['timesubmitted'];
                    } elseif (isset($submission['timemodified']) && $submission['timemodified'] > 0) {
                        $submittedAt = $submission['timemodified'];
                    } elseif (isset($submission['timecreated']) && $submission['timecreated'] > 0) {
                        $submittedAt = $submission['timecreated'];
                    }

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
                            'graded_at' => $grade['timecreated'] ?? null,
                            'submitted_at' => $submittedAt
                        ]);
                    } elseif ($submissionSubmitted || isset($submission['filesubmissions']) || isset($submission['onlinetext'])) {
                        // Есть сдача (файл или текст загружен), но нет оценки - не проверено преподавателем
                        $status = 'pending';
                        $statusText = 'Не проверено';

                        Log::debug('Задание сдано, но не проверено', [
                            'assignment_id' => $assignmentId,
                            'assignment_name' => $assignment['name'] ?? 'Без названия',
                            'submission_status' => $submissionStatus,
                            'submitted_at' => $submittedAt
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
                    'cmid' => $module['id'] ?? null, // Course Module ID для ссылки
                    'name' => $assignment['name'] ?? $module['name'] ?? 'Без названия',
                    'section_name' => $section['name'] ?? '',
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'submission' => $submission,
                    'submitted_at' => $submittedAt,
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
     * @param array|null $quizzes Массив тестов (если уже получены, чтобы избежать повторного запроса)
     * @return array|false Массив с попытками или false в случае ошибки
     */
    public function getStudentQuizAttempts(int $courseId, int $studentMoodleId, ?array $quizzes = null)
    {
        // Если тесты не переданы, получаем их
        if ($quizzes === null) {
            $quizzes = $this->getCourseQuizzes($courseId);
        }

        if ($quizzes === false || empty($quizzes)) {
            return [];
        }

        // Если studentMoodleId = 0, пропускаем запросы попыток (требуют реального пользователя)
        if ($studentMoodleId == 0) {
            Log::info('getStudentQuizAttempts: пропущено (student_moodle_id = 0)', ['course_id' => $courseId]);
            return [];
        }

        $quizIds = array_column($quizzes, 'id');
        $allAttempts = [];

        foreach ($quizIds as $quizId) {
            try {
                $result = $this->call('mod_quiz_get_user_attempts', [
                    'quizid' => $quizId,
                    'userid' => $studentMoodleId,
                    'status' => 'all'
                ]);

                if ($result !== false && !isset($result['exception']) && isset($result['attempts'])) {
                    foreach ($result['attempts'] as $attempt) {
                        $allAttempts[$quizId][] = $attempt;
                    }
                } elseif (isset($result['exception']) && isset($result['errorcode']) && $result['errorcode'] === 'accessexception') {
                    // Игнорируем ошибки доступа - это нормально для некоторых пользователей
                    Log::debug('getStudentQuizAttempts: ошибка доступа (игнорируется)', [
                        'quiz_id' => $quizId,
                        'student_moodle_id' => $studentMoodleId
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('getStudentQuizAttempts: ошибка при получении попыток', [
                    'quiz_id' => $quizId,
                    'student_moodle_id' => $studentMoodleId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $allAttempts;
    }

    /**
     * Получить оценки студента за тесты курса
     *
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @param array|null $quizzes Массив тестов (если уже получены, чтобы избежать повторного запроса)
     * @return array|false Массив с оценками или false в случае ошибки
     */
    public function getStudentQuizGrades(int $courseId, int $studentMoodleId, ?array $quizzes = null)
    {
        // Если тесты не переданы, получаем их
        if ($quizzes === null) {
            $quizzes = $this->getCourseQuizzes($courseId);
        }

        if ($quizzes === false || empty($quizzes)) {
            return [];
        }

        // Если studentMoodleId = 0, пропускаем запросы оценок (требуют реального пользователя)
        if ($studentMoodleId == 0) {
            Log::info('getStudentQuizGrades: пропущено (student_moodle_id = 0)', ['course_id' => $courseId]);
            return [];
        }

        $quizIds = array_column($quizzes, 'id');
        $grades = [];

        foreach ($quizIds as $quizId) {
            try {
                $result = $this->call('mod_quiz_get_user_best_grade', [
                    'quizid' => $quizId,
                    'userid' => $studentMoodleId
                ]);

                if ($result !== false && !isset($result['exception']) && isset($result['grade'])) {
                    $grades[$quizId] = $result;
                } elseif (isset($result['exception']) && isset($result['errorcode']) && $result['errorcode'] === 'accessexception') {
                    // Игнорируем ошибки доступа
                    Log::debug('getStudentQuizGrades: ошибка доступа (игнорируется)', [
                        'quiz_id' => $quizId,
                        'student_moodle_id' => $studentMoodleId
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('getStudentQuizGrades: ошибка при получении оценок', [
                    'quiz_id' => $quizId,
                    'student_moodle_id' => $studentMoodleId,
                    'error' => $e->getMessage()
                ]);
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
     * Получить посты студента в форумах курса с проверкой ответов преподавателя
     *
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @param array|null $forums Массив форумов (если уже получены, чтобы избежать повторного запроса)
     * @return array|false Массив с постами и информацией об ответах преподавателя или false в случае ошибки
     */
    public function getStudentForumPosts(int $courseId, int $studentMoodleId, ?array $forums = null)
    {
        // Если форумы не переданы, получаем их
        if ($forums === null) {
            $forums = $this->getCourseForums($courseId);
        }

        if ($forums === false || empty($forums)) {
            return [];
        }

        // Получаем преподавателей курса для проверки их ответов
        $teachers = $this->getCourseTeachers($courseId);
        $teacherIds = [];
        if ($teachers !== false && is_array($teachers)) {
            $teacherIds = array_column($teachers, 'id');
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

                // Получаем ВСЕ посты в обсуждении (не только студента)
                $postsResult = $this->call('mod_forum_get_discussion_posts', [
                    'discussionid' => $discussionId
                ]);

                if ($postsResult !== false && !isset($postsResult['exception']) && isset($postsResult['posts'])) {
                    $allDiscussionPosts = $postsResult['posts'];
                    $studentPosts = [];
                    $teacherReplies = [];

                    // Разделяем посты студента и ответы преподавателей
                    foreach ($allDiscussionPosts as $post) {
                        $authorId = $post['author']['id'] ?? null;
                        $parentId = $post['parent'] ?? null;

                        if ($authorId == $studentMoodleId) {
                            // Пост студента
                            $studentPosts[] = $post;
                        } elseif (in_array($authorId, $teacherIds) && $parentId) {
                            // Ответ преподавателя (если есть parent, значит это ответ на чей-то пост)
                            // Проверяем, является ли родительский пост постом студента
                            foreach ($allDiscussionPosts as $parentPost) {
                                if (($parentPost['id'] ?? null) == $parentId &&
                                    ($parentPost['author']['id'] ?? null) == $studentMoodleId) {
                                    $teacherReplies[$parentId] = true;
                                    break;
                                }
                            }
                        }
                    }

                    // Добавляем посты студента с информацией о наличии ответов преподавателя
                    foreach ($studentPosts as $post) {
                        $postId = $post['id'] ?? null;
                        $hasTeacherReply = isset($teacherReplies[$postId]);

                        // Добавляем флаг о наличии ответа преподавателя
                        $post['has_teacher_reply'] = $hasTeacherReply;
                        $post['needs_response'] = !$hasTeacherReply; // Если нет ответа преподавателя, требуется ответ

                        if (!isset($allPosts[$forumId])) {
                            $allPosts[$forumId] = [];
                        }
                        $allPosts[$forumId][] = $post;
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
     * Получить содержимое курса с разделами и неделями
     *
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с разделами курса или false в случае ошибки
     */
    public function getCourseContentsWithSections(int $courseId)
    {
        $contents = $this->getCourseContents($courseId);

        if ($contents === false) {
            return false;
        }

        // Обрабатываем разделы для определения недель
        $sections = [];
        foreach ($contents as $section) {
            $sectionNumber = $section['section'] ?? null;
            $sectionName = $section['name'] ?? '';

            // Определяем номер недели из названия раздела (если есть паттерн "Неделя 1", "Week 1" и т.д.)
            $weekNumber = null;
            if (preg_match('/(?:неделя|week|week_|седмица)\s*(\d+)/i', $sectionName, $matches)) {
                $weekNumber = (int)$matches[1];
            } elseif ($sectionNumber !== null && $sectionNumber > 0) {
                // Если номер недели не найден в названии, используем номер раздела
                $weekNumber = $sectionNumber;
            }

            $sections[] = [
                'id' => $section['id'] ?? null,
                'section' => $sectionNumber,
                'name' => $sectionName,
                'summary' => $section['summary'] ?? '',
                'week_number' => $weekNumber,
                'modules' => $section['modules'] ?? [],
            ];
        }

        return $sections;
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

        // Получаем содержимое курса с разделами для определения недель
        $courseContents = $this->getCourseContents($courseId);
        $sectionsMap = [];
        if ($courseContents !== false) {
            foreach ($courseContents as $section) {
                $sectionId = $section['id'] ?? null;
                $sectionNumber = $section['section'] ?? null;
                $sectionName = $section['name'] ?? '';

                // Определяем номер недели из названия раздела
                $weekNumber = null;
                if (preg_match('/(?:неделя|week|week_|седмица)\s*(\d+)/i', $sectionName, $matches)) {
                    $weekNumber = (int)$matches[1];
                } elseif ($sectionNumber !== null && $sectionNumber > 0) {
                    $weekNumber = $sectionNumber;
                }

                $sectionsMap[$sectionId] = [
                    'id' => $sectionId,
                    'section' => $sectionNumber,
                    'name' => $sectionName,
                    'week_number' => $weekNumber,
                    'modules' => $section['modules'] ?? [],
                ];
            }
        }

        // Получаем задания курса напрямую (без использования getCourseContents)
        // Кэшируем assignments, чтобы не запрашивать их повторно в getStudentSubmissions и getStudentGrades
        $assignments = $this->getCourseAssignments($courseId);

        Log::info('getAllCourseActivities: получены задания курса', [
            'course_id' => $courseId,
            'assignments' => $assignments !== false ? count($assignments) : 'false',
            'assignments_data' => $assignments !== false ? $assignments : 'false'
        ]);

        // Передаем уже полученные assignments, чтобы избежать повторных запросов
        $submissions = $this->getStudentSubmissions($courseId, $studentMoodleId, $assignments);

        Log::info('getAllCourseActivities: получены сдачи студента', [
            'course_id' => $courseId,
            'student_moodle_id' => $studentMoodleId,
            'submissions' => $submissions !== false ? count($submissions) : 'false'
        ]);

        // Передаем уже полученные assignments, чтобы избежать повторных запросов
        $grades = $this->getStudentGrades($courseId, $studentMoodleId, $assignments);

        Log::info('getAllCourseActivities: получены оценки студента', [
            'course_id' => $courseId,
            'student_moodle_id' => $studentMoodleId,
            'grades' => $grades !== false ? count($grades) : 'false'
        ]);

        if ($assignments !== false) {
            foreach ($assignments as $assignment) {
                $assignmentId = $assignment['id'];

                // Получаем cmid из courseContents (если доступен) или через Moodle API
                $cmid = null;

                // Сначала пытаемся найти cmid в courseContents
                foreach ($sectionsMap as $section) {
                    foreach ($section['modules'] as $module) {
                        if (($module['modname'] ?? '') === 'assign' && ($module['instance'] ?? null) == $assignmentId) {
                            $cmid = $module['id'] ?? null;
                            break 2;
                        }
                    }
                }

                // Если не нашли в courseContents, пытаемся через API
                if (!$cmid) {
                    try {
                        $cmResult = $this->call('core_course_get_course_module_by_instance', [
                            'module' => 'assign',
                            'instance' => $assignmentId
                        ]);

                        if ($cmResult !== false && !isset($cmResult['exception']) && isset($cmResult['cm']['id'])) {
                            $cmid = $cmResult['cm']['id'];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Не удалось получить cmid для задания', [
                            'assignment_id' => $assignmentId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $submission = $submissions[$assignmentId] ?? null;
                $grade = $grades[$assignmentId] ?? null;

                // Находим раздел для этого задания
                $sectionInfo = null;
                $weekNumber = null;
                $sectionNumber = null;
                $sectionName = '';
                $sectionOrder = null;

                foreach ($sectionsMap as $section) {
                    foreach ($section['modules'] as $order => $module) {
                        if (($module['modname'] ?? '') === 'assign' && ($module['instance'] ?? null) == $assignmentId) {
                            $sectionInfo = $section;
                            $weekNumber = $section['week_number'];
                            $sectionNumber = $section['section'];
                            $sectionName = $section['name'];
                            $sectionOrder = $order;
                            break 2;
                        }
                    }
                }

                $status = 'not_submitted';
                $statusText = 'Не сдано';
                $gradeValue = null;
                $submittedAt = null;
                $gradedAt = null;
                $hasDraft = false;
                $needsGrading = false;
                $isGraded = false;
                $draftCreatedAt = null;
                $draftUpdatedAt = null;

                if ($submission) {
                    // Определяем дату сдачи: приоритет timesubmitted, затем timemodified, затем timecreated
                    if (isset($submission['timesubmitted']) && $submission['timesubmitted'] > 0) {
                        $submittedAt = $submission['timesubmitted'];
                    } elseif (isset($submission['timemodified']) && $submission['timemodified'] > 0) {
                        $submittedAt = $submission['timemodified'];
                    } elseif (isset($submission['timecreated']) && $submission['timecreated'] > 0) {
                        $submittedAt = $submission['timecreated'];
                    }

                    $submissionStatus = $submission['status'] ?? null;
                    $submissionSubmitted = isset($submission['status']) && $submission['status'] === 'submitted';

                    // Определяем наличие черновика
                    if ($submissionStatus === 'draft' || (!$submissionSubmitted && (isset($submission['filesubmissions']) || isset($submission['onlinetext'])))) {
                        $hasDraft = true;
                        $draftCreatedAt = $submission['timecreated'] ?? null;
                        $draftUpdatedAt = $submission['timemodified'] ?? null;
                    }

                    if ($grade && isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '' && $grade['grade'] >= 0) {
                        $status = 'graded';
                        $statusText = (string)$grade['grade'];
                        $gradeValue = (float)$grade['grade'];
                        $gradedAt = isset($grade['timecreated']) ? $grade['timecreated'] : null;
                        $isGraded = true;
                    } elseif ($submissionSubmitted || isset($submission['filesubmissions']) || isset($submission['onlinetext'])) {
                        $status = 'submitted';
                        $statusText = 'Не проверено';
                        $needsGrading = true;
                    }
                }

                $activities[] = [
                    'type' => 'assign',
                    'moodle_id' => $assignmentId,
                    'cmid' => $cmid,
                    'name' => $assignment['name'] ?? 'Без названия',
                    'section_name' => $sectionName,
                    'moodle_section_id' => $sectionInfo['id'] ?? null,
                    'week_number' => $weekNumber,
                    'section_number' => $sectionNumber,
                    'section_order' => $sectionOrder,
                    'section_type' => 'week',
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'max_grade' => $assignment['grade'] ?? null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => $gradedAt,
                    'has_draft' => $hasDraft,
                    'needs_grading' => $needsGrading,
                    'is_graded' => $isGraded,
                    'draft_created_at' => $draftCreatedAt,
                    'draft_updated_at' => $draftUpdatedAt,
                    'draft_data' => $hasDraft ? $submission : null,
                    'submission_data' => $submission,
                    'grade_data' => $grade,
                ];
            }
        }

        Log::info('getAllCourseActivities: получены задания', [
            'course_id' => $courseId,
            'assignments_count' => count($activities)
        ]);

        // Получаем тесты с попытками и оценками
        $quizzes = $this->getCourseQuizzes($courseId);
        // Передаем уже полученные тесты, чтобы избежать повторных запросов
        $quizAttempts = $this->getStudentQuizAttempts($courseId, $studentMoodleId, $quizzes);
        $quizGrades = $this->getStudentQuizGrades($courseId, $studentMoodleId, $quizzes);

        if ($quizzes !== false) {
            foreach ($quizzes as $quiz) {
                $quizId = $quiz['id'];

                // Используем coursemodule из ответа Moodle API (это и есть cmid)
                // В ответе mod_quiz_get_quizzes_by_courses поле coursemodule содержит cmid
                $cmid = $quiz['coursemodule'] ?? null;

                // Если coursemodule отсутствует, пытаемся получить через API (fallback)
                if (!$cmid) {
                    try {
                        $cmResult = $this->call('core_course_get_course_module_by_instance', [
                            'module' => 'quiz',
                            'instance' => $quizId
                        ]);

                        if ($cmResult !== false && !isset($cmResult['exception']) && isset($cmResult['cm']['id'])) {
                            $cmid = $cmResult['cm']['id'];
                        } elseif (isset($cmResult['exception']) && isset($cmResult['errorcode']) && $cmResult['errorcode'] === 'accessexception') {
                            // Игнорируем ошибки доступа при получении cmid - это нормально
                            Log::debug('Не удалось получить cmid для теста (ошибка доступа, игнорируется)', [
                                'quiz_id' => $quizId
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Не удалось получить cmid для теста', [
                            'quiz_id' => $quizId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Находим раздел для этого теста
                $sectionInfo = null;
                $weekNumber = null;
                $sectionNumber = null;
                $sectionName = '';
                $sectionOrder = null;

                foreach ($sectionsMap as $section) {
                    foreach ($section['modules'] as $order => $module) {
                        if (($module['modname'] ?? '') === 'quiz' && ($module['instance'] ?? null) == $quizId) {
                            $sectionInfo = $section;
                            $weekNumber = $section['week_number'];
                            $sectionNumber = $section['section'];
                            $sectionName = $section['name'];
                            $sectionOrder = $order;
                            break 2;
                        }
                    }
                }

                $attempts = $quizAttempts[$quizId] ?? [];
                $grade = $quizGrades[$quizId] ?? null;

                $status = 'not_started';
                $statusText = 'Не начато';
                $gradeValue = null;
                $hasDraft = false;
                $needsGrading = false;
                $isGraded = false;
                $questionsData = null;
                $correctAnswers = null;
                $totalQuestions = null;

                if (!empty($attempts)) {
                    $latestAttempt = end($attempts);
                    $attemptStatus = $latestAttempt['state'] ?? '';

                    // Определяем наличие черновика (незавершенная попытка)
                    if ($attemptStatus !== 'finished') {
                        $hasDraft = true;
                    }

                    if ($attemptStatus === 'finished') {
                        if ($grade && isset($grade['grade'])) {
                            $status = 'graded';
                            $statusText = (string)$grade['grade'];
                            $gradeValue = (float)$grade['grade'];
                            $isGraded = true;
                        } else {
                            $status = 'submitted';
                            $statusText = 'Сдано';
                            $needsGrading = true;
                        }
                    } else {
                        $status = 'in_progress';
                        $statusText = 'В процессе';
                    }

                    // Собираем данные о вопросах и ответах
                    if (isset($latestAttempt['questions'])) {
                        $questionsData = $latestAttempt['questions'];
                        $totalQuestions = count($questionsData);
                        $correctAnswers = 0;
                        foreach ($questionsData as $question) {
                            if (isset($question['mark']) && $question['mark'] > 0) {
                                $correctAnswers++;
                            }
                        }
                    }
                }

                $submittedAt = null;
                $gradedAt = null;
                $lastAttemptAt = null;
                if (!empty($attempts)) {
                    $latestAttempt = end($attempts);
                    $submittedAt = $latestAttempt['timefinish'] ?? null;
                    $lastAttemptAt = $latestAttempt['timestart'] ?? null;
                    if ($status === 'graded') {
                        $gradedAt = $submittedAt;
                    }
                }

                $activities[] = [
                    'type' => 'quiz',
                    'moodle_id' => $quizId,
                    'cmid' => $cmid,
                    'name' => $quiz['name'] ?? 'Без названия',
                    'section_name' => $sectionName,
                    'moodle_section_id' => $sectionInfo['id'] ?? null,
                    'week_number' => $weekNumber,
                    'section_number' => $sectionNumber,
                    'section_order' => $sectionOrder,
                    'section_type' => 'week',
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => $gradeValue,
                    'max_grade' => $quiz['grade'] ?? null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => $gradedAt,
                    'has_draft' => $hasDraft,
                    'needs_grading' => $needsGrading,
                    'is_graded' => $isGraded,
                    'attempts_count' => count($attempts),
                    'max_attempts' => $quiz['attempts'] ?? null,
                    'last_attempt_at' => $lastAttemptAt,
                    'questions_data' => $questionsData,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'attempts_data' => $attempts,
                    'grade_data' => $grade,
                ];
            }
        }

        // Получаем форумы с постами студента (пропускаем, если есть ошибки доступа)
        try {
            $forums = $this->getCourseForums($courseId);
            // Передаем уже полученные форумы, чтобы избежать повторного запроса
            $forumPosts = $this->getStudentForumPosts($courseId, $studentMoodleId, $forums);
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

                // Получаем cmid из courseContents (если доступен) или через Moodle API
                $cmid = null;

                // Сначала пытаемся найти cmid в courseContents
                foreach ($sectionsMap as $section) {
                    foreach ($section['modules'] as $module) {
                        if (($module['modname'] ?? '') === 'forum' && ($module['instance'] ?? null) == $forumId) {
                            $cmid = $module['id'] ?? null;
                            break 2;
                        }
                    }
                }

                // Если не нашли в courseContents, пытаемся через API
                if (!$cmid) {
                    try {
                        $cmResult = $this->call('core_course_get_course_module_by_instance', [
                            'module' => 'forum',
                            'instance' => $forumId
                        ]);

                        if ($cmResult !== false && !isset($cmResult['exception']) && isset($cmResult['cm']['id'])) {
                            $cmid = $cmResult['cm']['id'];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Не удалось получить cmid для форума', [
                            'forum_id' => $forumId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Находим раздел для этого форума
                $sectionInfo = null;
                $weekNumber = null;
                $sectionNumber = null;
                $sectionName = '';
                $sectionOrder = null;

                foreach ($sectionsMap as $section) {
                    foreach ($section['modules'] as $order => $module) {
                        if (($module['modname'] ?? '') === 'forum' && ($module['instance'] ?? null) == $forumId) {
                            $sectionInfo = $section;
                            $weekNumber = $section['week_number'];
                            $sectionNumber = $section['section'];
                            $sectionName = $section['name'];
                            $sectionOrder = $order;
                            break 2;
                        }
                    }
                }

                $posts = $forumPosts[$forumId] ?? [];
                $needsGrading = false;
                $needsResponse = false;
                $isGraded = false;
                $hasTeacherReply = false;

                $status = empty($posts) ? 'not_started' : 'completed';
                $statusText = empty($posts) ? 'Не участвовал' : 'Участвовал';
                $submittedAt = !empty($posts) ? max(array_column($posts, 'timecreated')) : null;

                // Проверяем посты студента
                if (!empty($posts)) {
                    foreach ($posts as $post) {
                        // Проверяем, нужен ли ответ преподавателя
                        if (isset($post['needs_response']) && $post['needs_response']) {
                            $needsResponse = true;
                        }

                        // Проверяем, есть ли ответ преподавателя
                        if (isset($post['has_teacher_reply']) && $post['has_teacher_reply']) {
                            $hasTeacherReply = true;
                        }

                        // Проверяем, есть ли непроверенные посты (если форум оценивается)
                        if (isset($forum['grade'])) {
                            // Если пост не оценен, требуется проверка
                            if (!isset($post['grade']) || $post['grade'] === null) {
                                $needsGrading = true;
                            } else {
                                $isGraded = true;
                            }
                        }
                    }

                    // Если студент ответил, но преподаватель не ответил, устанавливаем статус
                    if ($needsResponse && !$hasTeacherReply) {
                        $status = 'needs_response';
                        $statusText = 'Ожидает ответа преподавателя';
                        $needsGrading = true; // Используем needs_grading для отображения в списке ожидающих проверки
                    }
                }

                $activities[] = [
                    'type' => 'forum',
                    'moodle_id' => $forumId,
                    'cmid' => $cmid,
                    'name' => $forum['name'] ?? 'Без названия',
                    'section_name' => $sectionName,
                    'moodle_section_id' => $sectionInfo['id'] ?? null,
                    'week_number' => $weekNumber,
                    'section_number' => $sectionNumber,
                    'section_order' => $sectionOrder,
                    'section_type' => 'week',
                    'status' => $status,
                    'status_text' => $statusText,
                    'grade' => null,
                    'max_grade' => $forum['grade'] ?? null,
                    'submitted_at' => $submittedAt,
                    'graded_at' => null,
                    'needs_grading' => $needsGrading || $needsResponse,
                    'needs_response' => $needsResponse,
                    'has_teacher_reply' => $hasTeacherReply,
                    'is_graded' => $isGraded,
                    'posts_data' => $posts,
                    'posts_count' => count($posts),
                ];
            }
        }

        // Получаем материалы курса (resources) из содержимого курса
        if ($courseContents !== false) {
            foreach ($courseContents as $section) {
                if (!isset($section['modules'])) {
                    continue;
                }

                $sectionId = $section['id'] ?? null;
                $sectionNumber = $section['section'] ?? null;
                $sectionName = $section['name'] ?? '';

                // Определяем номер недели
                $weekNumber = null;
                if (preg_match('/(?:неделя|week|week_|седмица)\s*(\d+)/i', $sectionName, $matches)) {
                    $weekNumber = (int)$matches[1];
                } elseif ($sectionNumber !== null && $sectionNumber > 0) {
                    $weekNumber = $sectionNumber;
                }

                foreach ($section['modules'] as $order => $module) {
                    $modname = $module['modname'] ?? '';
                    $resourceTypes = ['resource', 'file', 'folder', 'page', 'url', 'book'];

                    if (in_array($modname, $resourceTypes)) {
                        $instanceId = $module['instance'] ?? null;
                        $cmid = $module['id'] ?? null;

                        if (!$instanceId && !$cmid) {
                            continue;
                        }

                        // Определяем, просмотрен ли материал (используем completion, если доступен)
                        $isViewed = false;
                        $isRead = false;
                        $lastViewedAt = null;
                        $viewCount = 0;

                        if (isset($module['completion']) && $module['completion'] > 0) {
                            $isViewed = true;
                            $isRead = true;
                            $viewCount = 1;

                            // Получаем timestamp из completiondata, если доступен
                            if (isset($module['completiondata']['timecompleted']) && $module['completiondata']['timecompleted'] > 0) {
                                $lastViewedAt = $module['completiondata']['timecompleted'];
                            }
                            // Если timecompleted нет, но есть другие данные о времени, используем их
                            // Но не используем completion как timestamp, так как это статус (0, 1, 2)
                        }

                        $activities[] = [
                            'type' => $modname,
                            'moodle_id' => $instanceId ?? $cmid,
                            'cmid' => $cmid,
                            'name' => $module['name'] ?? 'Без названия',
                            'section_name' => $sectionName,
                            'moodle_section_id' => $sectionId,
                            'week_number' => $weekNumber,
                            'section_number' => $sectionNumber,
                            'section_order' => $order,
                            'section_type' => 'week',
                            'status' => $isViewed ? 'completed' : 'not_started',
                            'status_text' => $isViewed ? 'Просмотрено' : 'Не просмотрено',
                            'grade' => null,
                            'max_grade' => null,
                            'submitted_at' => null,
                            'graded_at' => null,
                            'is_viewed' => $isViewed,
                            'is_read' => $isRead,
                            'last_viewed_at' => $lastViewedAt,
                            'view_count' => $viewCount,
                            'module_data' => $module,
                        ];
                    }
                }
            }
        }

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

        if ($result === false) {
            Log::error('Ошибка получения курсов из Moodle API: запрос вернул false', [
                'hint' => 'Проверьте подключение к Moodle, URL и токен в .env файле'
            ]);
            return false;
        }

        if (isset($result['exception'])) {
            Log::error('Ошибка получения курсов из Moodle API: исключение', [
                'exception' => $result['exception'] ?? null,
                'message' => $result['message'] ?? null,
                'errorcode' => $result['errorcode'] ?? null,
                'debuginfo' => $result['debuginfo'] ?? null,
                'hint' => 'Проверьте права доступа токена в Moodle. Токен должен иметь права на выполнение функции core_course_get_courses'
            ]);
            return false;
        }

        // Возвращаем массив курсов (исключаем системный курс с id=1)
        if (is_array($result)) {
            $totalCourses = count($result);
            $systemCourse = null;

            // Находим системный курс для логирования
            foreach ($result as $course) {
                if (isset($course['id']) && $course['id'] == 1) {
                    $systemCourse = $course;
                    break;
                }
            }

            $courses = array_values(array_filter($result, function($course) {
                return isset($course['id']) && $course['id'] > 1;
            }));

            Log::info('Курсы получены из Moodle API', [
                'total_from_api' => $totalCourses,
                'after_filtering' => count($courses),
                'system_course_excluded' => $systemCourse ? true : false,
                'course_ids' => array_map(function($c) { return $c['id'] ?? null; }, $courses),
                'first_course_sample' => !empty($courses) ? [
                    'id' => $courses[0]['id'] ?? null,
                    'fullname' => $courses[0]['fullname'] ?? null,
                    'shortname' => $courses[0]['shortname'] ?? null
                ] : null
            ]);

            // Если указаны конкретные ID, фильтруем по ним
            if (!empty($options['ids']) && is_array($options['ids'])) {
                $courses = array_filter($courses, function($course) use ($options) {
                    return in_array($course['id'], $options['ids']);
                });
                $courses = array_values($courses);
                Log::info('Курсы после фильтрации по заданным ID', [
                    'count' => count($courses),
                    'ids' => $options['ids']
                ]);
            }

            return $courses;
        }

        Log::warning('Moodle API вернул не массив курсов', [
            'result_type' => gettype($result),
            'result' => $result,
            'hint' => 'Ожидался массив курсов, но получен другой тип данных'
        ]);
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

        if ($result === false) {
            Log::error('Ошибка получения записей студентов из Moodle API: запрос вернул false', [
                'course_id' => $courseId,
                'hint' => 'Проверьте подключение к Moodle и права доступа токена'
            ]);
            return false;
        }

        if (isset($result['exception'])) {
            Log::error('Ошибка получения записей студентов из Moodle API: исключение', [
                'course_id' => $courseId,
                'exception' => $result['exception'] ?? null,
                'message' => $result['message'] ?? null,
                'errorcode' => $result['errorcode'] ?? null,
                'debuginfo' => $result['debuginfo'] ?? null,
                'hint' => 'Проверьте права доступа токена в Moodle. Токен должен иметь права на выполнение функции core_enrol_get_enrolled_users для курса с ID ' . $courseId
            ]);
            return false;
        }

        // Возвращаем массив пользователей
        if (is_array($result)) {
            Log::info('Записи студентов получены из Moodle API', [
                'course_id' => $courseId,
                'users_count' => count($result)
            ]);
            return $result;
        }

        Log::warning('Moodle API вернул не массив пользователей для курса', [
            'course_id' => $courseId,
            'result_type' => gettype($result),
            'result' => $result
        ]);
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

    /**
     * Получить URL для задания в Moodle
     *
     * @param int|null $cmid Course Module ID (если есть)
     * @param int|null $assignmentId Assignment ID (если cmid нет)
     * @param int|null $courseId Course ID (если нужен альтернативный способ)
     * @return string|null URL на задание или null если невозможно сформировать
     */
    public function getAssignmentUrl(?int $cmid = null, ?int $assignmentId = null, ?int $courseId = null): ?string
    {
        if ($cmid) {
            // Используем cmid для прямого доступа к заданию
            return $this->url . '/mod/assign/view.php?id=' . $cmid;
        }

        // Если cmid нет, можно попробовать использовать assignment ID
        // Но это менее надежно, так как нужен cmid
        if ($assignmentId && $courseId) {
            // Альтернативный способ - через курс и assignment ID
            // Но лучше использовать cmid
            return $this->url . '/mod/assign/view.php?id=' . $assignmentId;
        }

        return null;
    }

    /**
     * Получить URL для проверки работы конкретного студента в Moodle
     *
     * @param string $activityType Тип элемента курса (assign, quiz, forum и т.д.)
     * @param int|null $cmid Course Module ID в Moodle
     * @param int $moodleUserId ID студента в Moodle
     * @param int|null $moodleCourseId ID курса в Moodle (опционально)
     * @return string|null URL для проверки или null если данные недостаточны
     */
    public function getGradingUrl(string $activityType, ?int $cmid, int $moodleUserId, ?int $moodleCourseId = null): ?string
    {
        if (!$cmid) {
            return null;
        }

        switch ($activityType) {
            case 'assign':
                // Для заданий - прямая ссылка на проверку конкретного студента
                return $this->url . "/mod/assign/view.php?id={$cmid}&action=grade&userid={$moodleUserId}";

            case 'quiz':
                // Для тестов - ссылка на отчеты теста, где можно увидеть попытки студента
                // Если есть courseId, можно добавить фильтр по студенту
                if ($moodleCourseId) {
                    return $this->url . "/mod/quiz/report.php?id={$cmid}&mode=overview&course={$moodleCourseId}";
                }
                return $this->url . "/mod/quiz/view.php?id={$cmid}";

            case 'forum':
                // Для форумов - ссылка на просмотр форума (можно фильтровать по студенту)
                return $this->url . "/mod/forum/view.php?id={$cmid}";

            case 'resource':
            case 'file':
            case 'url':
                // Для материалов - ссылка на просмотр материала
                return $this->url . "/mod/{$activityType}/view.php?id={$cmid}";

            case 'exam':
                // Для экзаменов - аналогично тестам
                return $this->url . "/mod/quiz/view.php?id={$cmid}";

            default:
                // Для других типов - общая ссылка на просмотр элемента
                return $this->url . "/mod/{$activityType}/view.php?id={$cmid}";
        }
    }
}

