<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер для управления пользовательскими настройками
 *
 * Обрабатывает сохранение и загрузку персональных настроек пользователей,
 * таких как тема оформления, настройки интерфейса и другие предпочтения.
 */
class SettingsController extends Controller
{
    /**
     * Сохранить предпочтения темы пользователя
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveThemePreference(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark'
        ]);

        $user = Auth::user();

        // Сохраняем настройку темы в профиле пользователя
        $user->update([
            'theme_preference' => $request->theme
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Настройки темы сохранены'
        ]);
    }

    /**
     * Получить пользовательские настройки
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSettings()
    {
        $user = Auth::user();

        return response()->json([
            'theme' => $user->theme_preference ?? 'light',
            'sidebar_collapsed' => $user->sidebar_collapsed ?? false,
            'notifications_enabled' => $user->notifications_enabled ?? true,
        ]);
    }

    /**
     * Сохранить настройки интерфейса
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveInterfaceSettings(Request $request)
    {
        $request->validate([
            'sidebar_collapsed' => 'boolean',
            'notifications_enabled' => 'boolean'
        ]);

        $user = Auth::user();

        $user->update([
            'sidebar_collapsed' => $request->boolean('sidebar_collapsed'),
            'notifications_enabled' => $request->boolean('notifications_enabled')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Настройки интерфейса сохранены'
        ]);
    }
}
