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
        // Получаем данные из базы
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

                return $progress;
            });

        // Получаем актуальные данные из Moodle API для каждого теста
        try {
            $moodleApi = new MoodleApiService();
            
            // Группируем тесты по курсу и студенту для оптимизации запросов
            $quizzesByCourseAndStudent = [];
            foreach ($quizzes as $quiz) {
                $courseId = $quiz->course_id;
                $studentId = $quiz->user_id;
                $key = "{$courseId}_{$studentId}";
                
                if (!isset($quizzesByCourseAndStudent[$key])) {
                    $quizzesByCourseAndStudent[$key] = [
                        'course' => $quiz->course,
                        'student' => $quiz->user,
                        'quizzes' => []
                    ];
                }
                $quizzesByCourseAndStudent[$key]['quizzes'][] = $quiz;
            }

            // Получаем актуальные данные из Moodle для каждой группы
            foreach ($quizzesByCourseAndStudent as $key => $group) {
                $course = $group['course'];
                $student = $group['student'];
                
                if (!$course->moodle_course_id || !$student->moodle_user_id) {
                    continue;
                }

                try {
                    // Получаем тесты курса
                    $moodleQuizzes = $moodleApi->getCourseQuizzes($course->moodle_course_id);
                    if ($moodleQuizzes === false) {
                        continue;
                    }

                    // Получаем попытки студента
                    $quizAttempts = $moodleApi->getStudentQuizAttempts(
                        $course->moodle_course_id,
                        $student->moodle_user_id,
                        $moodleQuizzes
                    );

                    // Получаем оценки студента
                    $quizGrades = $moodleApi->getStudentQuizGrades(
                        $course->moodle_course_id,
                        $student->moodle_user_id,
                        $moodleQuizzes
                    );

                    // Обновляем данные для каждого теста в группе
                    foreach ($group['quizzes'] as $quiz) {
                        $activity = $quiz->activity;
                        if (!$activity || !$activity->moodle_activity_id) {
                            continue;
                        }

                        // Находим соответствующий тест в Moodle
                        $moodleQuiz = null;
                        foreach ($moodleQuizzes as $mq) {
                            if ($mq['id'] == $activity->moodle_activity_id) {
                                $moodleQuiz = $mq;
                                break;
                            }
                        }

                        if (!$moodleQuiz) {
                            continue;
                        }

                        $quizId = $moodleQuiz['id'];
                        $attempts = $quizAttempts[$quizId] ?? [];
                        $grade = $quizGrades[$quizId] ?? null;

                        // Обновляем статус на основе актуальных данных
                        if (!empty($attempts)) {
                            $latestAttempt = end($attempts);
                            $attemptStatus = $latestAttempt['state'] ?? '';
                            
                            if ($attemptStatus === 'finished') {
                                // Тест завершен
                                if ($grade && isset($grade['grade']) && $grade['grade'] !== null) {
                                    // Есть оценка
                                    $quiz->status = 'graded';
                                    $quiz->status_text = 'Оценен';
                                    $quiz->status_class = 'success';
                                    $quiz->grade = (float)$grade['grade'];
                                    $quiz->max_grade = $moodleQuiz['grade'] ?? null;
                                    $quiz->submitted_at = $latestAttempt['timefinish'] ?? null;
                                    $quiz->graded_at = $quiz->submitted_at;
                                } else {
                                    // Сдан, но ждет оценки
                                    $quiz->status = 'submitted';
                                    $quiz->status_text = 'Ждет оценки';
                                    $quiz->status_class = 'warning';
                                    $quiz->submitted_at = $latestAttempt['timefinish'] ?? null;
                                    $quiz->grade = null;
                                    $quiz->max_grade = $moodleQuiz['grade'] ?? null;
                                }
                            } else {
                                // Тест в процессе
                                $quiz->status = 'in_progress';
                                $quiz->status_text = 'В процессе';
                                $quiz->status_class = 'info';
                                $quiz->submitted_at = null;
                            }
                            
                            $quiz->attempts_count = count($attempts);
                            $quiz->last_attempt_at = $latestAttempt['timestart'] ?? null;
                        } else {
                            // Нет попыток - тест не сдан
                            $quiz->status = 'not_answered';
                            $quiz->status_text = 'Не сдан';
                            $quiz->status_class = 'danger';
                            $quiz->attempts_count = 0;
                            $quiz->submitted_at = null;
                            $quiz->last_attempt_at = null;
                            $quiz->grade = null;
                            $quiz->max_grade = $moodleQuiz['grade'] ?? null;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Ошибка при получении данных о тестах из Moodle', [
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при инициализации Moodle API для получения данных о тестах', [
                'error' => $e->getMessage()
            ]);
        }

        // Если данные не были обновлены из Moodle, используем данные из базы
        $quizzes = $quizzes->map(function ($progress) {
            if (!isset($progress->status)) {
                // Определяем статус: ответил или нет
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
            }

            return $progress;
        })
        ->sortByDesc(function ($progress) {
            // Сортируем: сначала отвеченные, потом неотвеченные
            return in_array($progress->status, ['answered', 'graded', 'submitted']) ? 1 : 0;
        })
        ->values();

        // Вкладка "Форумы" - форумы, ожидающие ответа преподавателя
        // Получаем данные из базы
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
            ->with(['user.roles', 'course', 'activity.course'])
            ->get()
            ->map(function ($progress) use ($coursesById) {
                // Присваиваем курс к активности для корректной работы moodle_url
                if ($progress->activity && isset($coursesById[$progress->course_id])) {
                    $progress->activity->setRelation('course', $coursesById[$progress->course_id]);
                }

                return $progress;
            });

        // Получаем актуальные данные из Moodle API для каждого форума
        try {
            $moodleApi = new MoodleApiService();
            
            // Группируем форумы по курсу и студенту для оптимизации запросов
            $forumsByCourseAndStudent = [];
            foreach ($forums as $forum) {
                $courseId = $forum->course_id;
                $studentId = $forum->user_id;
                $key = "{$courseId}_{$studentId}";
                
                if (!isset($forumsByCourseAndStudent[$key])) {
                    $forumsByCourseAndStudent[$key] = [
                        'course' => $forum->course,
                        'student' => $forum->user,
                        'forums' => []
                    ];
                }
                $forumsByCourseAndStudent[$key]['forums'][] = $forum;
            }

            // Получаем актуальные данные из Moodle для каждой группы
            foreach ($forumsByCourseAndStudent as $key => $group) {
                $course = $group['course'];
                $student = $group['student'];
                
                if (!$course->moodle_course_id || !$student->moodle_user_id) {
                    continue;
                }

                try {
                    // Получаем форумы курса
                    $moodleForums = $moodleApi->getCourseForums($course->moodle_course_id);
                    if ($moodleForums === false) {
                        continue;
                    }

                    // Получаем посты студента в форумах
                    $forumPosts = $moodleApi->getStudentForumPosts(
                        $course->moodle_course_id,
                        $student->moodle_user_id,
                        $moodleForums
                    );

                    // Обновляем данные для каждого форума в группе
                    foreach ($group['forums'] as $forum) {
                        $activity = $forum->activity;
                        if (!$activity || !$activity->moodle_activity_id) {
                            continue;
                        }

                        // Находим соответствующий форум в Moodle
                        $moodleForum = null;
                        foreach ($moodleForums as $mf) {
                            if ($mf['id'] == $activity->moodle_activity_id) {
                                $moodleForum = $mf;
                                break;
                            }
                        }

                        if (!$moodleForum) {
                            continue;
                        }

                        $forumId = $moodleForum['id'];
                        $posts = $forumPosts[$forumId] ?? [];

                        // Обновляем статус на основе актуальных данных
                        if (!empty($posts)) {
                            $needsResponse = false;
                            $hasTeacherReply = false;
                            $latestPost = null;
                            $latestPostTime = 0;

                            foreach ($posts as $post) {
                                // Проверяем, нужен ли ответ преподавателя
                                if (isset($post['needs_response']) && $post['needs_response']) {
                                    $needsResponse = true;
                                }

                                // Проверяем, есть ли ответ преподавателя
                                if (isset($post['has_teacher_reply']) && $post['has_teacher_reply']) {
                                    $hasTeacherReply = true;
                                }

                                // Находим последний пост
                                $postTime = $post['timecreated'] ?? 0;
                                if ($postTime > $latestPostTime) {
                                    $latestPostTime = $postTime;
                                    $latestPost = $post;
                                }
                            }

                            // Если студент ответил, но преподаватель не ответил, требуется ответ
                            if ($needsResponse && !$hasTeacherReply) {
                                $forum->needs_response = true;
                                $forum->status = 'needs_response';
                                $forum->status_text = 'Ожидает ответа преподавателя';
                                $forum->status_class = 'warning';
                            } else {
                                $forum->needs_response = false;
                                $forum->status = 'completed';
                                $forum->status_text = 'Ответ получен';
                                $forum->status_class = 'success';
                            }

                            $forum->submitted_at = $latestPostTime ? date('Y-m-d H:i:s', $latestPostTime) : null;
                            
                            // Сохраняем данные постов в progress_data
                            $forum->progress_data = [
                                'posts' => $posts,
                                'posts_count' => count($posts),
                                'needs_response' => $needsResponse,
                                'has_teacher_reply' => $hasTeacherReply
                            ];
                        } else {
                            // Нет постов - форум не начат
                            $forum->needs_response = false;
                            $forum->status = 'not_started';
                            $forum->status_text = 'Не участвовал';
                            $forum->status_class = 'secondary';
                            $forum->submitted_at = null;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Ошибка при получении данных о форумах из Moodle', [
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при инициализации Moodle API для получения данных о форумах', [
                'error' => $e->getMessage()
            ]);
        }

        // Фильтруем только форумы, ожидающие ответа преподавателя
        $forums = $forums->filter(function ($forum) {
            return $forum->needs_response === true && $forum->submitted_at !== null;
        })
        ->map(function ($progress) {
            // Извлекаем текст сообщения из progress_data или draft_data
            $progress->message_text = $this->extractForumMessage($progress);
            return $progress;
        })
        ->sortByDesc(function ($progress) {
            return $progress->submitted_at ? strtotime($progress->submitted_at) : 0;
        })
        ->values();

        return view('admin.student-review.index', compact('assignments', 'quizzes', 'forums'));
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
