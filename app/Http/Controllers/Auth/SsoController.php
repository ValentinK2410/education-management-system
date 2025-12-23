<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для Single Sign-On (SSO) из WordPress
 */
class SsoController extends Controller
{
    /**
     * Обработка SSO входа из WordPress
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $token = $request->query('token');

        if (empty($token)) {
            Log::warning('SSO login attempt without token', ['ip' => $request->ip()]);
            return redirect()->route('login')->with('error', 'Токен не предоставлен');
        }

        // Проверяем токен через WordPress API
        $wordpressUrl = config('services.wordpress.url');
        $ssoApiKey = config('services.wordpress.sso_api_key');

        if (empty($wordpressUrl) || empty($ssoApiKey)) {
            Log::error('SSO: WordPress URL or API key not configured', [
                'wordpress_url' => $wordpressUrl ?: 'empty',
                'sso_api_key' => $ssoApiKey ? 'set' : 'empty'
            ]);
            return redirect()->route('login')->with('error', 'SSO не настроен');
        }

        // Вызываем WordPress API для проверки токена
        $response = Http::timeout(10)->get(rtrim($wordpressUrl, '/') . '/wp-admin/admin-ajax.php', [
            'action' => 'verify_sso_token',
            'token' => $token,
            'service' => 'laravel',
            'api_key' => $ssoApiKey,
        ]);

        if (!$response->successful()) {
            Log::warning('SSO: Failed to verify token with WordPress', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return redirect()->route('login')->with('error', 'Не удалось проверить токен');
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            Log::warning('SSO: Invalid token', ['response' => $data]);
            return redirect()->route('login')->with('error', 'Недействительный токен');
        }

        $userData = $data['data'];

        // Находим пользователя по email
        $user = User::where('email', $userData['email'])->first();

        if (!$user) {
            Log::warning('SSO: User not found in Laravel', ['email' => $userData['email']]);
            return redirect()->route('login')->with('error', 'Пользователь не найден');
        }

        // Автоматически входим пользователя
        Auth::login($user, true); // true = запомнить пользователя

        Log::info('SSO: User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        // Перенаправляем всех пользователей в админ панель
        return redirect()->intended(route('admin.dashboard'));
    }
}

