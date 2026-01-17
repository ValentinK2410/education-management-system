<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\StudentActivityProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorStatsController extends Controller
{
    /**
     * Показать список всех преподавателей со статистикой
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Получаем всех пользователей с ролью преподавателя
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })
        ->with(['taughtCourses' => function ($query) {
            $query->withCount(['users' => function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('slug', 'student');
                });
            }]);
        }])
        ->get()
        ->map(function ($instructor) {
            // Подсчитываем статистику для каждого преподавателя
            $courses = $instructor->taughtCourses;
            $totalCourses = $courses->count();

            // Общее количество студентов (сумма по всем курсам, с возможными дубликатами)
            $totalStudentsAll = $courses->sum('users_count');

            // Получаем уникальных студентов по всем курсам преподавателя
            $courseIds = $courses->pluck('id');
            $uniqueStudents = 0;
            if ($courseIds->isNotEmpty()) {
                $uniqueStudents = \DB::table('user_courses')
                    ->whereIn('course_id', $courseIds)
                    ->join('users', 'user_courses.user_id', '=', 'users.id')
                    ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->where('roles.slug', 'student')
                    ->distinct('user_courses.user_id')
                    ->count('user_courses.user_id');
            }

            // Подсчитываем проверенные работы
            // Если graded_by_user_id заполнен, проверяем его
            // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
            $gradedActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', 'graded')
            ->where(function ($query) use ($instructor) {
                $query->where('graded_by_user_id', $instructor->id)
                      ->orWhereNull('graded_by_user_id');
            })
            ->count();

            // Подсчитываем непроверенные работы
            $pendingActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', 'submitted')
            ->count();

            return [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'email' => $instructor->email,
                'photo' => $instructor->photo,
                'total_courses' => $totalCourses,
                'total_students_all' => $totalStudentsAll,
                'unique_students' => $uniqueStudents,
                'graded_activities' => $gradedActivities,
                'pending_activities' => $pendingActivities,
            ];
        });

        return view('admin.instructor-stats.index', compact('instructors'));
    }

    /**
     * Показать детальную статистику конкретного преподавателя
     *
     * @param User $instructor
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function show(User $instructor, Request $request)
    {
        try {
            // Проверяем, что пользователь является преподавателем
            if (!$instructor->hasRole('instructor')) {
                abort(404, 'Пользователь не является преподавателем');
            }

            // Получаем фильтры по дате
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            // Получаем все курсы преподавателя с количеством студентов и активными студентами
            $courses = Course::where('instructor_id', $instructor->id)
                ->with(['program', 'instructor'])
                ->withCount(['users' => function ($query) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('slug', 'student');
                    });
                }])
                ->get();

        // Получаем все ID курсов для оптимизации запросов
        $courseIds = $courses->pluck('id');

        // Загружаем все курсы в память для быстрого доступа
        $coursesById = $courses->keyBy('id');

        // Получаем все элементы курса для всех курсов одним запросом
        $allActivities = \App\Models\CourseActivity::whereIn('course_id', $courseIds)
            ->orderBy('section_number')
            ->orderBy('section_order')
            ->get();

        // Присваиваем курсы к активностям вручную для избежания дополнительных запросов
        foreach ($allActivities as $activity) {
            if (isset($coursesById[$activity->course_id])) {
                $activity->setRelation('course', $coursesById[$activity->course_id]);
            }
        }

        // Группируем элементы по курсам
        $coursesWithActivities = [];
        foreach ($allActivities as $activity) {
            $coursesWithActivities[$activity->course_id][] = $activity;
        }
        // Преобразуем массивы в коллекции
        foreach ($coursesWithActivities as $courseId => $activities) {
            $coursesWithActivities[$courseId] = collect($activities);
        }

        // Получаем данные о студентах и их активности по каждому курсу
        $coursesWithStudents = [];

        // Получаем всех студентов всех курсов одним запросом с ролями
        // Ограничиваем количество для предотвращения исчерпания памяти
        $allStudents = \App\Models\User::whereHas('courses', function ($query) use ($courseIds) {
                $query->whereIn('courses.id', $courseIds);
            })
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'student');
            })
            ->with('roles')
            ->limit(150) // Ограничиваем общее количество студентов
            ->get();

        // Получаем все прогрессы студентов одним запросом
        // Не загружаем связи activity и user для экономии памяти - они не нужны в этом контексте
        // Ограничиваем количество прогрессов для предотвращения исчерпания памяти
        $studentIds = $allStudents->pluck('id');
        if ($studentIds->isNotEmpty()) {
            $allProgresses = \App\Models\StudentActivityProgress::whereIn('course_id', $courseIds)
                ->whereIn('user_id', $studentIds)
                ->limit(5000) // Ограничиваем общее количество прогрессов
                ->get();
        } else {
            $allProgresses = collect();
        }

        // Группируем прогрессы по course_id -> user_id -> activity_id
        $progressesByCourseAndUser = [];
        foreach ($allProgresses as $progress) {
            $progressesByCourseAndUser[$progress->course_id][$progress->user_id][$progress->activity_id] = $progress;
        }

        // Получаем связи студентов с курсами
        $userCourseRelations = \DB::table('user_courses')
            ->whereIn('course_id', $courseIds)
            ->whereIn('user_id', $allStudents->pluck('id'))
            ->get()
            ->groupBy('course_id');

        // Ограничиваем общее количество обрабатываемых студентов для предотвращения исчерпания памяти
        // Максимум 100 студентов на всех курсах
        $maxStudentsToProcess = 100;
        $totalStudentsProcessed = 0;

        foreach ($courses as $course) {
            // Получаем студентов этого курса
            $courseUserIds = $userCourseRelations->get($course->id, collect())->pluck('user_id');
            $students = $allStudents->whereIn('id', $courseUserIds);

            // Если уже обработали максимальное количество студентов, пропускаем остальные курсы
            if ($totalStudentsProcessed >= $maxStudentsToProcess) {
                break;
            }

            $studentsWithActivity = [];

            foreach ($students as $student) {
                // Если достигли лимита, прекращаем обработку
                if ($totalStudentsProcessed >= $maxStudentsToProcess) {
                    break;
                }
                // Проверяем наличие настроек для синхронизации
                $canSync = !empty($course->moodle_course_id) && !empty($student->moodle_user_id);

                // Получаем все элементы курса из уже загруженных данных
                $activities = $coursesWithActivities[$course->id] ?? collect();

                // Получаем прогрессы студента по этому курсу из уже загруженных данных
                $studentProgresses = $progressesByCourseAndUser[$course->id][$student->id] ?? [];

                $studentActivities = [];

                foreach ($activities as $activity) {
                    // Получаем прогресс студента по этому элементу из уже загруженных данных
                    $progress = $studentProgresses[$activity->id] ?? null;

                    // Показываем только элементы, с которыми студент взаимодействовал
                    if ($progress && ($progress->is_viewed || $progress->is_read || $progress->started_at ||
                        $progress->submitted_at || $progress->is_graded || $progress->has_draft)) {

                        // Определяем статус (приоритет: проверено > сдано > ожидает проверки/ответа > в процессе > прочитано > просмотрено)
                        $status = 'viewed';
                        $statusText = 'Просмотрено';
                        $statusIcon = 'fa-eye';
                        $statusClass = 'secondary';

                        // Проверяем, является ли это форумом
                        $isForum = $activity->activity_type === 'forum';
                        $needsResponse = $progress->needs_response ?? false;

                        // Приоритет 1: Проверено (есть оценка)
                        if ($progress->grade !== null || ($progress->is_graded && $progress->graded_at)) {
                            $status = 'graded';
                            $statusText = 'Проверено';
                            $statusIcon = 'fa-check-circle';
                            $statusClass = 'success';
                        }
                        // Приоритет 2: Сдано (есть дата сдачи)
                        elseif ($progress->submitted_at) {
                            $status = 'submitted';
                            $statusText = 'Сдано';
                            $statusIcon = 'fa-paper-plane';
                            $statusClass = 'info';
                        }
                        // Приоритет 3: Ожидает проверки или ответа преподавателя (для форумов)
                        elseif ($progress->needs_grading || ($progress->submitted_at && !$progress->is_graded)) {
                            if ($isForum && $needsResponse) {
                                $status = 'needs_response';
                                $statusText = 'Ожидает ответа преподавателя';
                                $statusIcon = 'fa-comments';
                                $statusClass = 'warning';
                            } else {
                                $status = 'needs_grading';
                                $statusText = 'Ожидает проверки';
                                $statusIcon = 'fa-clock';
                                $statusClass = 'warning';
                            }
                        }
                        // Приоритет 4: В процессе (есть черновик или начато)
                        elseif ($progress->has_draft || $progress->started_at) {
                            $status = 'in_progress';
                            $statusText = 'В процессе';
                            $statusIcon = 'fa-edit';
                            $statusClass = 'primary';
                        }
                        // Приоритет 5: Прочитано
                        elseif ($progress->is_read) {
                            $status = 'read';
                            $statusText = 'Прочитано';
                            $statusIcon = 'fa-book-open';
                            $statusClass = 'info';
                        }

                        // Форматируем даты заранее для экономии памяти
                        $submittedAtFormatted = null;
                        if ($progress->submitted_at) {
                            try {
                                $submittedAtFormatted = $progress->submitted_at instanceof \Carbon\Carbon
                                    ? $progress->submitted_at->format('d.m.Y H:i')
                                    : \Carbon\Carbon::parse($progress->submitted_at)->format('d.m.Y H:i');
                            } catch (\Exception $e) {
                                $submittedAtFormatted = null;
                            }
                        }

                        $gradedAtFormatted = null;
                        if ($progress->graded_at) {
                            try {
                                $gradedAtFormatted = $progress->graded_at instanceof \Carbon\Carbon
                                    ? $progress->graded_at->format('d.m.Y H:i')
                                    : \Carbon\Carbon::parse($progress->graded_at)->format('d.m.Y H:i');
                            } catch (\Exception $e) {
                                $gradedAtFormatted = null;
                            }
                        }

                        $studentActivities[] = [
                            'activity' => $activity,
                            'progress' => $progress,
                            'status' => $status,
                            'status_text' => $statusText,
                            'status_icon' => $statusIcon,
                            'status_class' => $statusClass,
                            'grade' => $progress->grade,
                            'max_grade' => $progress->max_grade ?? $activity->max_grade,
                            'submitted_at' => $progress->submitted_at,
                            'submitted_at_formatted' => $submittedAtFormatted,
                            'graded_at' => $progress->graded_at,
                            'graded_at_formatted' => $gradedAtFormatted,
                            'is_forum' => $isForum,
                            'needs_response' => $needsResponse,
                        ];
                    }
                }

                // Ограничиваем количество активностей для предотвращения исчерпания памяти
                // Показываем только последние 20 активностей на студента (уменьшено с 50)
                $limitedActivities = array_slice($studentActivities, 0, 20);

                // Добавляем всех студентов, даже если у них нет активности
                $studentsWithActivity[] = [
                    'student' => $student,
                    'activities' => $limitedActivities,
                    'total_activities' => count($studentActivities),
                    'displayed_activities' => count($limitedActivities),
                    'graded_count' => count(array_filter($studentActivities, fn($a) => $a['status'] === 'graded')),
                    'submitted_count' => count(array_filter($studentActivities, fn($a) => $a['status'] === 'submitted')),
                    'pending_count' => count(array_filter($studentActivities, fn($a) => $a['status'] === 'needs_grading' || $a['status'] === 'needs_response')),
                    'has_activity' => !empty($studentActivities),
                    'can_sync' => $canSync,
                    'missing_moodle_course_id' => empty($course->moodle_course_id),
                    'missing_moodle_user_id' => empty($student->moodle_user_id),
                ];

                $totalStudentsProcessed++;
            }

            $coursesWithStudents[$course->id] = $studentsWithActivity;
        }

        // Добавляем информацию о лимитах для отображения в представлении
        $hasMoreStudents = $totalStudentsProcessed >= $maxStudentsToProcess;

        // Получаем все проверенные работы преподавателя
        // Если graded_by_user_id заполнен, проверяем его
        // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
        $gradedActivitiesQuery = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'graded')
        ->where(function ($query) use ($instructor) {
            $query->where('graded_by_user_id', $instructor->id)
                  ->orWhereNull('graded_by_user_id');
        });

        // Применяем фильтр по дате проверки
        if ($dateFrom) {
            $gradedActivitiesQuery->whereDate('graded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $gradedActivitiesQuery->whereDate('graded_at', '<=', $dateTo);
        }

        $gradedActivities = $gradedActivitiesQuery
            ->with(['user', 'course', 'activity', 'gradedBy'])
            ->orderBy('graded_at', 'desc')
            ->get();

        // Получаем непроверенные работы (включая форумы, ожидающие ответа преподавателя)
        $pendingActivities = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where(function ($query) {
            $query->where('status', 'submitted')
                  ->orWhere('needs_grading', true)
                  ->orWhere('needs_response', true); // Включаем форумы, ожидающие ответа
        })
        ->whereHas('activity', function ($query) {
            // Включаем задания, тесты и форумы
            $query->whereIn('activity_type', ['assign', 'quiz', 'forum']);
        })
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();

        // Статистика по типам активностей
        // Если graded_by_user_id заполнен, проверяем его
        // Если нет, но курс принадлежит преподавателю и статус 'graded', считаем проверенным преподавателем
        $activityStatsQuery = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->where('status', 'graded')
        ->where(function ($query) use ($instructor) {
            $query->where('graded_by_user_id', $instructor->id)
                  ->orWhereNull('graded_by_user_id');
        });

        // Применяем фильтр по дате проверки
        if ($dateFrom) {
            $activityStatsQuery->whereDate('graded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $activityStatsQuery->whereDate('graded_at', '<=', $dateTo);
        }

        $activityStats = $activityStatsQuery
            ->join('course_activities', 'student_activity_progress.activity_id', '=', 'course_activities.id')
            ->select('course_activities.activity_type', DB::raw('COUNT(*) as count'))
            ->groupBy('course_activities.activity_type')
            ->get()
            ->pluck('count', 'activity_type');

        // Общее количество студентов (сумма по всем курсам)
        $totalStudentsAll = $courses->sum('users_count');

        // Получаем уникальных студентов по всем курсам преподавателя
        $courseIds = $courses->pluck('id');
        $uniqueStudentsCount = 0;
        if ($courseIds->isNotEmpty()) {
            $uniqueStudentsCount = \DB::table('user_courses')
                ->whereIn('course_id', $courseIds)
                ->join('users', 'user_courses.user_id', '=', 'users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.slug', 'student')
                ->distinct('user_courses.user_id')
                ->count('user_courses.user_id');
        }

        // Получаем важные уведомления для преподавателя
        // 1. Сданные задания (ожидают проверки)
        $submittedAssignments = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereHas('activity', function ($query) {
            $query->where('activity_type', 'assign');
        })
        ->where('submitted_at', '!=', null)
        ->where(function ($query) {
            $query->where('status', 'submitted')
                  ->orWhere('needs_grading', true)
                  ->orWhere(function ($q) {
                      $q->where('submitted_at', '!=', null)
                        ->where(function ($subQ) {
                            $subQ->where('is_graded', false)
                                 ->orWhereNull('is_graded');
                        });
                  });
        })
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();

        // 2. Форумы, ожидающие ответа преподавателя
        $forumsNeedingResponse = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereHas('activity', function ($query) {
            $query->where('activity_type', 'forum');
        })
        ->where('needs_response', true)
        ->where('submitted_at', '!=', null)
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();

        // 3. Работы, ожидающие оценки (задания и тесты)
        $activitiesNeedingGrading = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereHas('activity', function ($query) {
            $query->whereIn('activity_type', ['assign', 'quiz']);
        })
        ->where(function ($query) {
            $query->where('needs_grading', true)
                  ->orWhere(function ($q) {
                      $q->where('status', 'submitted')
                        ->where(function ($subQ) {
                            $subQ->where('is_graded', false)
                                 ->orWhereNull('is_graded');
                        });
                  });
        })
        ->where('submitted_at', '!=', null)
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();

        // 4. Сданные тесты и экзамены (показываем все сданные, но особо выделяем непроверенные)
        $submittedQuizzes = StudentActivityProgress::whereHas('course', function ($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereHas('activity', function ($query) {
            $query->where('activity_type', 'quiz');
        })
        ->where('submitted_at', '!=', null)
        ->with(['user', 'course', 'activity'])
        ->orderBy('submitted_at', 'desc')
        ->get();

        // Общая статистика
        $stats = [
            'total_courses' => $courses->count(),
            'total_students_all' => $totalStudentsAll,
            'unique_students' => $uniqueStudentsCount,
            'total_graded' => $gradedActivities->count(),
            'total_pending' => $pendingActivities->count(),
            'assignments_graded' => $activityStats->get('assign', 0),
            'quizzes_graded' => $activityStats->get('quiz', 0),
            'forums_graded' => $activityStats->get('forum', 0),
        ];

            return view('admin.instructor-stats.show', compact(
                'hasMoreStudents',
                'maxStudentsToProcess',
                'totalStudentsProcessed',
                'instructor',
                'courses',
                'coursesWithStudents',
                'coursesWithActivities',
                'gradedActivities',
                'pendingActivities',
                'stats',
                'dateFrom',
                'dateTo',
                'submittedAssignments',
                'forumsNeedingResponse',
                'activitiesNeedingGrading',
                'submittedQuizzes'
            ));
        } catch (\Exception $e) {
            \Log::error('Ошибка в InstructorStatsController::show', [
                'instructor_id' => $instructor->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Возвращаем ошибку пользователю
            if (config('app.debug')) {
                // В режиме отладки показываем детали ошибки
                return response()->json([
                    'error' => 'Ошибка при загрузке статистики преподавателя',
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            } else {
                // В продакшене показываем общее сообщение
                abort(500, 'Произошла ошибка при загрузке статистики преподавателя. Пожалуйста, попробуйте позже или обратитесь к администратору.');
            }
        }
    }
}

