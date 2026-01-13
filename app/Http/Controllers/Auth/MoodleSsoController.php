<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MoodleApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

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

        // Генерируем временный токен для SSO
        $token = $this->generateSsoToken($user);
        
        // Формируем URL для автоматического входа в Moodle
        $ssoUrl = rtrim($moodleUrl, '/') . '/auth/sso/login?' . http_build_query([
            'token' => $token,
            'email' => $user->email,
            'redirect' => $request->get('redirect', '/'),
        ]);

        Log::info('Moodle SSO: Redirecting user to Moodle', [
            'user_id' => $user->id,
            'email' => $user->email,
            'moodle_user_id' => $user->moodle_user_id
        ]);

        return redirect($ssoUrl);
    }

    /**
     * Генерация SSO токена для пользователя
     *
     * @param User $user
     * @return string
     */
    private function generateSsoToken(User $user): string
    {
        // Создаем токен на основе данных пользователя и времени
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'moodle_user_id' => $user->moodle_user_id,
            'timestamp' => now()->timestamp,
            'expires_at' => now()->addMinutes(5)->timestamp, // Токен действителен 5 минут
        ];

        // Шифруем токен
        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Обработка обратного вызова от Moodle (если требуется)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        // Если Moodle поддерживает обратный вызов, обрабатываем здесь
        $token = $request->query('token');
        
        if ($token) {
            try {
                $payload = json_decode(Crypt::decryptString($token), true);
                
                if (isset($payload['expires_at']) && $payload['expires_at'] < now()->timestamp) {
                    Log::warning('Moodle SSO: Token expired', ['payload' => $payload]);
                    return redirect()->route('admin.dashboard')->with('error', 'Токен истек');
                }

                Log::info('Moodle SSO: Callback received', ['payload' => $payload]);
                
                return redirect()->route('admin.dashboard')->with('success', 'Успешный вход в Moodle');
            } catch (\Exception $e) {
                Log::error('Moodle SSO: Failed to decrypt token', [
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('admin.dashboard')->with('error', 'Ошибка обработки токена');
            }
        }

        return redirect()->route('admin.dashboard');
    }
}
