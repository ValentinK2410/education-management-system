<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки ролей пользователей
 *
 * Проверяет, имеет ли авторизованный пользователь необходимую роль
 * для доступа к определенным маршрутам административной панели.
 */
class CheckRole
{
    /**
     * Обработать входящий запрос
     *
     * Проверяет аутентификацию пользователя и его роль.
     * Если пользователь не авторизован, перенаправляет на страницу входа.
     * Если у пользователя нет необходимой роли, возвращает ошибку 403.
     *
     * @param Request $request Входящий HTTP запрос
     * @param Closure $next Следующий middleware в цепочке
     * @param string $role Требуемая роль для доступа
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Проверка авторизации пользователя
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Проверка наличия необходимой роли у пользователя
        if (!auth()->user()->hasRole($role)) {
            abort(403, 'Недостаточно прав доступа.');
        }

        return $next($request);
    }
}
