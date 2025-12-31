<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Контроллер для управления курсами
 *
 * Обеспечивает CRUD операции для курсов в административной панели.
 * Управляет связями между курсами, программами и преподавателями.
 */
class CourseController extends Controller
{
    /**
     * Отобразить список всех курсов
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        
        // Если пользователь - админ, показываем все курсы
        if ($isAdmin) {
            $courses = Course::with(['program.institution', 'instructor'])->paginate(15);
            $coursesWithAssignments = [];
            return view('admin.courses.index', compact('courses', 'isAdmin', 'coursesWithAssignments'));
        }
        
        // Если пользователь - студент, показываем только его курсы
        // Сначала получаем ID курсов из таблицы user_courses
        $enrolledCourseIds = DB::table('user_courses')
            ->where('user_id', $user->id)
            ->pluck('course_id')
            ->toArray();
        
        // Логируем для отладки
        Log::info('Курсы студента', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'enrolled_course_ids' => $enrolledCourseIds,
            'courses_count' => count($enrolledCourseIds)
        ]);
        
        // Если нет записей, возвращаем пустую коллекцию с пагинацией
        if (empty($enrolledCourseIds)) {
            $courses = Course::whereIn('id', [])->with(['program.institution', 'instructor'])->paginate(15);
        } else {
            // Загружаем курсы по ID с пагинацией
            $courses = Course::whereIn('id', $enrolledCourseIds)
                ->with(['program.institution', 'instructor'])
                ->paginate(15);
        }
        
        // Получаем задания из Moodle для каждого курса
        $coursesWithAssignments = [];
        $moodleApiService = null;
        
        if ($user->moodle_user_id) {
            try {
                $moodleApiService = new MoodleApiService();
            } catch (\Exception $e) {
                Log::error('Ошибка инициализации MoodleApiService', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        foreach ($courses as $course) {
            $assignmentsData = null;
            
            // Получаем задания только если есть moodle_user_id и moodle_course_id
            if ($moodleApiService && $user->moodle_user_id && $course->moodle_course_id) {
                try {
                    $assignments = $moodleApiService->getCourseAssignmentsWithStatus(
                        $course->moodle_course_id,
                        $user->moodle_user_id,
                        'ПОСЛЕ СЕССИИ'
                    );
                    
                    if ($assignments !== false && !empty($assignments)) {
                        $assignmentsData = $assignments;
                    }
                } catch (\Exception $e) {
                    Log::error('Ошибка при получении заданий из Moodle', [
                        'course_id' => $course->id,
                        'moodle_course_id' => $course->moodle_course_id,
                        'moodle_user_id' => $user->moodle_user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $coursesWithAssignments[$course->id] = $assignmentsData;
        }
        
        return view('admin.courses.index', compact('courses', 'isAdmin', 'coursesWithAssignments'));
    }

    /**
     * Показать форму для создания нового курса
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Получение активных программ с их учебными заведениями
        $programs = Program::active()->with('institution')->get();

        // Получение пользователей с ролью преподавателя
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })->get();

        return view('admin.courses.create', compact('programs', 'instructors'));
    }

    /**
     * Сохранить новый курс в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'program_id' => 'required|exists:programs,id',
            'instructor_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:20',
            'credits' => 'nullable|integer|min:0',
            'duration' => 'nullable|string|max:100',
            'schedule' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'string',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для сохранения
        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_paid'] = $request->boolean('is_paid');

        // Если курс не платный, обнуляем цену
        if (!$data['is_paid']) {
            $data['price'] = null;
        }

        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            // Убеждаемся, что директория существует
            Storage::disk('public')->makeDirectory('courses');

            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('courses', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        Course::create($data);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно создан.');
    }

    /**
     * Отобразить конкретный курс
     *
     * @param Course $course
     * @return \Illuminate\View\View
     */
    public function show(Course $course)
    {
        $course->load(['program.institution', 'instructor']);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Показать форму для редактирования курса
     *
     * @param Course $course
     * @return \Illuminate\View\View
     */
    public function edit(Course $course)
    {
        // Получение активных программ с их учебными заведениями
        $programs = Program::active()->with('institution')->get();

        // Получение пользователей с ролью преподавателя
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })->get();

        return view('admin.courses.edit', compact('course', 'programs', 'instructors'));
    }

    /**
     * Обновить курс в базе данных
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Course $course)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'program_id' => 'required|exists:programs,id',
            'instructor_id' => 'nullable|exists:users,id',
            'code' => 'nullable|string|max:20',
            'credits' => 'nullable|integer|min:0',
            'duration' => 'nullable|string|max:100',
            'schedule' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'string',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для обновления
        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_paid'] = $request->boolean('is_paid');

        // Если курс не платный, обнуляем цену
        if (!$data['is_paid']) {
            $data['price'] = null;
        }

        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            // Убеждаемся, что директория существует
            Storage::disk('public')->makeDirectory('courses');

            // Удаляем старое изображение, если оно существует
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('courses', $imageName, 'public');
            $data['image'] = $imagePath;
        } elseif ($request->has('remove_image')) {
            // Удаляем изображение, если пользователь хочет его удалить
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
            $data['image'] = null;
        } else {
            // Сохраняем существующее изображение
            unset($data['image']);
        }

        $course->update($data);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно обновлен.');
    }

    /**
     * Удалить курс из базы данных
     *
     * @param Course $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Курс успешно удален.');
    }

    /**
     * Создать дубликат курса
     */
    public function duplicate(Course $course)
    {
        $duplicate = $course->replicate();
        $duplicate->name = $this->generateCopyLabel($course->name, Course::class, 'name');
        $duplicate->code = $this->generateCopyCode($course->code);
        $duplicate->save();

        return redirect()->route('admin.courses.edit', $duplicate)
            ->with('success', 'Дубликат курса создан. Проверьте и обновите данные перед публикацией.');
    }

    /**
     * Сформировать уникальное название с пометкой копии
     */
    private function generateCopyLabel(?string $original, string $modelClass, string $field, string $suffix = 'копия'): string
    {
        $base = $original ?: 'Новая запись';
        $candidate = $this->formatCopyLabel($base, $suffix);
        $counter = 2;

        while ($modelClass::where($field, $candidate)->exists()) {
            $candidate = $this->formatCopyLabel($base, $suffix, $counter);
            $counter++;
        }

        return Str::limit($candidate, 255, '');
    }

    /**
     * Сформировать код дубликата, если исходный код присутствует
     */
    private function generateCopyCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $base = Str::limit($code, 245, '');
        $candidate = $base . '-copy';
        $counter = 2;

        while (Course::where('code', $candidate)->exists()) {
            $candidate = $base . '-copy' . $counter;
            $counter++;
        }

        return Str::limit($candidate, 255, '');
    }

    /**
     * Собрать подпись дубликата с учетом счетчика
     */
    private function formatCopyLabel(string $base, string $suffix, ?int $counter = null): string
    {
        if ($counter === null) {
            return sprintf('%s (%s)', $base, $suffix);
        }

        return sprintf('%s (%s %d)', $base, $suffix, $counter);
    }
}
