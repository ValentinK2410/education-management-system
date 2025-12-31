<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки прав пользователей
 *
 * Проверяет, имеет ли авторизованный пользователь необходимое право
 * для доступа к определенным маршрутам административной панели.
 */
class CheckPermission
{
    /**
     * Обработать входящий запрос
     *
     * Проверяет аутентификацию пользователя и его право.
     * Если пользователь не авторизован, перенаправляет на страницу входа.
     * Если у пользователя нет необходимого права, возвращает ошибку 403.
     *
     * @param Request $request Входящий HTTP запрос
     * @param Closure $next Следующий middleware в цепочке
     * @param string $permission Требуемое право для доступа
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Проверка авторизации пользователя
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Проверка наличия необходимого права у пользователя
        if (!auth()->user()->hasPermission($permission)) {
            abort(403, 'У вас нет прав для выполнения этого действия.');
        }

        return $next($request);
    }
}

