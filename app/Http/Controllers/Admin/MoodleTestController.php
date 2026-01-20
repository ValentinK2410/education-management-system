<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoodleTestController extends Controller
{
    /**
     * Показать страницу тестирования Moodle API
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        
        // Проверяем права доступа
        if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
            abort(403, 'Доступ разрешен только преподавателям и администраторам');
        }
        
        // Получаем список студентов для удобного выбора
        $students = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('slug', 'student');
        })
        ->whereNotNull('moodle_user_id')
        ->orderBy('name')
        ->get(['id', 'name', 'email', 'moodle_user_id']);
        
        // Получаем список курсов преподавателя
        $courses = \App\Models\Course::where('instructor_id', $user->id)
            ->whereNotNull('moodle_course_id')
            ->orderBy('name')
            ->get(['id', 'name', 'moodle_course_id']);
        
        return view('admin.moodle-test.index', compact('students', 'courses'));
    }

    /**
     * Выполнить тест Moodle API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        $user = auth()->user();
        
        // Проверяем права доступа
        if (!$user->hasRole('instructor') && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'error' => 'Доступ запрещен'
            ], 403);
        }

        $request->validate([
            'course_id' => 'required|integer|min:1',
            'student_id' => 'nullable|integer|min:1',
            'test_type' => 'required|in:assignments,quizzes,forums,all',
            'assignment_id' => 'nullable|integer|min:1',
            'quiz_id' => 'nullable|integer|min:1',
            'forum_id' => 'nullable|integer|min:1',
        ]);

        $courseIdInput = $request->input('course_id');
        $studentId = $request->input('student_id');
        $testType = $request->input('test_type');

        try {
            // Используем токен текущего пользователя
            $userToken = $user->getMoodleToken();
            $moodleApi = new MoodleApiService(null, $userToken);

            // ВАЖНО: Преобразуем course_id в moodle_course_id
            // course_id может быть либо локальным ID курса, либо moodle_course_id
            $course = null;
            $moodleCourseId = null;
            $courseIdLocal = null;
            
            // Сначала проверяем, является ли это moodle_course_id
            $course = \App\Models\Course::where('moodle_course_id', $courseIdInput)
                ->where('instructor_id', $user->id)
                ->first();
            
            if ($course) {
                // Это moodle_course_id
                $moodleCourseId = $course->moodle_course_id;
                $courseIdLocal = $course->id;
                Log::info('MoodleTest: Используется Moodle Course ID', [
                    'input' => $courseIdInput,
                    'moodle_course_id' => $moodleCourseId,
                    'local_course_id' => $courseIdLocal
                ]);
            } else {
                // Попытка найти по локальному ID
                $course = \App\Models\Course::where('id', $courseIdInput)
                    ->where('instructor_id', $user->id)
                    ->first();
                
                if ($course && $course->moodle_course_id) {
                    // Найден курс по локальному ID, используем его moodle_course_id
                    $moodleCourseId = $course->moodle_course_id;
                    $courseIdLocal = $course->id;
                    Log::info('MoodleTest: Преобразован локальный ID в Moodle Course ID', [
                        'input' => $courseIdInput,
                        'moodle_course_id' => $moodleCourseId,
                        'local_course_id' => $courseIdLocal
                    ]);
                } else {
                    // Курс не найден или нет moodle_course_id
                    return response()->json([
                        'success' => false,
                        'error' => 'Курс не найден или не синхронизирован с Moodle. ' .
                                   'Убедитесь, что вы вводите правильный Moodle Course ID или локальный ID курса, ' .
                                   'который имеет настроенный moodle_course_id.'
                    ], 400);
                }
            }
            
            if (!$moodleCourseId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось определить Moodle Course ID для курса'
                ], 400);
            }

            $results = [
                'course_id_input' => $courseIdInput,
                'course_id_local' => $courseIdLocal,
                'moodle_course_id' => $moodleCourseId,
                'course_name' => $course->name ?? null,
                'student_id' => $studentId,
                'test_type' => $testType,
                'timestamp' => now()->toDateTimeString(),
                'data' => []
            ];

            // Получаем студента, если указан
            $student = null;
            $studentMoodleId = null;
            if ($studentId) {
                $student = \App\Models\User::find($studentId);
                if ($student) {
                    if (!$student->moodle_user_id) {
                        return response()->json([
                            'success' => false,
                            'error' => 'У выбранного студента отсутствует moodle_user_id. ' .
                                       'Студент должен быть синхронизирован с Moodle.'
                        ], 400);
                    }
                    $studentMoodleId = $student->moodle_user_id;
                    $results['student_info'] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'moodle_user_id' => $studentMoodleId
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Студент с ID ' . $studentId . ' не найден'
                    ], 404);
                }
            }

            // Получаем ID конкретных элементов для детального запроса
            $assignmentId = $request->input('assignment_id');
            $quizId = $request->input('quiz_id');
            $forumId = $request->input('forum_id');

            // Если указан конкретный ID элемента, показываем ТОЛЬКО детальную информацию об этом элементе
            // Иначе показываем все элементы курса
            if ($assignmentId) {
                // Детальная информация о конкретном задании
                $results['data']['assignment_details'] = $this->getAssignmentDetails($moodleApi, $assignmentId);
                $results['data']['is_specific_element'] = true;
                $results['data']['element_type'] = 'assignment';
                $results['data']['element_id'] = $assignmentId;
            } elseif ($quizId) {
                // Детальная информация о конкретном тесте
                $results['data']['quiz_details'] = $this->getQuizDetails($moodleApi, $quizId);
                $results['data']['is_specific_element'] = true;
                $results['data']['element_type'] = 'quiz';
                $results['data']['element_id'] = $quizId;
            } elseif ($forumId) {
                // Детальная информация о конкретном форуме
                $results['data']['forum_details'] = $this->getForumDetails($moodleApi, $forumId);
                $results['data']['is_specific_element'] = true;
                $results['data']['element_type'] = 'forum';
                $results['data']['element_id'] = $forumId;
            } else {
                // Обычный режим - показываем все элементы курса
                $results['data']['is_specific_element'] = false;
                
                // Тестируем в зависимости от типа
                // ВАЖНО: Передаем moodle_course_id, а не локальный ID
                if ($testType === 'assignments' || $testType === 'all') {
                    $results['data']['assignments'] = $this->testAssignments($moodleApi, $moodleCourseId, $studentMoodleId);
                }

                if ($testType === 'quizzes' || $testType === 'all') {
                    $results['data']['quizzes'] = $this->testQuizzes($moodleApi, $moodleCourseId, $studentMoodleId);
                }

                if ($testType === 'forums' || $testType === 'all') {
                    $results['data']['forums'] = $this->testForums($moodleApi, $moodleCourseId, $studentMoodleId);
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка тестирования Moodle API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Тестирование заданий
     *
     * @param MoodleApiService $moodleApi
     * @param int $courseId Moodle Course ID (НЕ локальный ID курса!)
     * @param int|null $studentMoodleId Moodle User ID студента (НЕ локальный ID пользователя!)
     * @return array
     */
    private function testAssignments(MoodleApiService $moodleApi, int $courseId, ?int $studentMoodleId): array
    {
        $result = [
            'api_call' => 'mod_assign_get_assignments',
            'params' => ['courseids' => [$courseId]],
            'assignments' => [],
            'student_data' => []
        ];

        // Получаем задания курса
        $assignments = $moodleApi->getCourseAssignments($courseId);
        $result['assignments'] = $assignments !== false ? $assignments : [];
        $result['assignments_count'] = is_array($assignments) ? count($assignments) : 0;

        // Если указан студент, получаем его сдачи и оценки
        if ($studentMoodleId) {
            $submissions = $moodleApi->getStudentSubmissions($courseId, $studentMoodleId, $assignments);
            $grades = $moodleApi->getStudentGrades($courseId, $studentMoodleId, $assignments);

            $result['student_data'] = [
                'submissions' => $submissions !== false ? $submissions : [],
                'grades' => $grades !== false ? $grades : [],
                'submissions_count' => is_array($submissions) ? count($submissions) : 0,
                'grades_count' => is_array($grades) ? count($grades) : 0
            ];
        }

        return $result;
    }

    /**
     * Тестирование тестов
     *
     * @param MoodleApiService $moodleApi
     * @param int $courseId Moodle Course ID (НЕ локальный ID курса!)
     * @param int|null $studentMoodleId Moodle User ID студента (НЕ локальный ID пользователя!)
     * @return array
     */
    private function testQuizzes(MoodleApiService $moodleApi, int $courseId, ?int $studentMoodleId): array
    {
        $result = [
            'api_call' => 'mod_quiz_get_quizzes_by_courses',
            'params' => ['courseids' => [$courseId]],
            'quizzes' => [],
            'student_data' => []
        ];

        // Получаем тесты курса
        $quizzes = $moodleApi->getCourseQuizzes($courseId);
        $result['quizzes'] = $quizzes !== false ? $quizzes : [];
        $result['quizzes_count'] = is_array($quizzes) ? count($quizzes) : 0;

        // Если указан студент, получаем его попытки и оценки
        if ($studentMoodleId) {
            $attempts = $moodleApi->getStudentQuizAttempts($courseId, $studentMoodleId, $quizzes);
            $grades = $moodleApi->getStudentQuizGrades($courseId, $studentMoodleId, $quizzes);

            $result['student_data'] = [
                'attempts' => $attempts !== false ? $attempts : [],
                'grades' => $grades !== false ? $grades : [],
                'total_attempts' => 0,
                'grades_count' => is_array($grades) ? count($grades) : 0
            ];

            // Подсчитываем общее количество попыток
            if (is_array($attempts)) {
                foreach ($attempts as $quizAttempts) {
                    $result['student_data']['total_attempts'] += count($quizAttempts);
                }
            }
        }

        return $result;
    }

    /**
     * Тестирование форумов
     *
     * @param MoodleApiService $moodleApi
     * @param int $courseId Moodle Course ID (НЕ локальный ID курса!)
     * @param int|null $studentMoodleId Moodle User ID студента (НЕ локальный ID пользователя!)
     * @return array
     */
    private function testForums(MoodleApiService $moodleApi, int $courseId, ?int $studentMoodleId): array
    {
        $result = [
            'api_call' => 'mod_forum_get_forums_by_courses',
            'params' => ['courseids' => [$courseId]],
            'forums' => [],
            'student_data' => []
        ];

        // Получаем форумы курса
        $forums = $moodleApi->getCourseForums($courseId);
        $result['forums'] = $forums !== false ? $forums : [];
        $result['forums_count'] = is_array($forums) ? count($forums) : 0;

        // Если указан студент, получаем его посты
        if ($studentMoodleId) {
            $forumPosts = $moodleApi->getStudentForumPosts($courseId, $studentMoodleId, $forums);

            $result['student_data'] = [
                'posts' => $forumPosts !== false ? $forumPosts : [],
                'total_posts' => 0,
                'unanswered_posts' => 0,
                'posts_by_forum' => []
            ];

            // Подсчитываем посты
            if (is_array($forumPosts) && !empty($forumPosts)) {
                foreach ($forumPosts as $forumId => $posts) {
                    $result['student_data']['posts_by_forum'][$forumId] = [
                        'posts_count' => count($posts),
                        'unanswered_count' => 0,
                        'posts' => []
                    ];

                    foreach ($posts as $post) {
                        $result['student_data']['total_posts']++;
                        $postData = [
                            'id' => $post['id'] ?? null,
                            'subject' => $post['subject'] ?? null,
                            'message' => isset($post['message']) ? strip_tags(substr($post['message'], 0, 200)) : null,
                            'timecreated' => $post['timecreated'] ?? null,
                            'has_teacher_reply' => $post['has_teacher_reply'] ?? false,
                            'needs_response' => $post['needs_response'] ?? false,
                            'author' => $post['author'] ?? null
                        ];

                        if ($postData['needs_response']) {
                            $result['student_data']['unanswered_posts']++;
                            $result['student_data']['posts_by_forum'][$forumId]['unanswered_count']++;
                        }

                        $result['student_data']['posts_by_forum'][$forumId]['posts'][] = $postData;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Получить детальную информацию о конкретном задании
     *
     * @param MoodleApiService $moodleApi
     * @param int $assignmentId Moodle Assignment ID
     * @return array
     */
    private function getAssignmentDetails(MoodleApiService $moodleApi, int $assignmentId): array
    {
        $result = [
            'api_call' => 'mod_assign_get_assignments',
            'params' => ['assignmentids' => [$assignmentId]],
            'assignment_id' => $assignmentId,
            'data' => null,
            'error' => null
        ];

        try {
            $details = $moodleApi->getAssignmentDetails($assignmentId);
            if ($details !== false) {
                $result['data'] = $details;
            } else {
                $result['error'] = 'Не удалось получить детальную информацию о задании';
            }
        } catch (\Exception $e) {
            $result['error'] = 'Ошибка: ' . $e->getMessage();
            Log::error('Ошибка получения детальной информации о задании', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Получить детальную информацию о конкретном тесте
     *
     * @param MoodleApiService $moodleApi
     * @param int $quizId Moodle Quiz ID
     * @return array
     */
    private function getQuizDetails(MoodleApiService $moodleApi, int $quizId): array
    {
        $result = [
            'api_call' => 'mod_quiz_get_quizzes_by_courses',
            'params' => ['quizids' => [$quizId]],
            'quiz_id' => $quizId,
            'data' => null,
            'error' => null
        ];

        try {
            $details = $moodleApi->getQuizDetails($quizId);
            if ($details !== false) {
                $result['data'] = $details;
            } else {
                $result['error'] = 'Не удалось получить детальную информацию о тесте';
            }
        } catch (\Exception $e) {
            $result['error'] = 'Ошибка: ' . $e->getMessage();
            Log::error('Ошибка получения детальной информации о тесте', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Получить детальную информацию о конкретном форуме
     *
     * @param MoodleApiService $moodleApi
     * @param int $forumId Moodle Forum ID
     * @return array
     */
    private function getForumDetails(MoodleApiService $moodleApi, int $forumId): array
    {
        $result = [
            'api_call' => 'mod_forum_get_forums_by_courses',
            'params' => ['forumid' => $forumId],
            'forum_id' => $forumId,
            'data' => null,
            'error' => null
        ];

        try {
            $details = $moodleApi->getForumDetails($forumId);
            if ($details !== false) {
                $result['data'] = $details;
            } else {
                $result['error'] = 'Не удалось получить детальную информацию о форуме';
            }
        } catch (\Exception $e) {
            $result['error'] = 'Ошибка: ' . $e->getMessage();
            Log::error('Ошибка получения детальной информации о форуме', [
                'forum_id' => $forumId,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }
}
