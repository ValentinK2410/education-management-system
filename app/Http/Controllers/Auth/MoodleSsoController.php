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

        // Генерируем временный токен для SSO
        $token = $this->generateSsoToken($user);
        
        // Получаем URL для перенаправления после входа
        $redirectUrl = $request->get('redirect', '/');
        
        // Формируем параметры для передачи в Moodle
        // Важно: используем urlencode для правильного кодирования параметров
        $queryParams = [
            'token' => $token,
            'email' => $user->email, // email не нужно кодировать, http_build_query сделает это автоматически
            'moodle_user_id' => $user->moodle_user_id,
            'redirect' => $redirectUrl,
        ];
        
        // Формируем URL с параметрами
        // Используем http_build_query с правильными настройками для внешних URL
        $queryString = http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        $ssoUrl = rtrim($moodleUrl, '/') . '/moodle-sso-from-laravel.php?' . $queryString;

        Log::info('Moodle SSO: Redirecting user to Moodle', [
            'user_id' => $user->id,
            'email' => $user->email,
            'moodle_user_id' => $user->moodle_user_id,
            'redirect_url' => $redirectUrl,
            'sso_url_length' => strlen($ssoUrl),
            'token_length' => strlen($token),
            'query_string_length' => strlen($queryString),
            'sso_url_preview' => substr($ssoUrl, 0, 300) . (strlen($ssoUrl) > 300 ? '...' : ''),
            'query_params_keys' => array_keys($queryParams)
        ]);

        // Используем redirect()->away() для внешнего редиректа
        // Это гарантирует, что браузер получит полный URL с параметрами
        return redirect()->away($ssoUrl);
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
