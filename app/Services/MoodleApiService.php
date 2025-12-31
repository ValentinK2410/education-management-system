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
     */
    public function __construct(?string $url = null, ?string $token = null)
    {
        $this->url = rtrim($url ?? config('services.moodle.url', ''), '/');
        $this->token = $token ?? config('services.moodle.token', '');
    }

    /**
     * Выполнение запроса к Moodle REST API
     * Универсальный метод для вызова любых функций Moodle через REST API
     * 
     * @param string $function Название функции Moodle API (например: 'core_user_create_users')
     * @param array $params Дополнительные параметры для запроса
     * @return array|false Массив данных в формате JSON или false в случае ошибки
     */
    private function call(string $function, array $params = []): array|false
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
        } catch (\Exception $e) {
            Log::error('Moodle API Exception', [
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
    public function createUser(array $userData): array|false
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
    public function getUserByEmail(string $email): array|false
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
    public function updateUser(int $moodleUserId, array $userData): array|false
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
    public function getCourseContents(int $courseId): array|false
    {
        $result = $this->call('core_course_get_contents', [
            'courseid' => $courseId
        ]);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        return $result;
    }

    /**
     * Получить задания курса
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с заданиями курса или false в случае ошибки
     */
    public function getCourseAssignments(int $courseId): array|false
    {
        $result = $this->call('mod_assign_get_assignments', [
            'courseids' => [$courseId]
        ]);

        if ($result === false || isset($result['exception'])) {
            return false;
        }

        // Возвращаем задания из первого курса
        if (isset($result['courses'][0]['assignments'])) {
            return $result['courses'][0]['assignments'];
        }

        return [];
    }

    /**
     * Получить сдачи студента по заданиям курса
     * 
     * @param int $courseId ID курса в Moodle
     * @param int $studentMoodleId ID студента в Moodle
     * @return array|false Массив с сдачами или false в случае ошибки
     */
    public function getStudentSubmissions(int $courseId, int $studentMoodleId): array|false
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
    public function getStudentGrades(int $courseId, int $studentMoodleId): array|false
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
    public function getCourseAssignmentsWithStatus(int $courseId, int $studentMoodleId, ?string $sectionName = null): array|false
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
                    // Проверяем, есть ли оценка
                    if ($grade && isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '' && $grade['grade'] >= 0) {
                        // Есть оценка - задание проверено
                        $status = 'graded';
                        $statusText = (string)$grade['grade'];
                        $gradeValue = (float)$grade['grade'];
                    } else {
                        // Есть сдача, но нет оценки - не проверено
                        $status = 'pending';
                        $statusText = 'Не проверено';
                    }
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
     * Получить все курсы из Moodle
     * 
     * @param array $options Опции фильтрации:
     *                       - 'ids' - массив ID курсов для получения (если пусто - все курсы)
     * @return array|false Массив курсов или false в случае ошибки
     */
    public function getAllCourses(array $options = []): array|false
    {
        $params = [];
        
        // Если указаны конкретные ID курсов
        if (!empty($options['ids']) && is_array($options['ids'])) {
            $params['options'] = [
                'ids' => $options['ids']
            ];
        }
        
        $result = $this->call('core_course_get_courses', $params);
        
        if ($result === false || isset($result['exception'])) {
            return false;
        }
        
        // Возвращаем массив курсов (исключаем системный курс с id=1)
        if (is_array($result)) {
            return array_values(array_filter($result, function($course) {
                return isset($course['id']) && $course['id'] > 1;
            }));
        }
        
        return [];
    }

    /**
     * Получить список пользователей, записанных на курс
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив пользователей или false в случае ошибки
     */
    public function getCourseEnrolledUsers(int $courseId): array|false
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
     * Получить информацию о конкретном курсе
     * 
     * @param int $courseId ID курса в Moodle
     * @return array|false Массив с данными курса или false в случае ошибки
     */
    public function getCourse(int $courseId): array|false
    {
        $result = $this->getAllCourses(['ids' => [$courseId]]);
        
        if ($result === false || empty($result)) {
            return false;
        }
        
        // Возвращаем первый курс
        return $result[0] ?? false;
    }
}

