<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Program;
use Illuminate\Http\Request;

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
        $programs = Program::with(['institution', 'courses'])->paginate(15);
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
        $program->load(['institution', 'courses.instructor']);
        return view('admin.programs.show', compact('program'));
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
        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно удалена.');
    }
}
