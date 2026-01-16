<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\Request;

/**
 * Контроллер для управления предметами (глобальными курсами)
 *
 * Обеспечивает CRUD операции для предметов в административной панели.
 * Предмет объединяет несколько курсов одной тематики.
 */
class SubjectController extends Controller
{
    /**
     * Отобразить список всех предметов
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $subjects = Subject::withCount('courses')
            ->withCount('programs')
            ->with('programs') // Загружаем программы для каждого предмета
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(15);
        
        // Получаем все активные программы для модального окна
        $availablePrograms = Program::active()
            ->with('institution')
            ->orderBy('name')
            ->get();
        
        return view('admin.subjects.index', compact('subjects', 'availablePrograms'));
    }

    /**
     * Показать форму для создания нового предмета
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.subjects.create');
    }

    /**
     * Сохранить новый предмет в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['order'] = $request->input('order', 0);

        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('subjects', 'public');
            $data['image'] = $imagePath;
        }

        try {
            Subject::create($data);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'Предмет успешно создан.');
        } catch (\Exception $e) {
            \Log::error('Ошибка создания предмета: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при создании предмета: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Отобразить конкретный предмет
     *
     * @param Subject $subject
     * @return \Illuminate\View\View
     */
    public function show(Subject $subject)
    {
        $subject->load(['courses.instructor', 'programs.institution']);
        
        // Загружаем курсы с сортировкой
        $courses = Course::where('subject_id', $subject->id)
            ->with('instructor')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        
        // Загружаем количество студентов для каждого курса
        $courses->loadCount(['users' => function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('slug', 'student');
            });
        }]);
        
        // Загружаем программы предмета с сортировкой
        $programs = $subject->programs()
            ->orderBy('program_subject.order', 'asc')
            ->orderBy('programs.name', 'asc')
            ->get();
        
        // Получаем все доступные программы для добавления
        $availablePrograms = Program::active()
            ->orderBy('name')
            ->get();
        
        return view('admin.subjects.show', compact('subject', 'courses', 'programs', 'availablePrograms'));
    }

    /**
     * Показать форму для редактирования предмета
     *
     * @param Subject $subject
     * @return \Illuminate\View\View
     */
    public function edit(Subject $subject)
    {
        // Загружаем программы предмета
        $subject->load('programs.institution');
        
        // Получаем все активные программы для выбора
        $availablePrograms = Program::active()
            ->with('institution')
            ->orderBy('name')
            ->get();
        
        return view('admin.subjects.edit', compact('subject', 'availablePrograms'));
    }

    /**
     * Обновить предмет в базе данных
     *
     * @param Request $request
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Subject $subject)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', $subject->is_active);
        $data['order'] = $request->input('order', $subject->order);

        // Обработка загрузки изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение, если есть
            if ($subject->image) {
                \Storage::disk('public')->delete($subject->image);
            }
            $imagePath = $request->file('image')->store('subjects', 'public');
            $data['image'] = $imagePath;
        }

        try {
            $subject->update($data);

            return redirect()->route('admin.subjects.index')
                ->with('success', 'Предмет успешно обновлен.');
        } catch (\Exception $e) {
            \Log::error('Ошибка обновления предмета: ' . $e->getMessage(), [
                'subject_id' => $subject->id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Произошла ошибка при обновлении предмета: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Удалить предмет из базы данных
     *
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subject $subject)
    {
        // Проверяем зависимости перед удалением
        $canDelete = $subject->canBeDeleted();
        
        if (!$canDelete['can_delete']) {
            return redirect()->back()
                ->with('error', $canDelete['message'])
                ->with('dependencies', $canDelete['dependencies']);
        }

        // Удаляем изображение, если есть
        if ($subject->image) {
            \Storage::disk('public')->delete($subject->image);
        }

        // Используем транзакцию для безопасности
        \DB::transaction(function () use ($subject) {
            $subject->delete();
        });

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Предмет успешно удален.');
    }

    /**
     * Добавить программу к предмету
     *
     * @param Request $request
     * @param Subject $subject
     * @return \Illuminate\Http\RedirectResponse
     */
    public function attachProgram(Request $request, Subject $subject)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $programId = $request->input('program_id');
        $order = $request->input('order', 0);

        // Проверяем, не добавлена ли уже программа
        if ($subject->programs()->where('program_id', $programId)->exists()) {
            return redirect()->back()
                ->with('error', 'Эта программа уже добавлена к предмету.');
        }

        $subject->programs()->attach($programId, ['order' => $order]);

        return redirect()->back()
            ->with('success', 'Программа успешно добавлена к предмету.');
    }

    /**
     * Удалить программу из предмета
     *
     * @param Subject $subject
     * @param Program $program
     * @return \Illuminate\Http\RedirectResponse
     */
    public function detachProgram(Subject $subject, Program $program)
    {
        $subject->programs()->detach($program->id);

        return redirect()->back()
            ->with('success', 'Программа успешно удалена из предмета.');
    }
}
