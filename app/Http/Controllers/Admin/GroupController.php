<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Контроллер для управления группами студентов
 *
 * Обеспечивает CRUD операции для групп в административной панели.
 * Включает управление студентами в группах.
 */
class GroupController extends Controller
{
    /**
     * Отобразить список всех групп
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Group::with(['course', 'program', 'students']);

        // Поиск по названию
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Фильтр по курсу
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Фильтр по программе
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Фильтр по активности
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $groups = $query->paginate(15)->withQueryString();
        $courses = Course::active()->orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();

        return view('admin.groups.index', compact('groups', 'courses', 'programs'));
    }

    /**
     * Показать форму для создания новой группы
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $courses = Course::active()->orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();
        return view('admin.groups.create', compact('courses', 'programs'));
    }

    /**
     * Сохранить новую группу в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:courses,id',
            'program_id' => 'nullable|exists:programs,id',
            'is_active' => 'boolean',
        ]);

        Group::create($request->all());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа успешно создана.');
    }

    /**
     * Отобразить конкретную группу со списком студентов
     *
     * @param Group $group
     * @return \Illuminate\View\View
     */
    public function show(Group $group)
    {
        $group->load(['course', 'program', 'students' => function ($query) {
            $query->orderBy('name');
        }]);
        return view('admin.groups.show', compact('group'));
    }

    /**
     * Показать форму для редактирования группы
     *
     * @param Group $group
     * @return \Illuminate\View\View
     */
    public function edit(Group $group)
    {
        $courses = Course::active()->orderBy('name')->get();
        $programs = Program::active()->orderBy('name')->get();
        return view('admin.groups.edit', compact('group', 'courses', 'programs'));
    }

    /**
     * Обновить группу в базе данных
     *
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'nullable|exists:courses,id',
            'program_id' => 'nullable|exists:programs,id',
            'is_active' => 'boolean',
        ]);

        $group->update($request->all());

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа успешно обновлена.');
    }

    /**
     * Удалить группу из базы данных
     *
     * @param Group $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Group $group)
    {
        // Проверяем зависимости перед удалением
        $canDelete = $group->canBeDeleted();

        if (!$canDelete['can_delete']) {
            return redirect()->back()
                ->with('error', $canDelete['message'])
                ->with('dependencies', $canDelete['dependencies']);
        }

        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Группа успешно удалена.');
    }

    /**
     * Добавить студента в группу
     *
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addStudent(Request $request, Group $group)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        // Проверяем, не состоит ли уже студент в группе
        if ($group->students()->where('user_id', $request->user_id)->exists()) {
            return redirect()->back()
                ->with('error', 'Студент уже состоит в этой группе.');
        }

        $group->students()->attach($request->user_id, [
            'notes' => $request->notes,
            'enrolled_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Студент успешно добавлен в группу.');
    }

    /**
     * Удалить студента из группы
     *
     * @param Group $group
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeStudent(Group $group, User $user)
    {
        $group->students()->detach($user->id);

        return redirect()->back()
            ->with('success', 'Студент успешно удален из группы.');
    }
}
