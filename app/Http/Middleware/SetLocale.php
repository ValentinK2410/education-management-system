<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Middleware для управления языком приложения
 * 
 * Позволяет переключать язык через URL параметр или сессию
 */
class SetLocale
{
    /**
     * Обработка входящего запроса
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Поддерживаемые языки
        $supportedLocales = ['ru', 'en'];
        
        // Получаем язык из URL параметра
        $locale = $request->get('lang');
        
        // Если язык не указан в URL, берем из сессии
        if (!$locale) {
            $locale = Session::get('locale', 'ru');
        }
        
        // Проверяем, что язык поддерживается
        if (in_array($locale, $supportedLocales)) {
            // Устанавливаем язык в приложении
            App::setLocale($locale);
            
            // Сохраняем язык в сессии
            Session::put('locale', $locale);
        } else {
            // Если язык не поддерживается, используем русский по умолчанию
            App::setLocale('ru');
            Session::put('locale', 'ru');
        }
        
        return $next($request);
    }
}
