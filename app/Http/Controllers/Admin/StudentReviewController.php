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
                    $progress->status = 'needs_grading';
                    $progress->status_text = 'Ожидает проверки';
                    $progress->status_class = 'warning';
                } elseif ($progress->submitted_at) {
                    $progress->status = 'submitted';
                    $progress->status_text = 'Сдано';
                    $progress->status_class = 'info';
                } elseif ($progress->has_draft) {
                    $progress->status = 'draft';
                    $progress->status_text = 'Есть черновик';
                    $progress->status_class = 'secondary';
                } else {
                    $progress->status = 'unknown';
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
                $hasAnswered = $progress->submitted_at !== null || $progress->attempts_count > 0;

                if ($hasAnswered) {
                    $progress->status = 'answered';
                    $progress->status_text = 'Ответил';
                    $progress->status_class = 'success';
                } else {
                    $progress->status = 'not_answered';
                    $progress->status_text = 'Не ответил';
                    $progress->status_class = 'warning';
                }

                return $progress;
            })
            ->sortByDesc(function ($progress) {
                // Сортируем: сначала отвеченные, потом неотвеченные
                return in_array($progress->status, ['answered', 'graded', 'submitted']) ? 1 : 0;
            })
            ->values();

        // Вкладка "Форумы" - форумы, ожидающие ответа преподавателя
        // Получаем данные из базы (быстро, без синхронизации с Moodle)
        $forums = StudentActivityProgress::whereIn('course_id', $courseIds)
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
            ->where('needs_response', true)
            ->whereNotNull('submitted_at')
            ->with(['user.roles', 'course', 'activity.course'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($progress) use ($coursesById) {
                // Присваиваем курс к активности для корректной работы moodle_url
                if ($progress->activity && isset($coursesById[$progress->course_id])) {
                    $progress->activity->setRelation('course', $coursesById[$progress->course_id]);
                }

                // Извлекаем текст сообщения из progress_data или draft_data
                $progress->message_text = $this->extractForumMessage($progress);
                return $progress;
            });

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
            $moodleApi = new MoodleApiService();
            $tab = $request->get('tab', 'quizzes'); // quizzes, forums
            
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

            if ($tab === 'quizzes') {
                // Синхронизация тестов
                $moodleQuizzes = $moodleApi->getCourseQuizzes($course->moodle_course_id);
                
                if ($moodleQuizzes === false) {
                    return response()->json(['error' => 'Не удалось получить тесты из Moodle'], 500);
                }

                foreach ($students as $student) {
                    try {
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

                        // Обновляем данные в базе
                        foreach ($moodleQuizzes as $moodleQuiz) {
                            $activity = \App\Models\CourseActivity::where('course_id', $courseId)
                                ->where('moodle_activity_id', $moodleQuiz['id'])
                                ->where('activity_type', 'quiz')
                                ->first();

                            if (!$activity) {
                                continue;
                            }

                            $attempts = $quizAttempts[$moodleQuiz['id']] ?? [];
                            $grade = $quizGrades[$moodleQuiz['id'] ?? null] ?? null;

                            $progress = StudentActivityProgress::updateOrCreate(
                                [
                                    'user_id' => $student->id,
                                    'course_id' => $courseId,
                                    'activity_id' => $activity->id,
                                ],
                                [
                                    'attempts_count' => count($attempts),
                                    'last_attempt_at' => !empty($attempts) ? date('Y-m-d H:i:s', end($attempts)['timestart'] ?? time()) : null,
                                    'submitted_at' => !empty($attempts) && (end($attempts)['state'] ?? '') === 'finished' 
                                        ? date('Y-m-d H:i:s', end($attempts)['timefinish'] ?? time()) 
                                        : null,
                                    'grade' => $grade && isset($grade['grade']) ? (float)$grade['grade'] : null,
                                    'max_grade' => $moodleQuiz['grade'] ?? null,
                                ]
                            );
                            
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

                            foreach ($posts as $post) {
                                if (isset($post['needs_response']) && $post['needs_response']) {
                                    $needsResponse = true;
                                }
                                $postTime = $post['timecreated'] ?? 0;
                                if ($postTime > $latestPostTime) {
                                    $latestPostTime = $postTime;
                                }
                            }

                            StudentActivityProgress::updateOrCreate(
                                [
                                    'user_id' => $student->id,
                                    'course_id' => $courseId,
                                    'activity_id' => $activity->id,
                                ],
                                [
                                    'needs_response' => $needsResponse,
                                    'submitted_at' => $latestPostTime ? date('Y-m-d H:i:s', $latestPostTime) : null,
                                    'progress_data' => [
                                        'posts' => $posts,
                                        'posts_count' => count($posts),
                                    ],
                                ]
                            );
                            
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
     * Проверить данные о заданиях напрямую из Moodle API
     * Возвращает детальную информацию о запросах и ответах
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMoodleAssignments()
    {
        $user = auth()->user();
        
        // Проверяем права доступа
        if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
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

        $results = [];
        
        try {
            $moodleApi = new MoodleApiService();
            
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
                    $courseResult['error'] = 'Не удалось получить задания из Moodle API';
                    $results[] = $courseResult;
                    continue;
                }

                $courseResult['assignments'] = $assignments;
                $courseResult['assignments_count'] = count($assignments);

                // Получаем студентов курса
                $students = \App\Models\User::whereHas('courses', function ($query) use ($course) {
                    $query->where('courses.id', $course->id);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('slug', 'student');
                })
                ->whereNotNull('moodle_user_id')
                ->get();

                $courseResult['students_count'] = $students->count();

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

            return response()->json([
                'success' => true,
                'total_courses' => $courses->count(),
                'courses' => $results,
                'summary' => [
                    'total_assignments' => array_sum(array_column($results, 'assignments_count')),
                    'total_students' => array_sum(array_column($results, 'students_count')),
                    'courses_with_assignments' => count(array_filter($results, function($r) {
                        return isset($r['assignments_count']) && $r['assignments_count'] > 0;
                    }))
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка проверки заданий из Moodle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Ошибка проверки: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
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
