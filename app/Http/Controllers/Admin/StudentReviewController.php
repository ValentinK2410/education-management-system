<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentActivityProgress;
use Illuminate\Http\Request;

class StudentReviewController extends Controller
{
    /**
     * Показать страницу проверки студентов
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $instructor = auth()->user();

        // Проверяем, что пользователь является преподавателем
        if (!$instructor->hasRole('instructor')) {
            abort(403, 'Доступ разрешен только преподавателям');
        }

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

                return $progress;
            })
            ->sortByDesc(function ($progress) {
                // Сортируем: сначала отвеченные, потом неотвеченные
                return $progress->status === 'answered' ? 1 : 0;
            })
            ->values();

        // Вкладка "Форумы" - форумы, ожидающие ответа преподавателя
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
