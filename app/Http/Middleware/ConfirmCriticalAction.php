<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для подтверждения критических операций
 *
 * Требует подтверждения для операций удаления и массовых операций
 */
class ConfirmCriticalAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, требуется ли подтверждение
        if ($request->method() === 'DELETE' || $request->has('_confirm_action')) {
            // Если уже подтверждено, пропускаем
            if ($request->has('_confirm_action') && $request->input('_confirm_action') === 'yes') {
                return $next($request);
            }

            // Для DELETE запросов проверяем наличие подтверждения
            if ($request->method() === 'DELETE') {
                // Если это AJAX запрос, проверяем заголовок
                if ($request->ajax() || $request->wantsJson()) {
                    if (!$request->hasHeader('X-Confirm-Action') || 
                        $request->header('X-Confirm-Action') !== 'yes') {
                        return response()->json([
                            'error' => 'Требуется подтверждение действия',
                            'requires_confirmation' => true
                        ], 422);
                    }
                } else {
                    // Для обычных запросов проверяем параметр
                    if (!$request->has('_confirm') || $request->input('_confirm') !== 'yes') {
                        // Возвращаем ошибку или редирект на страницу подтверждения
                        return redirect()->back()
                            ->with('error', 'Для выполнения этого действия требуется подтверждение.')
                            ->with('requires_confirmation', true);
                    }
                }
            }
        }

        return $next($request);
    }
}

