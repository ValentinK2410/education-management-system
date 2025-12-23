<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Определяем, какой layout использовать в зависимости от маршрута
        if (request()->routeIs('profile.*')) {
            // Публичный маршрут - используем публичное представление
            return view('public.profile.show', compact('user'));
        }

        // Админский маршрут - используем админское представление
        return view('admin.profile.show', compact('user'));
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
