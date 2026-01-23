<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Контроллер для управления образовательными программами
 *
 * Обеспечивает CRUD операции для образовательных программ в административной панели.
 * Управляет связями между программами и учебными заведениями.
 */
class ProgramController extends Controller
{
    /**
     * Отобразить список всех образовательных программ
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $programs = Program::with(['institution', 'subjects', 'courses'])->paginate(15);
        return view('admin.programs.index', compact('programs'));
    }

    /**
     * Показать форму для создания новой образовательной программы
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $institutions = Institution::active()->get();
        return view('admin.programs.create', compact('institutions'));
    }

    /**
     * Сохранить новую образовательную программу в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'duration' => 'nullable|numeric|min:0',
            'credits' => 'nullable|numeric|min:0',
            'degree_level' => 'nullable|string|max:100',
            'tuition_fee' => 'nullable|numeric|min:0',
            'language' => 'nullable|string|max:10',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
            'is_active' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для сохранения
        $data = $request->all();

        // Если программа не платная, обнуляем цену
        if (!$request->boolean('is_paid')) {
            $data['price'] = null;
        }

        // Устанавливаем значения по умолчанию для обязательных полей
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_paid'] = $request->boolean('is_paid', false);
        $data['language'] = $request->input('language', 'ru');

        try {
            Program::create($data);

            return redirect()->route('admin.programs.index')
                ->with('success', 'Учебная программа успешно создана.');
        } catch (\Exception $e) {
            \Log::error('Ошибка создания программы: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при создании программы: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Отобразить конкретную образовательную программу
     *
     * @param Program $program
     * @return \Illuminate\View\View
     */
    public function show(Program $program)
    {
        // Загружаем предметы и курсы
        $program->load(['institution', 'subjects']);
        
        // Загружаем предметы программы с сортировкой
        $subjects = $program->subjects()
            ->orderBy('program_subject.order', 'asc')
            ->orderBy('subjects.order', 'asc')
            ->orderBy('subjects.name', 'asc')
            ->get();
        
        // Для каждого предмета загружаем его курсы
        foreach ($subjects as $subject) {
            $subject->load(['courses' => function ($query) {
                $query->with('instructor')
                    ->orderBy('order', 'asc')
                    ->orderBy('id', 'asc');
            }]);
            
            // Загружаем количество студентов для каждого курса предмета
            foreach ($subject->courses as $course) {
                $course->loadCount(['users' => function ($query) {
                    $query->whereHas('roles', function ($q) {
                        $q->where('slug', 'student');
                    });
                }]);
            }
        }
        
        // Также загружаем старые курсы (для обратной совместимости)
        $courses = Course::where('program_id', $program->id)
            ->whereNull('subject_id')
            ->with('instructor')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        $courses->loadCount(['users' => function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('slug', 'student');
            });
        }]);
        
        // Получаем все доступные предметы для добавления в программу
        $availableSubjects = Subject::active()
            ->orderBy('order')
            ->orderBy('name')
            ->get();
        
        return view('admin.programs.show', compact('program', 'subjects', 'courses', 'availableSubjects'));
    }
    
    /**
     * Переместить курс вверх в списке программы
     *
     * @param Program $program
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveCourseUp(Program $program, $courseId)
    {
        $course = Course::findOrFail($courseId);
        
        // Проверяем, что курс принадлежит программе
        if ($course->program_id !== $program->id) {
            abort(404, 'Курс не принадлежит данной программе');
        }
        
        // Получаем все курсы программы, отсортированные по order
        $courses = Course::where('program_id', $program->id)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        $currentIndex = $courses->search(function ($item) use ($course) {
            return $item->id === $course->id;
        });
        
        // Если курс не первый, меняем местами с предыдущим
        if ($currentIndex > 0) {
            $previousCourse = $courses[$currentIndex - 1];
            
            // Меняем местами порядок
            $tempOrder = $course->order;
            $course->order = $previousCourse->order;
            $previousCourse->order = $tempOrder;
            
            $course->save();
            $previousCourse->save();
        }
        
        return redirect()->route('admin.programs.show', $program)
            ->with('success', 'Порядок курса изменен');
    }
    
    /**
     * Переместить курс вниз в списке программы
     *
     * @param Program $program
     * @param int $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveCourseDown(Program $program, $courseId)
    {
        $course = Course::findOrFail($courseId);
        
        // Проверяем, что курс принадлежит программе
        if ($course->program_id !== $program->id) {
            abort(404, 'Курс не принадлежит данной программе');
        }
        
        // Получаем все курсы программы, отсортированные по order
        $courses = Course::where('program_id', $program->id)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        $currentIndex = $courses->search(function ($item) use ($course) {
            return $item->id === $course->id;
        });
        
        // Если курс не последний, меняем местами со следующим
        if ($currentIndex !== false && $currentIndex < $courses->count() - 1) {
            $nextCourse = $courses[$currentIndex + 1];
            
            // Меняем местами порядок
            $tempOrder = $course->order;
            $course->order = $nextCourse->order;
            $nextCourse->order = $tempOrder;
            
            $course->save();
            $nextCourse->save();
        }
        
        return redirect()->route('admin.programs.show', $program)
            ->with('success', 'Порядок курса изменен');
    }

    /**
     * Показать форму для редактирования образовательной программы
     *
     * @param Program $program
     * @return \Illuminate\View\View
     */
    public function edit(Program $program)
    {
        $institutions = Institution::active()->get();
        return view('admin.programs.edit', compact('program', 'institutions'));
    }

    /**
     * Обновить образовательную программу в базе данных
     *
     * @param Request $request
     * @param Program $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Program $program)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'duration' => 'nullable|numeric|min:0',
            'credits' => 'nullable|numeric|min:0',
            'degree_level' => 'nullable|string|max:100',
            'tuition_fee' => 'nullable|numeric|min:0',
            'language' => 'nullable|string|max:10',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
            'is_active' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для обновления
        $data = $request->all();

        // Если программа не платная, обнуляем цену
        if (!$request->boolean('is_paid')) {
            $data['price'] = null;
        }

        // Устанавливаем значения по умолчанию для обязательных полей
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_paid'] = $request->boolean('is_paid', false);
        $data['language'] = $request->input('language', $program->language ?? 'ru');

        try {
            $program->update($data);

            return redirect()->route('admin.programs.index')
                ->with('success', 'Учебная программа успешно обновлена.');
        } catch (\Exception $e) {
            \Log::error('Ошибка обновления программы: ' . $e->getMessage(), [
                'program_id' => $program->id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при обновлении программы: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Удалить образовательную программу из базы данных
     *
     * @param Program $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Program $program)
    {
        // Проверяем зависимости перед удалением
        $canDelete = $program->canBeDeleted();
        
        if (!$canDelete['can_delete']) {
            return redirect()->back()
                ->with('error', $canDelete['message'])
                ->with('dependencies', $canDelete['dependencies']);
        }

        // Используем транзакцию для безопасности
        \DB::transaction(function () use ($program) {
            $program->delete();
        });

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно удалена.');
    }

    /**
     * Добавить предмет в программу
     *
     * @param Request $request
     * @param Program $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function attachSubject(Request $request, Program $program)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $subjectId = $request->input('subject_id');
        $order = $request->input('order', 0);

        // Проверяем, не добавлен ли уже предмет
        if ($program->subjects()->where('subject_id', $subjectId)->exists()) {
            return redirect()->back()
                ->with('error', 'Этот предмет уже добавлен в программу.');
        }

        $program->subjects()->attach($subjectId, ['order' => $order]);

        return redirect()->back()
            ->with('success', 'Предмет успешно добавлен в программу.');
    }

    /**
     * Удалить предмет из программы
     *
     * @param Program $program
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function detachSubject(Program $program, Subject $subject)
    {
        $program->subjects()->detach($subject->id);

        return redirect()->back()
            ->with('success', 'Предмет успешно удален из программы.');
    }

    /**
     * Переместить предмет вверх в списке программы
     *
     * @param Program $program
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveSubjectUp(Program $program, Subject $subject)
    {
        $subjects = $program->subjects()
            ->orderBy('program_subject.order', 'asc')
            ->orderBy('subjects.name', 'asc')
            ->get();

        $currentIndex = $subjects->search(function ($item) use ($subject) {
            return $item->id === $subject->id;
        });

        if ($currentIndex > 0) {
            $previousSubject = $subjects[$currentIndex - 1];
            
            $currentOrder = $program->subjects()->where('subject_id', $subject->id)->first()->pivot->order;
            $previousOrder = $program->subjects()->where('subject_id', $previousSubject->id)->first()->pivot->order;
            
            $program->subjects()->updateExistingPivot($subject->id, ['order' => $previousOrder]);
            $program->subjects()->updateExistingPivot($previousSubject->id, ['order' => $currentOrder]);
        }

        return redirect()->back()
            ->with('success', 'Порядок предмета изменен');
    }

    /**
     * Переместить предмет вниз в списке программы
     *
     * @param Program $program
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function moveSubjectDown(Program $program, Subject $subject)
    {
        $subjects = $program->subjects()
            ->orderBy('program_subject.order', 'asc')
            ->orderBy('subjects.name', 'asc')
            ->get();

        $currentIndex = $subjects->search(function ($item) use ($subject) {
            return $item->id === $subject->id;
        });

        if ($currentIndex !== false && $currentIndex < $subjects->count() - 1) {
            $nextSubject = $subjects[$currentIndex + 1];
            
            $currentOrder = $program->subjects()->where('subject_id', $subject->id)->first()->pivot->order;
            $nextOrder = $program->subjects()->where('subject_id', $nextSubject->id)->first()->pivot->order;
            
            $program->subjects()->updateExistingPivot($subject->id, ['order' => $nextOrder]);
            $program->subjects()->updateExistingPivot($nextSubject->id, ['order' => $currentOrder]);
        }

        return redirect()->back()
            ->with('success', 'Порядок предмета изменен');
    }

    /**
     * Создать дубликат программы
     *
     * @param Program $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(Program $program)
    {
        try {
            DB::beginTransaction();

            // Создаем копию программы
            $duplicate = $program->replicate();
            $duplicate->name = $this->generateCopyLabel($program->name, Program::class, 'name');
            $duplicate->code = $this->generateCopyCode($program->code);
            $duplicate->is_active = false; // Дубликат создается неактивным
            $duplicate->save();

            // Копируем связи с предметами (subjects)
            $subjects = $program->subjects()->get();
            foreach ($subjects as $subject) {
                $pivotData = $program->subjects()->where('subject_id', $subject->id)->first()->pivot;
                $duplicate->subjects()->attach($subject->id, [
                    'order' => $pivotData->order ?? 0
                ]);
            }

            DB::commit();

            return redirect()->route('admin.programs.edit', $duplicate)
                ->with('success', 'Дубликат программы создан. Проверьте и обновите данные перед активацией.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ошибка дублирования программы', [
                'program_id' => $program->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при дублировании программы: ' . $e->getMessage());
        }
    }

    /**
     * Сформировать уникальное название с пометкой копии
     *
     * @param string|null $original
     * @param string $modelClass
     * @param string $field
     * @param string $suffix
     * @return string
     */
    private function generateCopyLabel(?string $original, string $modelClass, string $field, string $suffix = 'копия'): string
    {
        $base = $original ?: 'Новая программа';
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
     *
     * @param string|null $code
     * @return string|null
     */
    private function generateCopyCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $base = Str::limit($code, 245, '');
        $candidate = $base . '-copy';
        $counter = 2;

        while (Program::where('code', $candidate)->exists()) {
            $candidate = $base . '-copy' . $counter;
            $counter++;
        }

        return Str::limit($candidate, 255, '');
    }

    /**
     * Собрать подпись дубликата с учетом счетчика
     *
     * @param string $base
     * @param string $suffix
     * @param int|null $counter
     * @return string
     */
    private function formatCopyLabel(string $base, string $suffix, ?int $counter = null): string
    {
        if ($counter === null) {
            return $base . ' (' . $suffix . ')';
        }

        return $base . ' (' . $suffix . ' ' . $counter . ')';
    }
}
