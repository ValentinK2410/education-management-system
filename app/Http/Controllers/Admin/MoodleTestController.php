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
        
        return view('admin.moodle-test.index');
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
        ]);

        $courseId = $request->input('course_id');
        $studentId = $request->input('student_id');
        $testType = $request->input('test_type');

        try {
            // Используем токен текущего пользователя
            $userToken = $user->getMoodleToken();
            $moodleApi = new MoodleApiService(null, $userToken);

            $results = [
                'course_id' => $courseId,
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
                    $studentMoodleId = $student->moodle_user_id;
                    $results['student_info'] = [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'moodle_user_id' => $studentMoodleId
                    ];
                }
            }

            // Тестируем в зависимости от типа
            if ($testType === 'assignments' || $testType === 'all') {
                $results['data']['assignments'] = $this->testAssignments($moodleApi, $courseId, $studentMoodleId);
            }

            if ($testType === 'quizzes' || $testType === 'all') {
                $results['data']['quizzes'] = $this->testQuizzes($moodleApi, $courseId, $studentMoodleId);
            }

            if ($testType === 'forums' || $testType === 'all') {
                $results['data']['forums'] = $this->testForums($moodleApi, $courseId, $studentMoodleId);
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
     * @param int $courseId
     * @param int|null $studentMoodleId
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
     * @param int $courseId
     * @param int|null $studentMoodleId
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
     * @param int $courseId
     * @param int|null $studentMoodleId
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
}
