<?php

namespace App\Console\Commands;

use App\Services\MoodleApiService;
use App\Models\Course;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestMoodleApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moodle:test 
                            {--course-id= : ID курса в Moodle}
                            {--student-id= : ID студента в локальной БД}
                            {--moodle-student-id= : ID студента в Moodle}
                            {--full : Полная информация (курс + элементы + студент)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование Moodle API: получение информации о курсе, элементах и студенте';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moodleApi = new MoodleApiService();
        
        $courseId = $this->option('course-id');
        $studentId = $this->option('student-id');
        $moodleStudentId = $this->option('moodle-student-id');
        $full = $this->option('full');

        if (!$courseId) {
            $this->error('Необходимо указать --course-id (ID курса в Moodle)');
            return 1;
        }

        $this->info("=== Тестирование Moodle API ===");
        $this->info("Курс ID (Moodle): {$courseId}");
        $this->newLine();

        // 1. Получаем информацию о курсе
        $this->info("1. Получение информации о курсе...");
        $courseInfo = $moodleApi->getCourse($courseId);
        if ($courseInfo === false) {
            $this->error("   ❌ Не удалось получить информацию о курсе");
        } else {
            $this->info("   ✅ Курс найден:");
            $this->line("      Название: " . ($courseInfo['fullname'] ?? 'N/A'));
            $this->line("      Короткое название: " . ($courseInfo['shortname'] ?? 'N/A'));
            $this->line("      ID: " . ($courseInfo['id'] ?? 'N/A'));
            $this->line("      Категория ID: " . ($courseInfo['categoryid'] ?? 'N/A'));
            $this->line("      Видимый: " . (($courseInfo['visible'] ?? 0) ? 'Да' : 'Нет'));
        }
        $this->newLine();

        // 2. Получаем преподавателей курса
        $this->info("2. Получение преподавателей курса...");
        $teachers = $moodleApi->getCourseTeachers($courseId);
        if ($teachers === false) {
            $this->error("   ❌ Не удалось получить преподавателей");
        } else {
            $this->info("   ✅ Преподавателей найдено: " . count($teachers));
            foreach ($teachers as $teacher) {
                $this->line("      - {$teacher['firstname']} {$teacher['lastname']} (ID: {$teacher['id']}, Email: {$teacher['email']})");
            }
        }
        $this->newLine();

        // 3. Получаем список записанных студентов
        $this->info("3. Получение списка записанных студентов...");
        $enrolledUsers = $moodleApi->getCourseEnrolledUsers($courseId);
        if ($enrolledUsers === false) {
            $this->error("   ❌ Не удалось получить список студентов");
        } else {
            $this->info("   ✅ Студентов записано: " . count($enrolledUsers));
            if (count($enrolledUsers) > 0) {
                $this->line("   Первые 5 студентов:");
                foreach (array_slice($enrolledUsers, 0, 5) as $user) {
                    $this->line("      - {$user['firstname']} {$user['lastname']} (ID: {$user['id']}, Email: {$user['email']})");
                }
                if (count($enrolledUsers) > 5) {
                    $this->line("      ... и еще " . (count($enrolledUsers) - 5) . " студентов");
                }
            }
        }
        $this->newLine();

        // 4. Проверяем, записан ли конкретный студент
        if ($studentId || $moodleStudentId) {
            $moodleUserId = $moodleStudentId;
            
            if ($studentId && !$moodleStudentId) {
                // Получаем moodle_user_id из локальной БД
                $user = User::find($studentId);
                if ($user && $user->moodle_user_id) {
                    $moodleUserId = $user->moodle_user_id;
                    $this->info("4. Проверка записи студента (локальный ID: {$studentId}, Moodle ID: {$moodleUserId})...");
                } else {
                    $this->error("   ❌ Студент с ID {$studentId} не найден или у него нет moodle_user_id");
                    return 1;
                }
            } else {
                $this->info("4. Проверка записи студента (Moodle ID: {$moodleUserId})...");
            }

            $isEnrolled = false;
            if ($enrolledUsers !== false) {
                foreach ($enrolledUsers as $user) {
                    if ($user['id'] == $moodleUserId) {
                        $isEnrolled = true;
                        $this->info("   ✅ Студент записан на курс:");
                        $this->line("      Имя: {$user['firstname']} {$user['lastname']}");
                        $this->line("      Email: {$user['email']}");
                        $this->line("      ID: {$user['id']}");
                        break;
                    }
                }
            }

            if (!$isEnrolled) {
                $this->warn("   ⚠️  Студент с Moodle ID {$moodleUserId} не записан на курс");
            }
            $this->newLine();
        }

        // 5. Получаем элементы курса (если указан --full или студент)
        if ($full || ($studentId || $moodleStudentId)) {
            $moodleUserId = $moodleStudentId;
            if ($studentId && !$moodleStudentId) {
                $user = User::find($studentId);
                $moodleUserId = $user->moodle_user_id ?? null;
            }

            if (!$moodleUserId) {
                $this->warn("   ⚠️  Не указан ID студента в Moodle, пропускаем получение элементов");
            } else {
                $this->info("5. Получение элементов курса и статусов для студента (Moodle ID: {$moodleUserId})...");
                
                // Получаем задания
                $this->line("   Получение заданий...");
                
                // Используем прямой вызов API для отладки
                $rawResult = $moodleApi->call('mod_assign_get_assignments', [
                    'courseids' => [$courseId]
                ]);
                
                if ($rawResult === false) {
                    $this->error("      ❌ Запрос к Moodle API вернул false (проверьте MOODLE_URL и MOODLE_TOKEN)");
                } elseif (isset($rawResult['exception'])) {
                    $this->error("      ❌ Moodle вернул ошибку:");
                    $this->line("         Тип: " . ($rawResult['exception'] ?? 'unknown'));
                    $this->line("         Сообщение: " . ($rawResult['message'] ?? 'неизвестная ошибка'));
                    $this->line("         Код ошибки: " . ($rawResult['errorcode'] ?? 'N/A'));
                    if (isset($rawResult['debuginfo'])) {
                        $this->line("         Отладка: " . $rawResult['debuginfo']);
                    }
                } else {
                    // Показываем структуру ответа
                    $this->line("      Структура ответа: " . json_encode(array_keys($rawResult), JSON_UNESCAPED_UNICODE));
                    
                    if (isset($rawResult['courses']) && is_array($rawResult['courses']) && count($rawResult['courses']) > 0) {
                        $firstCourse = $rawResult['courses'][0];
                        $this->line("      Ключи первого курса: " . json_encode(array_keys($firstCourse), JSON_UNESCAPED_UNICODE));
                        
                        if (isset($firstCourse['assignments'])) {
                            $assignments = $firstCourse['assignments'];
                            $this->info("      ✅ Заданий найдено: " . count($assignments));
                            if (count($assignments) > 0) {
                                foreach ($assignments as $assignment) {
                                    $this->line("         - ID: {$assignment['id']}, Название: " . ($assignment['name'] ?? 'N/A'));
                                }
                            }
                        } else {
                            $this->warn("      ⚠️  В ответе нет ключа 'assignments' в первом курсе");
                            $this->line("      Полный ответ первого курса: " . json_encode($firstCourse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                        }
                    } else {
                        $this->warn("      ⚠️  В ответе нет массива 'courses' или он пустой");
                        $this->line("      Полный ответ: " . json_encode($rawResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    }
                }

                // Получаем сдачи и оценки студента
                if ($moodleUserId) {
                    $this->line("   Получение сдач студента...");
                    $submissions = $moodleApi->getStudentSubmissions($courseId, $moodleUserId);
                    if ($submissions === false) {
                        $this->error("      ❌ Не удалось получить сдачи");
                    } else {
                        $this->info("      ✅ Сдач найдено: " . count($submissions));
                        foreach ($submissions as $assignmentId => $submission) {
                            $status = $submission['status'] ?? 'unknown';
                            $this->line("         - Задание ID {$assignmentId}: статус = {$status}");
                        }
                    }

                    $this->line("   Получение оценок студента...");
                    $grades = $moodleApi->getStudentGrades($courseId, $moodleUserId);
                    if ($grades === false) {
                        $this->error("      ❌ Не удалось получить оценки");
                    } else {
                        $this->info("      ✅ Оценок найдено: " . count($grades));
                        foreach ($grades as $assignmentId => $grade) {
                            $gradeValue = $grade['grade'] ?? 'N/A';
                            $this->line("         - Задание ID {$assignmentId}: оценка = {$gradeValue}");
                        }
                    }
                }

                // Получаем тесты
                $this->line("   Получение тестов...");
                
                // Используем прямой вызов API для отладки
                $rawQuizResult = $moodleApi->call('mod_quiz_get_quizzes_by_courses', [
                    'courseids' => [$courseId]
                ]);
                
                if ($rawQuizResult === false) {
                    $this->error("      ❌ Запрос к Moodle API вернул false (проверьте MOODLE_URL и MOODLE_TOKEN)");
                } elseif (isset($rawQuizResult['exception'])) {
                    $this->error("      ❌ Moodle вернул ошибку:");
                    $this->line("         Тип: " . ($rawQuizResult['exception'] ?? 'unknown'));
                    $this->line("         Сообщение: " . ($rawQuizResult['message'] ?? 'неизвестная ошибка'));
                    $this->line("         Код ошибки: " . ($rawQuizResult['errorcode'] ?? 'N/A'));
                    if (isset($rawQuizResult['debuginfo'])) {
                        $this->line("         Отладка: " . $rawQuizResult['debuginfo']);
                    }
                } else {
                    // Показываем структуру ответа
                    $this->line("      Структура ответа: " . json_encode(array_keys($rawQuizResult), JSON_UNESCAPED_UNICODE));
                    
                    if (isset($rawQuizResult['quizzes'])) {
                        $quizzes = $rawQuizResult['quizzes'];
                        $this->info("      ✅ Тестов найдено: " . count($quizzes));
                        if (count($quizzes) > 0) {
                            foreach ($quizzes as $quiz) {
                                $this->line("         - ID: {$quiz['id']}, Название: " . ($quiz['name'] ?? 'N/A'));
                            }
                        }
                    } else {
                        $this->warn("      ⚠️  В ответе нет ключа 'quizzes'");
                        $this->line("      Полный ответ: " . json_encode($rawQuizResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    }
                }

                // Получаем форумы
                $this->line("   Получение форумов...");
                $forums = $moodleApi->getCourseForums($courseId);
                if ($forums === false) {
                    $this->warn("      ⚠️  Не удалось получить форумы (возможно, нет прав доступа)");
                } else {
                    $this->info("      ✅ Форумов найдено: " . count($forums));
                    if (count($forums) > 0) {
                        foreach ($forums as $forum) {
                            $this->line("         - ID: {$forum['id']}, Название: " . ($forum['name'] ?? 'N/A'));
                        }
                    }
                }

                // Получаем все активности с статусами
                if ($moodleUserId) {
                    $this->line("   Получение всех активностей с статусами...");
                    $activities = $moodleApi->getAllCourseActivities($courseId, $moodleUserId);
                    if ($activities === false) {
                        $this->error("      ❌ Не удалось получить активности");
                    } else {
                        $this->info("      ✅ Активностей найдено: " . count($activities));
                        if (count($activities) > 0) {
                            $this->table(
                                ['Тип', 'ID', 'Название', 'Статус', 'Оценка', 'Сдано', 'Проверено'],
                                array_map(function ($activity) {
                                    return [
                                        $activity['type'] ?? 'N/A',
                                        $activity['moodle_id'] ?? 'N/A',
                                        $activity['name'] ?? 'N/A',
                                        $activity['status_text'] ?? 'N/A',
                                        $activity['grade'] !== null ? ($activity['grade'] . '/' . ($activity['max_grade'] ?? 'N/A')) : '—',
                                        $activity['submitted_at'] ? date('d.m.Y H:i', $activity['submitted_at']) : '—',
                                        $activity['graded_at'] ? date('d.m.Y H:i', $activity['graded_at']) : '—',
                                    ];
                                }, $activities)
                            );
                        }
                    }
                }
            }
            $this->newLine();
        }

        // 6. Проверяем локальную БД
        $this->info("6. Проверка локальной БД...");
        $localCourse = Course::where('moodle_course_id', $courseId)->first();
        if ($localCourse) {
            $this->info("   ✅ Курс найден в локальной БД:");
            $this->line("      ID: {$localCourse->id}");
            $this->line("      Название: {$localCourse->name}");
            $this->line("      Moodle ID: {$localCourse->moodle_course_id}");
            
            if ($studentId) {
                $user = User::find($studentId);
                if ($user) {
                    $isEnrolledLocal = $user->courses()->where('courses.id', $localCourse->id)->exists();
                    if ($isEnrolledLocal) {
                        $this->info("      ✅ Студент записан на курс в локальной БД");
                    } else {
                        $this->warn("      ⚠️  Студент НЕ записан на курс в локальной БД");
                    }
                }
            }
        } else {
            $this->warn("   ⚠️  Курс не найден в локальной БД");
        }

        $this->newLine();
        
        // 7. Тестирование получения cohorts
        $this->info("7. Тестирование получения глобальных групп (cohorts)...");
        try {
            $cohorts = $moodleApi->getCohorts();
            if ($cohorts === false) {
                $this->error("   ❌ Не удалось получить cohorts");
                $this->line("   Проверьте логи для детальной информации об ошибке");
            } elseif (isset($cohorts['exception'])) {
                $this->error("   ❌ Moodle вернул ошибку:");
                $this->line("      Тип: " . ($cohorts['exception'] ?? 'unknown'));
                $this->line("      Сообщение: " . ($cohorts['message'] ?? 'неизвестная ошибка'));
                $this->line("      Код ошибки: " . ($cohorts['errorcode'] ?? 'N/A'));
                if (isset($cohorts['debuginfo'])) {
                    $this->line("      Отладка: " . $cohorts['debuginfo']);
                }
                $this->warn("   ⚠️  Возможно, токен не имеет прав на функцию core_cohort_get_cohorts");
            } elseif (is_array($cohorts)) {
                $this->info("   ✅ Cohorts получены успешно: " . count($cohorts));
                if (count($cohorts) > 0) {
                    $this->line("   Первые 5 cohorts:");
                    foreach (array_slice($cohorts, 0, 5) as $cohort) {
                        $this->line("      - ID: {$cohort['id']}, Название: " . ($cohort['name'] ?? 'N/A'));
                    }
                    if (count($cohorts) > 5) {
                        $this->line("      ... и еще " . (count($cohorts) - 5) . " cohorts");
                    }
                } else {
                    $this->warn("   ⚠️  Cohorts не найдены (возможно, их нет в Moodle)");
                }
            } else {
                $this->warn("   ⚠️  Неожиданный формат ответа: " . gettype($cohorts));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Ошибка при получении cohorts: " . $e->getMessage());
        }
        $this->newLine();

        // 8. Проверка доступных функций API
        $this->info("8. Проверка доступных функций Moodle API...");
        $this->line("   Попытка получить список доступных функций...");
        
        try {
            // Пробуем получить информацию о токене
            $siteInfo = $moodleApi->call('core_webservice_get_site_info', []);
            if ($siteInfo !== false && !isset($siteInfo['exception'])) {
                $this->info("   ✅ Подключение к Moodle API работает");
                if (isset($siteInfo['functions'])) {
                    $this->line("   Доступных функций: " . count($siteInfo['functions']));
                    // Проверяем, есть ли функция для cohorts
                    $hasCohortFunction = false;
                    foreach ($siteInfo['functions'] as $func) {
                        if (isset($func['name']) && strpos($func['name'], 'cohort') !== false) {
                            $hasCohortFunction = true;
                            $this->line("   ✅ Найдена функция для cohorts: " . $func['name']);
                        }
                    }
                    if (!$hasCohortFunction) {
                        $this->warn("   ⚠️  Функции для cohorts не найдены в списке доступных функций");
                    }
                }
            } else {
                $this->warn("   ⚠️  Не удалось получить информацию о сайте");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Ошибка при проверке функций: " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("=== Тестирование завершено ===");
        $this->newLine();
        
        // Выводим рекомендации по настройке прав доступа
        if (isset($rawResult) && isset($rawResult['exception']) && $rawResult['exception'] === 'webservice_access_exception') {
            $this->warn("⚠️  ВАЖНО: Обнаружена проблема с правами доступа!");
            $this->newLine();
            $this->line("Для исправления проблемы выполните следующие шаги:");
            $this->newLine();
            $this->line("1. Войдите в Moodle как администратор");
            $this->line("2. Перейдите в: Настройки сайта → Плагины → Веб-сервисы → Управление токенами");
            $this->line("3. Найдите ваш токен API (или создайте новый)");
            $this->line("4. Перейдите в: Настройки сайта → Плагины → Веб-сервисы → Управление протоколами");
            $this->line("5. Выберите протокол 'REST' и нажмите 'Изменить'");
            $this->line("6. В разделе 'Функции' добавьте следующие функции:");
            $this->line("   - mod_assign_get_assignments");
            $this->line("   - mod_assign_get_submissions");
            $this->line("   - mod_assign_get_grades");
            $this->line("   - mod_quiz_get_quizzes_by_courses");
            $this->line("   - mod_quiz_get_user_attempts");
            $this->line("   - mod_quiz_get_user_best_grade");
            $this->line("   - mod_forum_get_forums_by_courses");
            $this->line("   - mod_forum_get_posts_by_discussion");
            $this->line("   - core_grades_get_grades");
            $this->line("   - core_course_get_contents (если нужно)");
            $this->newLine();
            $this->line("Альтернативно:");
            $this->line("1. Перейдите в: Настройки сайта → Плагины → Веб-сервисы → Внешние службы");
            $this->line("2. Создайте или отредактируйте службу");
            $this->line("3. Добавьте необходимые функции в список разрешенных");
            $this->line("4. Убедитесь, что токен использует эту службу");
            $this->newLine();
        }
        
        return 0;
    }
}

