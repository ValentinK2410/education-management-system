<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Контроллер для переключения между пользователями и ролями
 *
 * Позволяет администратору:
 * - Переключаться между ролями
 * - Входить под другим пользователем
 * - Видеть интерфейс так, как его видит другой пользователь
 */
class UserSwitchController extends Controller
{
    /**
     * Переключиться на другого пользователя
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchToUser(Request $request, User $user)
    {
        // Проверяем, что текущий пользователь - администратор
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Только администраторы могут переключаться между пользователями');
        }

        // Сохраняем информацию об оригинальном пользователе
        Session::put('original_user_id', Auth::id());
        Session::put('is_switched', true);

        // Входим под выбранным пользователем
        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Вы вошли под пользователем: ' . $user->name);
    }

    /**
     * Вернуться к своему аккаунту
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchBack(Request $request)
    {
        if (!Session::has('original_user_id')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Нет информации об оригинальном пользователе');
        }

        $originalUserId = Session::get('original_user_id');
        $originalUser = User::findOrFail($originalUserId);

        // Проверяем, что это действительно администратор
        if (!$originalUser->hasRole('admin')) {
            Session::forget(['original_user_id', 'is_switched']);
            return redirect()->route('admin.dashboard')
                ->with('error', 'Ошибка возврата к аккаунту');
        }

        // Возвращаемся к оригинальному пользователю
        Auth::login($originalUser);

        // Очищаем сессию переключения
        Session::forget(['original_user_id', 'is_switched']);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Вы вернулись к своему аккаунту');
    }

    /**
     * Переключиться на роль (временно заменяем все роли на выбранную)
     *
     * @param Request $request
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchToRole(Request $request, Role $role)
    {
        // Проверяем, что текущий пользователь - реальный администратор
        $isRealAdmin = false;
        $originalUserId = Session::get('original_user_id');
        
        // Если переключены на пользователя, проверяем оригинального
        if ($originalUserId) {
            $originalUser = User::find($originalUserId);
            $isRealAdmin = $originalUser && $originalUser->hasRole('admin');
        } elseif (!session('role_switched') && !session('is_switched')) {
            $isRealAdmin = Auth::user()->hasRole('admin');
        }
        
        if (!$isRealAdmin) {
            abort(403, 'Только администраторы могут переключаться между ролями');
        }

        // Сохраняем текущие роли пользователя (если еще не сохранены)
        if (!Session::has('original_roles')) {
            Session::put('original_roles', $currentUser->roles->pluck('id')->toArray());
        }

        // ВРЕМЕННО заменяем все роли на выбранную роль
        // Это позволит видеть интерфейс так, как его видит пользователь с этой ролью
        $currentUser->roles()->sync([$role->id]);

        Session::put('role_switched', true);
        Session::put('switched_role_id', $role->id);
        Session::put('switched_role_slug', $role->slug);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Вы переключились на роль: ' . $role->name . '. Теперь вы видите интерфейс как пользователь с этой ролью.');
    }

    /**
     * Вернуться к оригинальным ролям
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchRoleBack(Request $request)
    {
        if (!Session::has('original_roles')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Нет информации об оригинальных ролях');
        }

        $originalRoles = Session::get('original_roles');

        // Восстанавливаем оригинальные роли
        Auth::user()->roles()->sync($originalRoles);

        // Очищаем сессию переключения ролей
        Session::forget(['original_roles', 'role_switched', 'switched_role_id']);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Вы вернулись к своим оригинальным ролям');
    }

    /**
     * Получить список пользователей для переключения
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        // Проверяем, является ли пользователь реальным админом
        $isRealAdmin = false;
        if (session('original_user_id')) {
            $originalUser = User::find(session('original_user_id'));
            $isRealAdmin = $originalUser && $originalUser->hasRole('admin');
        } elseif (!session('role_switched') && !session('is_switched')) {
            $isRealAdmin = Auth::user()->hasRole('admin');
        }
        
        if (!$isRealAdmin) {
            abort(403);
        }

        $search = $request->input('search', '');

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->with('roles')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map(function ($role) {
                        return ['name' => $role->name];
                    })
                ];
            });

        return response()->json($users);
    }
}
