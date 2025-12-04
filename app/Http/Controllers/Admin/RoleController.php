<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Контроллер для управления ролями
 *
 * Обеспечивает CRUD операции для ролей в административной панели.
 * Управляет ролями и их разрешениями.
 */
class RoleController extends Controller
{
    /**
     * Отобразить список всех ролей
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $roles = Role::with('permissions', 'users')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Показать форму для создания новой роли
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Сохранить новую роль в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Генерация слага, если не указан
        $slug = $request->slug ?: Str::slug($request->name);

        // Проверка уникальности слага
        if (Role::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        // Создание новой роли
        $role = Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        // Назначение разрешений роли
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно создана.');
    }

    /**
     * Отобразить конкретную роль
     *
     * @param Role $role
     * @return \Illuminate\View\View
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Показать форму для редактирования роли
     *
     * @param Role $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Обновить роль в базе данных
     *
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Role $role)
    {
        // Валидация входящих данных
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Генерация слага, если не указан
        $slug = $request->slug ?: Str::slug($request->name);

        // Проверка уникальности слага (кроме текущей роли)
        if (Role::where('slug', $slug)->where('id', '!=', $role->id)->exists()) {
            $slug = $slug . '-' . time();
        }

        // Обновление роли
        $role->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        // Обновление разрешений роли
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно обновлена.');
    }

    /**
     * Удалить роль из базы данных
     *
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        // Проверка, что роль не является системной (опционально)
        // Можно добавить проверку на системные роли, которые нельзя удалять

        // Удаление связей с разрешениями
        $role->permissions()->detach();

        // Удаление роли
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Роль успешно удалена.');
    }
}
