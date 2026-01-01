<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\MoodleApiService;
use App\Services\MoodleSyncService;
use App\Services\CourseActivitySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

/**
 * Контроллер для управления профилем пользователя
 *
 * Обрабатывает просмотр и редактирование профиля авторизованного пользователя.
 * Включает загрузку фото и управление личной информацией.
 */
class ProfileController extends Controller
{
    /**
     * Показать профиль пользователя
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        
        // Синхронизируем данные пользователя из Moodle для актуальности информации
        $this->syncUserDataFromMoodle($user);
        
        // Перезагружаем пользователя с актуальными данными
        $user->refresh();
        $user->load(['courses.program.institution', 'courses.instructor', 'programs.institution']);
        
        // Получаем данные о заданиях для всех курсов пользователя
        $coursesWithAssignments = [];
        $moodleApiService = null;
        
        if ($user->moodle_user_id) {
            try {
                $moodleApiService = new MoodleApiService();
            } catch (\Exception $e) {
                Log::error('Ошибка инициализации MoodleApiService в ProfileController', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Получаем задания для всех курсов пользователя
        foreach ($user->courses as $course) {
            if ($moodleApiService && $course->moodle_course_id) {
                try {
                    $assignments = $moodleApiService->getCourseAssignmentsWithStatus(
                        $course->moodle_course_id,
                        $user->moodle_user_id,
                        'ПОСЛЕ СЕССИИ'
                    );
                    
                    if ($assignments !== false && !empty($assignments)) {
                        $coursesWithAssignments[$course->id] = $assignments;
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка при получении заданий из Moodle в ProfileController', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $course->moodle_course_id,
                        'moodle_user_id' => $user->moodle_user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Для обратной совместимости оставляем данные о курсе id=15
        $assignmentsData = $coursesWithAssignments[15] ?? null;
        $course15 = Course::find(15);

        // Определяем, какой layout использовать в зависимости от маршрута
        if (request()->routeIs('profile.*')) {
            // Публичный маршрут - используем публичное представление
            return view('public.profile.show', compact('user', 'assignmentsData', 'course15', 'coursesWithAssignments'));
        }

        // Админский маршрут - используем админское представление
        return view('admin.profile.show', compact('user', 'assignmentsData', 'course15', 'coursesWithAssignments'));
    }
    
    /**
     * Синхронизировать данные пользователя из Moodle
     *
     * @param \App\Models\User $user
     * @return void
     */
    private function syncUserDataFromMoodle($user): void
    {
        // Проверяем, есть ли у пользователя moodle_user_id
        if (!$user->moodle_user_id) {
            Log::info('Пользователь не имеет moodle_user_id, пропускаем синхронизацию', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return;
        }

        try {
            $syncService = new MoodleSyncService();
            $moodleApi = new MoodleApiService();
            
            // Пытаемся получить курсы пользователя из Moodle напрямую
            $userMoodleCourses = $moodleApi->call('core_enrol_get_users_courses', [
                'userid' => $user->moodle_user_id
            ]);
            
            // Проверяем результат - если ошибка доступа, используем альтернативный способ
            if ($userMoodleCourses === false || isset($userMoodleCourses['exception'])) {
                // Альтернативный способ: синхронизируем все курсы и затем фильтруем по пользователю
                $syncService->syncCourses();
                
                // Синхронизируем записи для всех курсов
                $allCourses = Course::whereNotNull('moodle_course_id')->get();
                foreach ($allCourses as $course) {
                    try {
                        $syncService->syncCourseEnrollments($course->id);
                    } catch (\Exception $e) {
                        Log::error('Ошибка синхронизации записей на курс', [
                            'course_id' => $course->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } else {
                // Фильтруем системный курс с id=1
                if (is_array($userMoodleCourses)) {
                    $userMoodleCourses = array_values(array_filter($userMoodleCourses, function($course) {
                        return isset($course['id']) && $course['id'] > 1;
                    }));
                }
                
                // Синхронизируем только курсы пользователя
                if (!empty($userMoodleCourses)) {
                    foreach ($userMoodleCourses as $moodleCourse) {
                        try {
                            $syncService->syncCourse($moodleCourse);
                        } catch (\Exception $e) {
                            Log::error('Ошибка синхронизации курса', [
                                'user_id' => $user->id,
                                'moodle_course_id' => $moodleCourse['id'] ?? null,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    // Синхронизируем записи пользователя на курсы
                    foreach ($userMoodleCourses as $moodleCourse) {
                        try {
                            $localCourse = Course::where('moodle_course_id', $moodleCourse['id'])->first();
                            if ($localCourse) {
                                $syncService->syncUserEnrollment($localCourse, [
                                    'id' => $user->moodle_user_id,
                                    'email' => $user->email
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Ошибка синхронизации записи на курс', [
                                'user_id' => $user->id,
                                'moodle_course_id' => $moodleCourse['id'] ?? null,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
            }
            
            // Синхронизируем прогресс студента только по его курсам
            if ($user->hasRole('student')) {
                try {
                    $activitySyncService = new CourseActivitySyncService();
                    $user->load('courses');
                    
                    foreach ($user->courses as $course) {
                        if ($course->moodle_course_id) {
                            try {
                                $activitySyncService->syncStudentProgress($course->id, $user->id);
                            } catch (\Exception $e) {
                                Log::error('Ошибка синхронизации прогресса студента', [
                                    'user_id' => $user->id,
                                    'course_id' => $course->id,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка инициализации CourseActivitySyncService', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\InvalidArgumentException $e) {
            // Конфигурация Moodle не настроена - это нормально, просто пропускаем синхронизацию
            Log::info('Moodle не настроен, пропускаем синхронизацию', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации данных пользователя из Moodle в ProfileController', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Показать форму редактирования профиля
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();

        // Определяем, какой layout использовать в зависимости от маршрута
        if (request()->routeIs('profile.*')) {
            // Публичный маршрут - используем админское представление редактирования
            // (можно создать публичное представление редактирования позже)
            return view('admin.profile.edit', compact('user'));
        }

        // Админский маршрут - используем админское представление
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Обновить профиль пользователя
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'church' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'education' => 'nullable|string|max:100',
            'about_me' => 'nullable|string|max:1000',
            'bio' => 'nullable|string|max:2000',
            'photo' => [
                'nullable',
                File::image()
                    ->max(2 * 1024) // 2MB
                    ->types(['jpg', 'jpeg', 'png', 'gif'])
            ],
            'is_active' => 'boolean',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'city', 'religion', 'church',
            'marital_status', 'education', 'about_me', 'bio', 'is_active'
        ]);

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удалить старое фото, если оно есть
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Сохранить новое фото
            $photoPath = $request->file('photo')->store('photos', 'public');
            $data['photo'] = $photoPath;
        }

        // Обновить пользователя
        $user->update($data);

        // Определяем, на какой маршрут перенаправлять
        if (request()->routeIs('profile.*')) {
            return redirect()->route('profile.show')
                ->with('success', 'Профиль успешно обновлен.');
        }

        return redirect()->route('admin.profile.show')
            ->with('success', 'Профиль успешно обновлен.');
    }
}
