<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Контроллер для управления учебными заведениями
 *
 * Обеспечивает CRUD операции для учебных заведений в административной панели.
 * Включает загрузку и управление логотипами учебных заведений.
 */
class InstitutionController extends Controller
{
    /**
     * Отобразить список всех учебных заведений
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $institutions = Institution::with('programs')->paginate(15);
        return view('admin.institutions.index', compact('institutions'));
    }

    /**
     * Показать форму для создания нового учебного заведения
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.institutions.create');
    }

    /**
     * Сохранить новое учебное заведение в базе данных
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
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        // Обработка загрузки логотипа
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('institutions', 'public');
        }

        Institution::create($data);

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно создано.');
    }

    /**
     * Отобразить конкретное учебное заведение
     *
     * @param Institution $institution
     * @return \Illuminate\View\View
     */
    public function show(Institution $institution)
    {
        $institution->load('programs.courses');
        return view('admin.institutions.show', compact('institution'));
    }

    /**
     * Показать форму для редактирования учебного заведения
     *
     * @param Institution $institution
     * @return \Illuminate\View\View
     */
    public function edit(Institution $institution)
    {
        return view('admin.institutions.edit', compact('institution'));
    }

    /**
     * Обновить учебное заведение в базе данных
     *
     * @param Request $request
     * @param Institution $institution
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Institution $institution)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();

        // Обработка загрузки нового логотипа
        if ($request->hasFile('logo')) {
            // Удаление старого логотипа
            if ($institution->logo) {
                Storage::disk('public')->delete($institution->logo);
            }
            $data['logo'] = $request->file('logo')->store('institutions', 'public');
        }

        $institution->update($data);

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно обновлено.');
    }

    /**
     * Удалить учебное заведение из базы данных
     *
     * @param Institution $institution
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Institution $institution)
    {
        // Проверяем зависимости перед удалением
        $canDelete = $institution->canBeDeleted();
        
        if (!$canDelete['can_delete']) {
            return redirect()->back()
                ->with('error', $canDelete['message'])
                ->with('dependencies', $canDelete['dependencies']);
        }

        // Используем транзакцию для безопасности
        \DB::transaction(function () use ($institution) {
            // Удаление файла логотипа
            if ($institution->logo) {
                Storage::disk('public')->delete($institution->logo);
            }

            $institution->delete();
        });

        return redirect()->route('admin.institutions.index')
            ->with('success', 'Учебное заведение успешно удалено.');
    }
}
