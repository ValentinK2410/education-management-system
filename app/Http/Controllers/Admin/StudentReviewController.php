<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentActivityProgress;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentReviewController extends Controller
{
    /**
     * Показать страницу проверки студентов
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();

        // Проверяем, что пользователь является преподавателем или администратором
        // Пользователь может иметь несколько ролей, поэтому проверяем наличие роли instructor
        if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
            abort(403, 'Доступ разрешен только преподавателям и администраторам');
        }

        $instructor = $user;

        // Получаем все курсы преподавателя для присвоения к активностям
        $courses = \App\Models\Course::where('instructor_id', $instructor->id)->get();
        $courseIds = $courses->pluck('id');
        $coursesById = $courses->keyBy('id');

        if ($courseIds->isEmpty()) {
            return view('admin.student-review.index', [
                'assignments' => collect(),
                'quizzes' => collect(),
                'forums' => collect(),
                'courses' => collect(), // Добавляем пустую коллекцию курсов
            ]);
        }

        // Вкладка "Задания" - задания, ожидающие проверки
        $assignments = StudentActivityProgress::whereIn('course_id', $courseIds)
            ->whereHas('activity', function ($query) {
                $query->where('activity_type', 'assign');
            })
            ->whereHas('user', function ($query) {
                // Показываем только студентов, исключаем преподавателей и администраторов
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('slug', 'student');
                })->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->whereIn('slug', ['instructor', 'admin']);
                });
            })
            ->where(function ($query) {
                $query->where('needs_grading', true)
                      ->orWhereNotNull('submitted_at')
                      ->orWhere('has_draft', true);
            })
            ->with(['user.roles', 'course', 'activity.course'])
            ->orderBy('submitted_at', 'desc')
            ->orderBy('draft_created_at', 'desc')
            ->get()
            ->map(function ($progress) use ($coursesById) {
                // Присваиваем курс к активности для корректной работы moodle_url
                if ($progress->activity && isset($coursesById[$progress->course_id])) {
                    $progress->activity->setRelation('course', $coursesById[$progress->course_id]);
                }

                // Определяем статус задания
                if ($progress->needs_grading && $progress->submitted_at) {
                    $progress->status = 'submitted';
                    $progress->status_text = 'Ожидает проверки';
                    $progress->status_class = 'warning';
                } elseif ($progress->submitted_at) {
                    $progress->status = 'submitted';
                    $progress->status_text = 'Сдано';
                    $progress->status_class = 'info';
                } elseif ($progress->has_draft) {
                    $progress->status = 'in_progress';
                    $progress->status_text = 'Есть черновик';
                    $progress->status_class = 'secondary';
                } else {
                    $progress->status = 'not_started';
                    $progress->status_text = 'Неизвестно';
                    $progress->status_class = 'secondary';
                }

                // Дата для отображения
                $progress->display_date = $progress->submitted_at ?? $progress->draft_created_at ?? $progress->created_at;

                return $progress;
            });

        // Вкладка "Тесты" - все тесты со статусом ответил/не ответил
        // Получаем данные из базы (быстро, без синхронизации с Moodle)
        $quizzes = StudentActivityProgress::whereIn('course_id', $courseIds)
            ->whereHas('activity', function ($query) {
                $query->where('activity_type', 'quiz');
            })
            ->whereHas('user', function ($query) {
                // Показываем только студентов, исключаем преподавателей и администраторов
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('slug', 'student');
                })->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->whereIn('slug', ['instructor', 'admin']);
                });
            })
            ->with(['user.roles', 'course', 'activity.course'])
            ->get()
            ->map(function ($progress) use ($coursesById) {
                // Присваиваем курс к активности для корректной работы moodle_url
                if ($progress->activity && isset($coursesById[$progress->course_id])) {
                    $progress->activity->setRelation('course', $coursesById[$progress->course_id]);
                }

                // Определяем статус на основе данных из базы
                // Проверяем, есть ли оценка (тест проверен)
                // Учитываем, что grade может быть 0 (ноль), что тоже является валидной оценкой
                $hasGrade = false;
                $gradeValue = null;

                // Проверяем оценку разными способами
                if ($progress->grade !== null && $progress->grade !== '') {
                    // Преобразуем в число для проверки
                    $gradeValue = is_numeric($progress->grade) ? (float)$progress->grade : null;
                    if ($gradeValue !== null) {
                        // Оценка может быть 0 (ноль), что тоже валидно!
                        $hasGrade = true;
                    }
                }

                // Также проверяем is_graded, если оценка не найдена напрямую
                if (!$hasGrade && $progress->is_graded) {
                    $hasGrade = true;
                }

                // Если есть оценка (даже если is_graded не установлен), считаем тест выполненным
                if ($hasGrade) {
                    $progress->status = 'graded';
                    $progress->status_text = 'Выполнено';
                    $progress->status_class = 'success';
                    // Убеждаемся, что is_graded установлен, если есть оценка
                    if (!$progress->is_graded) {
                        $progress->is_graded = true;
                        // Сохраняем в базу для будущих запросов
                        $progress->save();
                    }
                }
                // Проверяем, сдан ли тест (есть попытки и завершены)
                elseif ($progress->submitted_at !== null || ($progress->attempts_count > 0 && $progress->needs_grading)) {
                    $progress->status = 'submitted';
                    $progress->status_text = 'Сдано';
                    $progress->status_class = 'warning';
                }
                // Проверяем, начат ли тест (есть попытки, но не завершены)
                elseif ($progress->attempts_count > 0 && !$progress->submitted_at) {
                    $progress->status = 'in_progress';
                    $progress->status_text = 'В процессе';
                    $progress->status_class = 'info';
                }
                // Проверяем, есть ли попытки (старый статус для обратной совместимости)
                elseif ($progress->attempts_count > 0) {
                    $progress->status = 'in_progress';
                    $progress->status_text = 'Ответил';
                    $progress->status_class = 'success';
                }
                // Тест не начат
                else {
                    $progress->status = 'not_started';
                    $progress->status_text = 'Не ответил';
                    $progress->status_class = 'warning';
                }

                return $progress;
            })
            ->sortByDesc(function ($progress) {
                // Сортируем: сначала отвеченные, потом неотвеченные
                return in_array($progress->status, ['in_progress', 'graded', 'submitted']) ? 1 : 0;
            })
            ->values();

        // Вкладка "Форумы" - форумы, ожидающие ответа преподавателя
        // Получаем данные из базы (быстро, без синхронизации с Moodle)
        // Показываем все форумы, где студент написал пост (независимо от needs_response)
        // но приоритет отдаем тем, где needs_response = true
        $forumsQuery = StudentActivityProgress::whereIn('course_id', $courseIds)
            ->whereHas('activity', function ($query) {
                $query->where('activity_type', 'forum');
            })
            ->whereHas('user', function ($query) {
                // Показываем только студентов, исключаем преподавателей и администраторов
                $query->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('slug', 'student');
                })->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->whereIn('slug', ['instructor', 'admin']);
                });
            })
            ->where(function($query) {
                // Показываем форумы, где:
                // 1. needs_response = true (требуется ответ преподавателя)
                // 2. ИЛИ есть submitted_at (студент написал пост)
                // 3. ИЛИ есть progress_data с постами
                $query->where('needs_response', true)
                    ->orWhereNotNull('submitted_at')
                    ->orWhereNotNull('progress_data');
            })
            ->with(['user.roles', 'course', 'activity.course']);

        // Логируем запрос для отладки
        $forumsCount = $forumsQuery->count();
        Log::info('Запрос форумов для страницы student-review', [
            'instructor_id' => $instructor->id,
            'course_ids' => $courseIds->toArray(),
            'forums_count_before_filter' => $forumsCount
        ]);

        $forums = $forumsQuery
            ->orderByRaw('needs_response DESC, submitted_at DESC')
            ->get()
            ->filter(function ($progress) {
                // Дополнительная фильтрация: проверяем, что есть реальные данные о постах
                if ($progress->progress_data && is_array($progress->progress_data)) {
                    $posts = $progress->progress_data['posts'] ?? [];
                    return !empty($posts);
                }
                // Если нет progress_data, но есть submitted_at, показываем
                return !is_null($progress->submitted_at);
            })
            ->map(function ($progress) use ($coursesById) {
                // Присваиваем курс к активности для корректной работы moodle_url
                if ($progress->activity && isset($coursesById[$progress->course_id])) {
                    $progress->activity->setRelation('course', $coursesById[$progress->course_id]);
                }

                // Извлекаем текст сообщения из progress_data или draft_data
                $progress->message_text = $this->extractForumMessage($progress);

                // Если needs_response не установлен, но есть посты, устанавливаем его
                if (is_null($progress->needs_response) && $progress->progress_data) {
                    $posts = $progress->progress_data['posts'] ?? [];
                    if (!empty($posts)) {
                        // Проверяем, есть ли посты с needs_response = true
                        $hasNeedsResponse = false;
                        foreach ($posts as $post) {
                            if (isset($post['needs_response']) && $post['needs_response']) {
                                $hasNeedsResponse = true;
                                break;
                            }
                        }
                        // Если ни один пост не имеет needs_response = true, значит все посты получили ответ
                        $progress->needs_response = $hasNeedsResponse;
                    }
                }

                return $progress;
            })
            ->values();

        Log::info('Результат фильтрации форумов', [
            'forums_count_after_filter' => $forums->count(),
            'forums_with_needs_response' => $forums->where('needs_response', true)->count()
        ]);

        return view('admin.student-review.index', compact('assignments', 'quizzes', 'forums', 'courses'));
    }

    /**
     * Синхронизировать данные для конкретного курса из Moodle API
     *
     * @param Request $request
     * @param int $courseId ID курса
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncCourseData(Request $request, int $courseId)
    {
        $user = auth()->user();

        // Проверяем права доступа
        if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что курс принадлежит преподавателю
        $course = \App\Models\Course::where('id', $courseId)
            ->where('instructor_id', $user->id)
            ->first();

        if (!$course) {
            return response()->json(['error' => 'Курс не найден'], 404);
        }

        if (!$course->moodle_course_id) {
            return response()->json(['error' => 'Курс не синхронизирован с Moodle'], 400);
        }

        try {
            // Используем токен текущего пользователя (преподавателя/администратора)
            $user = auth()->user();
            $userToken = $user ? $user->getMoodleToken() : null;
            $moodleApi = new MoodleApiService(null, $userToken);
            $tab = $request->get('tab', 'quizzes'); // assignments, quizzes, forums

            // Получаем студентов курса
            $students = \App\Models\User::whereHas('courses', function ($query) use ($courseId) {
                $query->where('courses.id', $courseId);
            })
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'student');
            })
            ->whereNotNull('moodle_user_id')
            ->get();

            $updatedCount = 0;
            $errors = [];

            if ($tab === 'assignments') {
                // Синхронизация заданий
                $moodleAssignments = $moodleApi->getCourseAssignments($course->moodle_course_id);

                if ($moodleAssignments === false) {
                    return response()->json(['error' => 'Не удалось получить задания из Moodle'], 500);
                }

                foreach ($students as $student) {
                    try {
                        $submissions = $moodleApi->getStudentSubmissions(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $moodleAssignments
                        );

                        $grades = $moodleApi->getStudentGrades(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $moodleAssignments
                        );

                        Log::info('Получены данные о заданиях студента из Moodle', [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_moodle_id' => $student->moodle_user_id,
                            'course_id' => $courseId,
                            'assignments_count' => count($moodleAssignments),
                            'submissions_keys' => array_keys($submissions !== false ? $submissions : []),
                            'grades_keys' => array_keys($grades !== false ? $grades : []),
                        ]);

                        // Обновляем данные в базе
                        foreach ($moodleAssignments as $moodleAssignment) {
                            // Ищем или создаем CourseActivity для задания
                            $activity = \App\Models\CourseActivity::firstOrCreate(
                                [
                                    'course_id' => $courseId,
                                    'moodle_activity_id' => $moodleAssignment['id'],
                                    'activity_type' => 'assign',
                                ],
                                [
                                    'title' => $moodleAssignment['name'] ?? 'Задание',
                                    'description' => $moodleAssignment['intro'] ?? null,
                                    'is_active' => true,
                                ]
                            );

                            $submission = ($submissions !== false) ? ($submissions[$moodleAssignment['id']] ?? null) : null;
                            $grade = ($grades !== false) ? ($grades[$moodleAssignment['id']] ?? null) : null;

                            // Определяем дату сдачи
                            $submittedAt = null;
                            if ($submission) {
                                if (isset($submission['timesubmitted']) && $submission['timesubmitted'] > 0) {
                                    $submittedAt = date('Y-m-d H:i:s', $submission['timesubmitted']);
                                } elseif (isset($submission['timemodified']) && $submission['timemodified'] > 0) {
                                    $submittedAt = date('Y-m-d H:i:s', $submission['timemodified']);
                                } elseif (isset($submission['timecreated']) && $submission['timecreated'] > 0) {
                                    $submittedAt = date('Y-m-d H:i:s', $submission['timecreated']);
                                }
                            }

                            // Проверяем наличие оценки
                            $hasGrade = false;
                            $gradeValue = null;
                            $gradedAt = null;

                            if ($grade && isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '') {
                                $gradeValue = (float)$grade['grade'];
                                $hasGrade = true;
                                if (isset($grade['timecreated'])) {
                                    $gradedAt = date('Y-m-d H:i:s', $grade['timecreated']);
                                }
                            }

                            // Определяем статус
                            $status = 'not_started';
                            $needsGrading = false;

                            if ($hasGrade) {
                                $status = 'graded';
                            } elseif ($submission && ($submission['status'] ?? '') === 'submitted') {
                                $status = 'submitted';
                                $needsGrading = true;
                            } elseif ($submission) {
                                $status = 'in_progress';
                            }

                            Log::info('Синхронизация задания для студента', [
                                'student_id' => $student->id,
                                'student_name' => $student->name,
                                'assignment_id' => $moodleAssignment['id'],
                                'assignment_name' => $moodleAssignment['name'] ?? 'Unknown',
                                'has_submission' => $submission !== null,
                                'has_grade' => $hasGrade,
                                'grade_value' => $gradeValue,
                                'status' => $status,
                            ]);

                            StudentActivityProgress::updateOrCreate(
                                [
                                    'user_id' => $student->id,
                                    'course_id' => $courseId,
                                    'activity_id' => $activity->id,
                                ],
                                [
                                    'submitted_at' => $submittedAt,
                                    'grade' => $hasGrade ? $gradeValue : null,
                                    'max_grade' => isset($moodleAssignment['grade']) && $moodleAssignment['grade'] > 0
                                        ? (float)$moodleAssignment['grade']
                                        : null,
                                    'is_graded' => $hasGrade,
                                    'needs_grading' => $needsGrading,
                                    'status' => $status,
                                    'graded_at' => $gradedAt,
                                ]
                            );

                            $updatedCount++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ошибка для студента {$student->name}: " . $e->getMessage();
                        Log::warning('Ошибка синхронизации заданий для студента', [
                            'student_id' => $student->id,
                            'course_id' => $courseId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } elseif ($tab === 'quizzes') {
                // Синхронизация тестов
                $moodleQuizzes = $moodleApi->getCourseQuizzes($course->moodle_course_id);

                if ($moodleQuizzes === false) {
                    return response()->json(['error' => 'Не удалось получить тесты из Moodle'], 500);
                }

                foreach ($students as $student) {
                    try {
                        // Получаем попытки и оценки более эффективно
                        $quizAttempts = $moodleApi->getStudentQuizAttempts(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $moodleQuizzes
                        );

                        $quizGrades = $moodleApi->getStudentQuizGrades(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $moodleQuizzes
                        );

                        Log::info('Получены данные о тестах студента из Moodle', [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_moodle_id' => $student->moodle_user_id,
                            'course_id' => $courseId,
                            'quizzes_count' => count($moodleQuizzes),
                            'attempts_keys' => array_keys($quizAttempts),
                            'grades_keys' => array_keys($quizGrades),
                            'grades_data' => $quizGrades
                        ]);

                        // Обновляем данные в базе
                        // ВАЖНО: Создаем записи для ВСЕХ тестов курса, даже если у студента нет попыток
                        foreach ($moodleQuizzes as $moodleQuiz) {
                            // Ищем или создаем CourseActivity для теста
                            $activity = \App\Models\CourseActivity::firstOrCreate(
                                [
                                    'course_id' => $courseId,
                                    'moodle_activity_id' => $moodleQuiz['id'],
                                    'activity_type' => 'quiz',
                                ],
                                [
                                    'title' => $moodleQuiz['name'] ?? 'Тест',
                                    'description' => $moodleQuiz['intro'] ?? null,
                                    'is_active' => true,
                                ]
                            );

                            $attempts = $quizAttempts[$moodleQuiz['id']] ?? [];
                            $grade = $quizGrades[$moodleQuiz['id']] ?? null;

                            // Определяем, завершена ли последняя попытка
                            $latestAttempt = !empty($attempts) ? end($attempts) : null;
                            $isFinished = $latestAttempt && ($latestAttempt['state'] ?? '') === 'finished';

                            // Улучшенная проверка наличия оценки
                            // Проверяем оценку из mod_quiz_get_user_best_grade
                            $hasGrade = false;
                            $gradeValue = null;
                            $gradedAt = null;

                            if ($grade) {
                                // Проверяем наличие оценки разными способами
                                if (isset($grade['grade']) && $grade['grade'] !== null && $grade['grade'] !== '') {
                                    $gradeValue = (float)$grade['grade'];
                                    // Оценка может быть 0 (ноль), что тоже валидно!
                                    // Проверяем hasgrade, но если его нет, считаем что оценка есть
                                    if (isset($grade['hasgrade'])) {
                                        $hasGrade = $grade['hasgrade'];
                                    } else {
                                        // Если hasgrade не указан, но grade есть, считаем что оценка выставлена
                                        $hasGrade = true;
                                    }
                                    // Проверяем дату оценки
                                    if (isset($grade['timecreated'])) {
                                        $gradedAt = date('Y-m-d H:i:s', $grade['timecreated']);
                                    }
                                } elseif (isset($grade['hasgrade']) && $grade['hasgrade'] && isset($grade['grade'])) {
                                    // Если hasgrade = true, но grade может быть null (редкий случай)
                                    // В этом случае оценка может быть в попытках
                                    $hasGrade = true;
                                }
                            }

                            // Дополнительно проверяем оценки из попыток (если есть завершенные попытки)
                            if (!$hasGrade && !empty($attempts)) {
                                foreach ($attempts as $attempt) {
                                    if (($attempt['state'] ?? '') === 'finished' && isset($attempt['sumgrades'])) {
                                        // Если есть завершенная попытка с оценкой, используем её
                                        $attemptGrade = $attempt['sumgrades'] ?? null;
                                        if ($attemptGrade !== null && $attemptGrade !== '') {
                                            $gradeValue = (float)$attemptGrade;
                                            $hasGrade = true;
                                            if (isset($attempt['timefinish'])) {
                                                $gradedAt = date('Y-m-d H:i:s', $attempt['timefinish']);
                                            }
                                            break;
                                        }
                                    }
                                }
                            }

                            // Получаем существующую запись для проверки даты изменения
                            $existingProgress = StudentActivityProgress::where('user_id', $student->id)
                                ->where('course_id', $courseId)
                                ->where('activity_id', $activity->id)
                                ->first();

                            Log::info('Синхронизация теста для студента', [
                                'student_id' => $student->id,
                                'student_name' => $student->name,
                                'student_moodle_id' => $student->moodle_user_id,
                                'quiz_id' => $moodleQuiz['id'],
                                'quiz_name' => $moodleQuiz['name'] ?? 'Unknown',
                                'activity_id' => $activity->id,
                                'attempts_count' => count($attempts),
                                'has_grade' => $hasGrade,
                                'grade_value' => $gradeValue,
                                'is_finished' => $isFinished,
                                'latest_attempt_state' => $latestAttempt['state'] ?? null,
                                'latest_attempt_timestart' => $latestAttempt['timestart'] ?? null,
                                'latest_attempt_timefinish' => $latestAttempt['timefinish'] ?? null,
                                'existing_progress_id' => $existingProgress ? $existingProgress->id : null,
                                'existing_grade' => $existingProgress ? $existingProgress->grade : null,
                                'existing_updated_at' => $existingProgress ? $existingProgress->updated_at : null,
                                'grade_from_api' => $grade,
                            ]);

                            // ВАЖНО: Создаем запись для ВСЕХ студентов и ВСЕХ тестов, даже если нет попыток
                            $progress = StudentActivityProgress::updateOrCreate(
                                [
                                    'user_id' => $student->id,
                                    'course_id' => $courseId,
                                    'activity_id' => $activity->id,
                                ],
                                [
                                    'attempts_count' => count($attempts),
                                    'last_attempt_at' => !empty($attempts) && isset($latestAttempt['timestart'])
                                        ? date('Y-m-d H:i:s', $latestAttempt['timestart'])
                                        : null,
                                    'submitted_at' => $isFinished && isset($latestAttempt['timefinish'])
                                        ? date('Y-m-d H:i:s', $latestAttempt['timefinish'])
                                        : null,
                                    'grade' => $hasGrade ? (float)$gradeValue : null,
                                    'max_grade' => isset($moodleQuiz['grade']) && $moodleQuiz['grade'] > 0
                                        ? (float)$moodleQuiz['grade']
                                        : null,
                                    'is_graded' => $hasGrade, // Тест проверен, если есть оценка (включая 0)
                                    'needs_grading' => $isFinished && !$hasGrade, // Нужна проверка, если завершен, но нет оценки
                                    'status' => $hasGrade ? 'graded' : ($isFinished ? 'submitted' : (!empty($attempts) ? 'in_progress' : 'not_started')),
                                    'graded_at' => $gradedAt,
                                ]
                            );

                            Log::info('Сохранена запись прогресса теста', [
                                'progress_id' => $progress->id,
                                'student_id' => $student->id,
                                'quiz_id' => $moodleQuiz['id'],
                                'grade' => $progress->grade,
                                'is_graded' => $progress->is_graded,
                                'status' => $progress->status,
                                'submitted_at' => $progress->submitted_at,
                                'graded_at' => $progress->graded_at,
                                'updated_at' => $progress->updated_at,
                            ]);

                            $updatedCount++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ошибка для студента {$student->name}: " . $e->getMessage();
                        Log::warning('Ошибка синхронизации тестов для студента', [
                            'student_id' => $student->id,
                            'course_id' => $courseId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } elseif ($tab === 'forums') {
                // Синхронизация форумов
                $moodleForums = $moodleApi->getCourseForums($course->moodle_course_id);

                if ($moodleForums === false) {
                    return response()->json(['error' => 'Не удалось получить форумы из Moodle'], 500);
                }

                        foreach ($students as $student) {
                    try {
                        $forumPosts = $moodleApi->getStudentForumPosts(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $moodleForums
                        );

                        Log::info('Синхронизация форумов для студента', [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'moodle_user_id' => $student->moodle_user_id,
                            'course_id' => $courseId,
                            'forums_count' => count($moodleForums),
                            'forum_posts_keys' => array_keys($forumPosts),
                            'total_posts' => array_sum(array_map('count', $forumPosts)),
                            'forum_posts_details' => array_map(function($posts) {
                                return [
                                    'posts_count' => count($posts),
                                    'posts_with_needs_response' => count(array_filter($posts, function($p) {
                                        return $p['needs_response'] ?? false;
                                    })),
                                    'posts' => array_map(function($p) {
                                        return [
                                            'id' => $p['id'] ?? null,
                                            'subject' => $p['subject'] ?? null,
                                            'timecreated' => $p['timecreated'] ?? null,
                                            'needs_response' => $p['needs_response'] ?? false,
                                            'has_teacher_reply' => $p['has_teacher_reply'] ?? false,
                                            'is_last_post' => $p['is_last_post'] ?? false
                                        ];
                                    }, $posts)
                                ];
                            }, $forumPosts)
                        ]);

                        // Обновляем данные в базе
                        foreach ($moodleForums as $moodleForum) {
                            $activity = \App\Models\CourseActivity::where('course_id', $courseId)
                                ->where('moodle_activity_id', $moodleForum['id'])
                                ->where('activity_type', 'forum')
                                ->first();

                            if (!$activity) {
                                continue;
                            }

                            $posts = $forumPosts[$moodleForum['id']] ?? [];
                            $needsResponse = false;
                            $latestPostTime = 0;
                            $hasStudentPosts = false;

                            // Проверяем посты студента
                            $lastStudentPost = null;
                            $lastStudentPostTime = 0;

                            foreach ($posts as $post) {
                                $hasStudentPosts = true;

                                // Если есть пост студента с needs_response = true, значит нужен ответ
                                if (isset($post['needs_response']) && $post['needs_response']) {
                                    $needsResponse = true;
                                }

                                // Отслеживаем последний пост студента
                                $postTime = $post['timecreated'] ?? 0;
                                if ($postTime > $lastStudentPostTime) {
                                    $lastStudentPostTime = $postTime;
                                    $lastStudentPost = $post;
                                }

                                if ($postTime > $latestPostTime) {
                                    $latestPostTime = $postTime;
                                }
                            }

                            // ВАЖНО: Если последний пост студента требует ответа (needs_response = true),
                            // или это последний пост в обсуждении и после него нет ответа преподавателя,
                            // устанавливаем needsResponse = true
                            if ($lastStudentPost && isset($lastStudentPost['needs_response']) && $lastStudentPost['needs_response']) {
                                $needsResponse = true;
                            }

                            // Дополнительная проверка: если последний пост студента помечен как последний в обсуждении
                            // и требует ответа, устанавливаем needsResponse
                            if ($lastStudentPost && isset($lastStudentPost['is_last_post']) && $lastStudentPost['is_last_post']) {
                                if (isset($lastStudentPost['needs_response']) && $lastStudentPost['needs_response']) {
                                    $needsResponse = true;
                                }
                            }

                            // Сохраняем данные только если студент написал хотя бы один пост
                            if ($hasStudentPosts) {
                                $progressData = [
                                    'posts' => $posts,
                                    'posts_count' => count($posts),
                                ];

                                Log::info('Сохранение данных форума для студента', [
                                    'student_id' => $student->id,
                                    'activity_id' => $activity->id,
                                    'forum_id' => $moodleForum['id'],
                                    'posts_count' => count($posts),
                                    'needs_response' => $needsResponse,
                                    'latest_post_time' => $latestPostTime
                                ]);

                                StudentActivityProgress::updateOrCreate(
                                    [
                                        'user_id' => $student->id,
                                        'course_id' => $courseId,
                                        'activity_id' => $activity->id,
                                    ],
                                    [
                                        'needs_response' => $needsResponse,
                                        'submitted_at' => $latestPostTime ? date('Y-m-d H:i:s', $latestPostTime) : null,
                                        'status' => $needsResponse ? 'submitted' : 'completed',
                                        'progress_data' => $progressData, // Laravel автоматически конвертирует в JSON через cast
                                    ]
                                );

                                $updatedCount++;
                            } else {
                                // Если студент не писал в форум, удаляем запись (если была)
                                StudentActivityProgress::where('user_id', $student->id)
                                    ->where('course_id', $courseId)
                                    ->where('activity_id', $activity->id)
                                    ->delete();
                            }

                            $updatedCount++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ошибка для студента {$student->name}: " . $e->getMessage();
                        Log::warning('Ошибка синхронизации форумов для студента', [
                            'student_id' => $student->id,
                            'course_id' => $courseId,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'message' => "Синхронизировано записей: {$updatedCount}"
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации данных курса', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверить данные о заданиях и тестах напрямую из Moodle API
     * Возвращает детальную информацию о запросах и ответах
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMoodleAssignments(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь не авторизован'
                ], 401)->header('Content-Type', 'application/json');
            }

            // Проверяем права доступа
            if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Доступ запрещен. Требуется роль преподавателя или администратора.'
                ], 403)->header('Content-Type', 'application/json');
            }

        $instructor = $user;
        $courses = \App\Models\Course::where('instructor_id', $instructor->id)->get();

        if ($courses->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет курсов',
                'courses' => []
            ]);
        }

        // Определяем, что проверяем: assignments, quizzes, forums или all (все типы)
        $checkType = $request->get('type', 'all'); // assignments, quizzes, forums или all

        $results = [];

        // Используем токен текущего пользователя (преподавателя/администратора)
        $user = auth()->user();
        $userToken = $user ? $user->getMoodleToken() : null;
        $moodleApi = new MoodleApiService(null, $userToken);

        foreach ($courses as $course) {
                if (!$course->moodle_course_id) {
                    $results[] = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'moodle_course_id' => null,
                        'error' => 'Курс не синхронизирован с Moodle (нет moodle_course_id)',
                        'assignments' => [],
                        'submissions' => []
                    ];
                    continue;
                }

                // Если проверяем все типы, создаем общий результат для курса
                if ($checkType === 'all') {
                    $courseResult = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'moodle_course_id' => $course->moodle_course_id,
                        'assignments' => [],
                        'quizzes' => [],
                        'forums' => [],
                        'students' => []
                    ];

                    // Проверяем задания
                    $assignments = $moodleApi->getCourseAssignments($course->moodle_course_id);
                    if ($assignments !== false) {
                        $courseResult['assignments'] = $assignments;
                        $courseResult['assignments_count'] = is_array($assignments) ? count($assignments) : 0;
                    } else {
                        $courseResult['assignments_count'] = 0;
                    }

                    // Проверяем тесты
                    $quizzes = $moodleApi->getCourseQuizzes($course->moodle_course_id);
                    if ($quizzes !== false) {
                        $courseResult['quizzes'] = $quizzes;
                        $courseResult['quizzes_count'] = is_array($quizzes) ? count($quizzes) : 0;
                    } else {
                        $courseResult['quizzes_count'] = 0;
                    }

                    // Проверяем форумы
                    $forums = $moodleApi->getCourseForums($course->moodle_course_id);
                    if ($forums !== false) {
                        $courseResult['forums'] = $forums;
                        $courseResult['forums_count'] = is_array($forums) ? count($forums) : 0;
                    } else {
                        $courseResult['forums_count'] = 0;
                    }

                    // Получаем студентов курса
                    $students = \App\Models\User::whereHas('courses', function ($query) use ($course) {
                        $query->where('courses.id', $course->id);
                    })
                    ->whereHas('roles', function ($query) {
                        $query->where('slug', 'student');
                    })
                    ->whereNotNull('moodle_user_id')
                    ->get();

                    $courseResult['students_count'] = $students ? $students->count() : 0;

                    // Для каждого студента получаем данные по всем типам
                    $totalUnansweredPosts = 0;
                    foreach ($students as $student) {
                        $studentResult = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_email' => $student->email,
                            'moodle_user_id' => $student->moodle_user_id,
                            'submissions' => [],
                            'quiz_attempts' => [],
                            'quiz_grades' => [],
                            'forum_posts' => [],
                            'unanswered_posts' => []
                        ];

                        // Получаем сдачи студента по заданиям
                        if ($courseResult['assignments_count'] > 0) {
                            $submissions = $moodleApi->getStudentSubmissions(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $assignments
                            );
                            if ($submissions !== false) {
                                $studentResult['submissions'] = $submissions;
                                $studentResult['submissions_count'] = count($submissions);
                            }

                            $grades = $moodleApi->getStudentGrades(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $assignments
                            );
                            if ($grades !== false) {
                                $studentResult['grades'] = $grades;
                                $studentResult['grades_count'] = count($grades);
                            }
                        }

                        // Получаем попытки и оценки студента по тестам
                        if ($courseResult['quizzes_count'] > 0) {
                            $quizAttempts = $moodleApi->getStudentQuizAttempts(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $quizzes
                            );
                            if ($quizAttempts !== false) {
                                $studentResult['quiz_attempts'] = $quizAttempts;
                                $totalAttempts = 0;
                                foreach ($quizAttempts as $attempts) {
                                    $totalAttempts += count($attempts);
                                }
                                $studentResult['quiz_attempts_count'] = $totalAttempts;
                            }

                            $quizGrades = $moodleApi->getStudentQuizGrades(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $quizzes
                            );
                            if ($quizGrades !== false) {
                                $studentResult['quiz_grades'] = $quizGrades;
                                $studentResult['quiz_grades_count'] = count($quizGrades);
                            }
                        }

                        // Получаем посты студента на форумах
                        if ($courseResult['forums_count'] > 0) {
                            $forumPosts = $moodleApi->getStudentForumPosts(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $forums
                            );

                            if ($forumPosts !== false && !empty($forumPosts)) {
                                $studentResult['forum_posts'] = $forumPosts;

                                $totalPosts = 0;
                                $unansweredPosts = 0;

                                foreach ($forumPosts as $forumId => $posts) {
                                    foreach ($posts as $post) {
                                        $totalPosts++;
                                        if (isset($post['needs_response']) && $post['needs_response']) {
                                            $unansweredPosts++;
                                            $studentResult['unanswered_posts'][] = [
                                                'forum_id' => $forumId,
                                                'post_id' => $post['id'] ?? null,
                                                'subject' => $post['subject'] ?? 'Без темы',
                                                'message' => isset($post['message']) ? strip_tags(substr($post['message'], 0, 200)) : '',
                                                'timecreated' => $post['timecreated'] ?? null,
                                                'has_teacher_reply' => $post['has_teacher_reply'] ?? false
                                            ];
                                        }
                                    }
                                }

                                $studentResult['total_posts'] = $totalPosts;
                                $studentResult['unanswered_posts_count'] = $unansweredPosts;
                                $totalUnansweredPosts += $unansweredPosts;
                            }
                        }

                        $courseResult['students'][] = $studentResult;
                    }

                    $courseResult['total_unanswered_posts'] = $totalUnansweredPosts;
                    $results[] = $courseResult;
                } elseif ($checkType === 'quizzes') {
                    // Проверка тестов
                    $courseResult = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'moodle_course_id' => $course->moodle_course_id,
                        'api_request' => [
                            'function' => 'mod_quiz_get_quizzes_by_courses',
                            'url' => config('services.moodle.url') . '/webservice/rest/server.php',
                            'params' => [
                                'courseids' => [$course->moodle_course_id],
                                'wstoken' => '***скрыто***',
                                'wsfunction' => 'mod_quiz_get_quizzes_by_courses',
                                'moodlewsrestformat' => 'json'
                            ]
                        ],
                        'quizzes' => [],
                        'students' => []
                    ];

                    // Получаем тесты курса
                    $quizzes = $moodleApi->getCourseQuizzes($course->moodle_course_id);

                    if ($quizzes === false) {
                        // Получаем полный ответ API для детального анализа ошибки
                        $apiResponse = $moodleApi->call('mod_quiz_get_quizzes_by_courses', [
                            'courseids' => [$course->moodle_course_id]
                        ]);

                        $courseResult['error'] = 'Не удалось получить тесты из Moodle API';
                        $courseResult['api_response'] = $apiResponse;
                        $results[] = $courseResult;
                        continue;
                    }

                    $courseResult['quizzes'] = $quizzes;
                    $courseResult['quizzes_count'] = is_array($quizzes) ? count($quizzes) : 0;

                    // Получаем студентов курса
                    $students = \App\Models\User::whereHas('courses', function ($query) use ($course) {
                        $query->where('courses.id', $course->id);
                    })
                    ->whereHas('roles', function ($query) {
                        $query->where('slug', 'student');
                    })
                    ->whereNotNull('moodle_user_id')
                    ->get();

                    $courseResult['students_count'] = $students ? $students->count() : 0;

                    // Для каждого студента получаем попытки и оценки
                    foreach ($students as $student) {
                        $studentResult = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_email' => $student->email,
                            'moodle_user_id' => $student->moodle_user_id,
                            'quiz_attempts' => [],
                            'quiz_grades' => []
                        ];

                        // Получаем попытки студента
                        $quizAttempts = $moodleApi->getStudentQuizAttempts(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $quizzes
                        );

                        if ($quizAttempts !== false) {
                            $studentResult['quiz_attempts'] = $quizAttempts;
                            $totalAttempts = 0;
                            foreach ($quizAttempts as $attempts) {
                                $totalAttempts += count($attempts);
                            }
                            $studentResult['quiz_attempts_count'] = $totalAttempts;
                        }

                        // Получаем оценки студента
                        $quizGrades = $moodleApi->getStudentQuizGrades(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $quizzes
                        );

                        if ($quizGrades !== false) {
                            $studentResult['quiz_grades'] = $quizGrades;
                            $studentResult['quiz_grades_count'] = count($quizGrades);
                        }

                        $courseResult['students'][] = $studentResult;
                    }

                    // Получаем полный ответ API для логирования
                    $apiResponse = $moodleApi->call('mod_quiz_get_quizzes_by_courses', [
                        'courseids' => [$course->moodle_course_id]
                    ]);

                    $courseResult['api_response'] = $apiResponse;
                    $results[] = $courseResult;
                } elseif ($checkType === 'forums') {
                    // Проверка форумов
                    $courseResult = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'moodle_course_id' => $course->moodle_course_id,
                        'api_request' => [
                            'function' => 'mod_forum_get_forums_by_courses',
                            'url' => config('services.moodle.url') . '/webservice/rest/server.php',
                            'params' => [
                                'courseids' => [$course->moodle_course_id],
                                'wstoken' => '***скрыто***',
                                'wsfunction' => 'mod_forum_get_forums_by_courses',
                                'moodlewsrestformat' => 'json'
                            ]
                        ],
                        'forums' => [],
                        'students' => []
                    ];

                    // Получаем форумы курса
                    $forums = $moodleApi->getCourseForums($course->moodle_course_id);

                    if ($forums === false) {
                        // Получаем полный ответ API для детального анализа ошибки
                        $apiResponse = $moodleApi->call('mod_forum_get_forums_by_courses', [
                            'courseids' => [$course->moodle_course_id]
                        ]);

                        $courseResult['error'] = 'Не удалось получить форумы из Moodle API';
                        $courseResult['api_response'] = $apiResponse;
                        $results[] = $courseResult;
                        continue;
                    }

                    $courseResult['forums'] = $forums;
                    $courseResult['forums_count'] = is_array($forums) ? count($forums) : 0;

                    // Получаем студентов курса
                    $students = \App\Models\User::whereHas('courses', function ($query) use ($course) {
                        $query->where('courses.id', $course->id);
                    })
                    ->whereHas('roles', function ($query) {
                        $query->where('slug', 'student');
                    })
                    ->whereNotNull('moodle_user_id')
                    ->get();

                    $courseResult['students_count'] = $students ? $students->count() : 0;

                    // Для каждого студента получаем посты на форумах
                    $totalUnansweredPosts = 0;
                    foreach ($students as $student) {
                        $studentResult = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_email' => $student->email,
                            'moodle_user_id' => $student->moodle_user_id,
                            'forum_posts' => [],
                            'unanswered_posts' => []
                        ];

                        // Получаем посты студента на форумах
                        $forumPosts = $moodleApi->getStudentForumPosts(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $forums
                        );

                        if ($forumPosts !== false && !empty($forumPosts)) {
                            $studentResult['forum_posts'] = $forumPosts;

                            // Подсчитываем посты и неотвеченные посты
                            $totalPosts = 0;
                            $unansweredPosts = 0;

                            foreach ($forumPosts as $forumId => $posts) {
                                foreach ($posts as $post) {
                                    $totalPosts++;
                                    if (isset($post['needs_response']) && $post['needs_response']) {
                                        $unansweredPosts++;
                                        $studentResult['unanswered_posts'][] = [
                                            'forum_id' => $forumId,
                                            'post_id' => $post['id'] ?? null,
                                            'subject' => $post['subject'] ?? 'Без темы',
                                            'message' => isset($post['message']) ? strip_tags(substr($post['message'], 0, 200)) : '',
                                            'timecreated' => $post['timecreated'] ?? null,
                                            'has_teacher_reply' => $post['has_teacher_reply'] ?? false
                                        ];
                                    }
                                }
                            }

                            $studentResult['total_posts'] = $totalPosts;
                            $studentResult['unanswered_posts_count'] = $unansweredPosts;
                            $totalUnansweredPosts += $unansweredPosts;
                        }

                        $courseResult['students'][] = $studentResult;
                    }

                    $courseResult['total_unanswered_posts'] = $totalUnansweredPosts;

                    // Получаем полный ответ API для логирования
                    $apiResponse = $moodleApi->call('mod_forum_get_forums_by_courses', [
                        'courseids' => [$course->moodle_course_id]
                    ]);

                    $courseResult['api_response'] = $apiResponse;
                    $results[] = $courseResult;
                } else {
                    // Проверка заданий (старый код)
                    $courseResult = [
                        'course_id' => $course->id,
                        'course_name' => $course->name,
                        'moodle_course_id' => $course->moodle_course_id,
                        'api_request' => [
                            'function' => 'mod_assign_get_assignments',
                            'url' => config('services.moodle.url') . '/webservice/rest/server.php',
                            'params' => [
                                'courseids' => [$course->moodle_course_id],
                                'wstoken' => '***скрыто***',
                                'wsfunction' => 'mod_assign_get_assignments',
                                'moodlewsrestformat' => 'json'
                            ]
                        ],
                        'assignments' => [],
                        'submissions' => [],
                        'students' => []
                    ];

                    // Получаем задания курса
                    $assignments = $moodleApi->getCourseAssignments($course->moodle_course_id);

                    if ($assignments === false) {
                        // Получаем полный ответ API для детального анализа ошибки
                        $apiResponse = $moodleApi->call('mod_assign_get_assignments', [
                            'courseids' => [$course->moodle_course_id]
                        ]);

                        $courseResult['error'] = 'Не удалось получить задания из Moodle API';
                        $courseResult['api_response'] = $apiResponse;

                        // Проверяем наличие предупреждений о правах доступа
                        if (isset($apiResponse['warnings']) && is_array($apiResponse['warnings'])) {
                            foreach ($apiResponse['warnings'] as $warning) {
                                if (isset($warning['warningcode']) && $warning['warningcode'] == '2') {
                                    $courseResult['permission_error'] = true;
                                    $courseResult['error_details'] = [
                                        'message' => $warning['message'] ?? 'Нет прав доступа',
                                        'item' => $warning['item'] ?? 'course',
                                        'itemid' => $warning['itemid'] ?? $course->moodle_course_id,
                                        'solution' => 'Пользователь токена должен быть зачислен на курс и иметь права mod/assign:view. ' .
                                                     'См. файл MOODLE_TOKEN_PERMISSIONS_FIX.md для инструкций по исправлению.'
                                    ];
                                }
                            }
                        }

                        $results[] = $courseResult;
                        continue;
                    }

                    $courseResult['assignments'] = $assignments;
                    $courseResult['assignments_count'] = is_array($assignments) ? count($assignments) : 0;

                    // Получаем студентов курса
                    $students = \App\Models\User::whereHas('courses', function ($query) use ($course) {
                        $query->where('courses.id', $course->id);
                    })
                    ->whereHas('roles', function ($query) {
                        $query->where('slug', 'student');
                    })
                    ->whereNotNull('moodle_user_id')
                    ->get();

                    $courseResult['students_count'] = $students ? $students->count() : 0;

                    // Для каждого студента получаем сдачи
                    foreach ($students as $student) {
                        $studentResult = [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'student_email' => $student->email,
                            'moodle_user_id' => $student->moodle_user_id,
                            'submissions' => []
                        ];

                        // Получаем сдачи студента
                        $submissions = $moodleApi->getStudentSubmissions(
                            $course->moodle_course_id,
                            $student->moodle_user_id,
                            $assignments
                        );

                        if ($submissions !== false) {
                            $studentResult['submissions'] = $submissions;
                            $studentResult['submissions_count'] = count($submissions);

                            // Получаем оценки студента
                            $grades = $moodleApi->getStudentGrades(
                                $course->moodle_course_id,
                                $student->moodle_user_id,
                                $assignments
                            );

                            if ($grades !== false) {
                                $studentResult['grades'] = $grades;
                                $studentResult['grades_count'] = count($grades);
                            }
                        }

                        $courseResult['students'][] = $studentResult;
                    }

                    // Получаем полный ответ API для логирования
                    $apiResponse = $moodleApi->call('mod_assign_get_assignments', [
                        'courseids' => [$course->moodle_course_id]
                    ]);

                    $courseResult['api_response'] = $apiResponse;

                    $results[] = $courseResult;
                }
            }

            // Безопасно вычисляем суммы с проверкой на существование ключей
            // Для типа 'all' подсчет делается отдельно в summary
            if ($checkType !== 'all') {
                $totalItems = 0;
                $totalStudents = 0;
                foreach ($results as $result) {
                    if ($checkType === 'quizzes') {
                        $totalItems += $result['quizzes_count'] ?? 0;
                    } elseif ($checkType === 'forums') {
                        $totalItems += $result['forums_count'] ?? 0;
                    } else {
                        $totalItems += $result['assignments_count'] ?? 0;
                    }
                    $totalStudents += $result['students_count'] ?? 0;
                }

                $coursesWithItems = count(array_filter($results, function($r) use ($checkType) {
                    if ($checkType === 'quizzes') {
                        return isset($r['quizzes_count']) && $r['quizzes_count'] > 0;
                    } elseif ($checkType === 'forums') {
                        return isset($r['forums_count']) && $r['forums_count'] > 0;
                    } else {
                        return isset($r['assignments_count']) && $r['assignments_count'] > 0;
                    }
                }));
            } else {
                // Для типа 'all' подсчитываем студентов
                $totalStudents = 0;
                foreach ($results as $result) {
                    $totalStudents += $result['students_count'] ?? 0;
                }
            }

            $summary = [
                'total_students' => $totalStudents,
            ];

            if ($checkType === 'all') {
                // Для всех типов показываем полную статистику
                $totalAssignments = 0;
                $totalQuizzes = 0;
                $totalForums = 0;
                $totalUnanswered = 0;
                $coursesWithAssignments = 0;
                $coursesWithQuizzes = 0;
                $coursesWithForums = 0;

                foreach ($results as $result) {
                    $totalAssignments += $result['assignments_count'] ?? 0;
                    $totalQuizzes += $result['quizzes_count'] ?? 0;
                    $totalForums += $result['forums_count'] ?? 0;
                    $totalUnanswered += $result['total_unanswered_posts'] ?? 0;

                    if (isset($result['assignments_count']) && $result['assignments_count'] > 0) {
                        $coursesWithAssignments++;
                    }
                    if (isset($result['quizzes_count']) && $result['quizzes_count'] > 0) {
                        $coursesWithQuizzes++;
                    }
                    if (isset($result['forums_count']) && $result['forums_count'] > 0) {
                        $coursesWithForums++;
                    }
                }

                $summary['total_assignments'] = $totalAssignments;
                $summary['total_quizzes'] = $totalQuizzes;
                $summary['total_forums'] = $totalForums;
                $summary['total_unanswered_posts'] = $totalUnanswered;
                $summary['courses_with_assignments'] = $coursesWithAssignments;
                $summary['courses_with_quizzes'] = $coursesWithQuizzes;
                $summary['courses_with_forums'] = $coursesWithForums;
            } elseif ($checkType === 'quizzes') {
                $summary['total_quizzes'] = $totalItems;
                $summary['courses_with_quizzes'] = $coursesWithItems;
            } elseif ($checkType === 'forums') {
                $summary['total_forums'] = $totalItems;
                $summary['courses_with_forums'] = $coursesWithItems;
                // Подсчитываем общее количество неотвеченных постов
                $totalUnanswered = 0;
                foreach ($results as $result) {
                    $totalUnanswered += $result['total_unanswered_posts'] ?? 0;
                }
                $summary['total_unanswered_posts'] = $totalUnanswered;
            } else {
                $summary['total_assignments'] = $totalItems;
                $summary['courses_with_assignments'] = $coursesWithItems;
            }

            return response()->json([
                'success' => true,
                'check_type' => $checkType,
                'total_courses' => $courses->count(),
                'courses' => $results,
                'summary' => $summary
            ])->header('Content-Type', 'application/json; charset=utf-8');
        } catch (\Exception $e) {
            Log::error('Ошибка проверки заданий из Moodle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка проверки: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500)->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * Извлечь текст сообщения форума из данных прогресса
     *
     * @param StudentActivityProgress $progress
     * @return string
     */
    private function extractForumMessage(StudentActivityProgress $progress): string
    {
        // Пытаемся извлечь из progress_data
        if ($progress->progress_data && is_array($progress->progress_data)) {
            // Ищем различные возможные ключи для сообщения
            $possibleKeys = ['message', 'post_message', 'last_message', 'content', 'text', 'subject'];

            foreach ($possibleKeys as $key) {
                if (isset($progress->progress_data[$key]) && !empty($progress->progress_data[$key])) {
                    $message = $progress->progress_data[$key];
                    // Если это массив, пытаемся найти текст внутри
                    if (is_array($message)) {
                        if (isset($message['text'])) {
                            return strip_tags($message['text']);
                        }
                        if (isset($message['message'])) {
                            return strip_tags($message['message']);
                        }
                    }
                    // Если это строка, возвращаем её
                    if (is_string($message)) {
                        return strip_tags($message);
                    }
                }
            }

            // Если есть массив posts, берем последнее сообщение
            if (isset($progress->progress_data['posts']) && is_array($progress->progress_data['posts'])) {
                $posts = $progress->progress_data['posts'];
                if (!empty($posts)) {
                    $lastPost = end($posts);
                    if (isset($lastPost['message']) || isset($lastPost['text'])) {
                        $message = $lastPost['message'] ?? $lastPost['text'];
                        return strip_tags($message);
                    }
                }
            }
        }

        // Пытаемся извлечь из draft_data
        if ($progress->draft_data && is_array($progress->draft_data)) {
            if (isset($progress->draft_data['message']) && !empty($progress->draft_data['message'])) {
                return strip_tags($progress->draft_data['message']);
            }
            if (isset($progress->draft_data['text']) && !empty($progress->draft_data['text'])) {
                return strip_tags($progress->draft_data['text']);
            }
        }

        // Если ничего не найдено, возвращаем заглушку
        return 'Текст сообщения недоступен';
    }
}
