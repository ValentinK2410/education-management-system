<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'language' => 'required|string|in:ru,en',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для сохранения
        $data = $request->all();

        // Если программа не платная, обнуляем цену
        if (!$request->boolean('is_paid')) {
            $data['price'] = null;
        }

        Program::create($data);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно создана.');
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
            'language' => 'required|string|in:ru,en',
            'is_active' => 'boolean',
            'is_paid' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:RUB,USD,EUR',
        ]);

        // Подготовка данных для обновления
        $data = $request->all();

        // Если программа не платная, обнуляем цену
        if (!$request->boolean('is_paid')) {
            $data['price'] = null;
        }

        $program->update($data);

        return redirect()->route('admin.programs.index')
            ->with('success', 'Учебная программа успешно обновлена.');
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

    /**
     * Создать дубликат образовательной программы
     */
    public function duplicate(Program $program)
    {
        $duplicate = $program->replicate();
        $duplicate->name = $this->generateCopyLabel($program->name, Program::class, 'name');
        $duplicate->code = $this->generateCopyCode($program->code);
        $duplicate->save();

        return redirect()->route('admin.programs.edit', $duplicate)
            ->with('success', 'Дубликат программы создан. Проверьте и обновите данные перед публикацией.');
    }

    /**
     * Сформировать уникальное имя с пометкой копии
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
     * Сформировать код копии, если исходный код присутствует
     */
    private function generateCopyCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $base = Str::limit($code, 45, '');
        $candidate = $base . '-copy';
        $counter = 2;

        while (Program::where('code', $candidate)->exists()) {
            $candidate = $base . '-copy' . $counter;
            $counter++;
        }

        return Str::limit($candidate, 50, '');
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
