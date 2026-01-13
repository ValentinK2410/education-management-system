<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для Single Sign-On (SSO) в Moodle
 */
class MoodleSsoController extends Controller
{
    /**
     * Перенаправление на Moodle с автоматическим входом
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('Moodle SSO: Attempt to access without authentication');
            return redirect()->route('login')->with('error', 'Необходима авторизация');
        }

        $moodleUrl = config('services.moodle.url');
        
        if (empty($moodleUrl)) {
            Log::error('Moodle SSO: Moodle URL not configured');
            return redirect()->back()->with('error', 'Moodle не настроен. Обратитесь к администратору.');
        }

        // Проверяем, есть ли у пользователя moodle_user_id
        if (empty($user->moodle_user_id)) {
            Log::info('Moodle SSO: User does not have moodle_user_id, trying to find in Moodle', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            // Пытаемся найти пользователя в Moodle по email
            try {
                $moodleApiService = new MoodleApiService();
                $moodleUser = $moodleApiService->getUserByEmail($user->email);
                
                if ($moodleUser && isset($moodleUser['id'])) {
                    $user->moodle_user_id = $moodleUser['id'];
                    $user->save();
                    Log::info('Moodle SSO: Found moodle_user_id for user', [
                        'user_id' => $user->id,
                        'moodle_user_id' => $user->moodle_user_id
                    ]);
                } else {
                    Log::warning('Moodle SSO: User not found in Moodle', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                    return redirect()->back()->with('error', 'Пользователь не найден в Moodle. Обратитесь к администратору.');
                }
            } catch (\Exception $e) {
                Log::error('Moodle SSO: Failed to find user in Moodle', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->with('error', 'Ошибка при проверке пользователя в Moodle. Обратитесь к администратору.');
            }
        }

        // Получаем URL для перенаправления после входа (из параметра или главная страница Moodle)
        $redirectUrl = $request->get('redirect', '/');
        
        // Формируем параметры для передачи в Moodle
        // Используем только email и moodle_user_id (без токена для упрощения)
        $queryParams = [
            'email' => $user->email,
            'moodle_user_id' => $user->moodle_user_id,
            'redirect' => $redirectUrl,
        ];
        
        // Формируем URL с параметрами
        $queryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        $ssoUrl = rtrim($moodleUrl, '/') . '/moodle-sso-from-laravel.php?' . $queryString;

        // Логируем информацию для отладки
        Log::info('Moodle SSO: Формирование URL для редиректа', [
            'user_id' => $user->id,
            'email' => $user->email,
            'moodle_user_id' => $user->moodle_user_id,
            'redirect_url' => $redirectUrl,
            'moodle_url' => $moodleUrl,
            'sso_url' => $ssoUrl,
            'sso_url_length' => strlen($ssoUrl),
        ]);

        // Используем redirect()->away() для внешнего редиректа
        return redirect()->away($ssoUrl);
    }

}
