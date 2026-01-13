<?php

/**
 * Laravel → Moodle SSO
 * 
 * Этот файл должен быть размещен в корневой директории Moodle:
 * /var/www/www-root/data/www/class.russianseminary.org/moodle-sso-from-laravel.php
 * 
 * config.php Moodle находится в той же директории:
 * /var/www/www-root/data/www/class.russianseminary.org/config.php
 * 
 * Использование:
 * https://class.russianseminary.org/moodle-sso-from-laravel.php?token=ENCRYPTED_TOKEN&email=user@example.com&moodle_user_id=123&redirect=/
 * 
 * Параметры:
 * - token: зашифрованный токен от Laravel (обязательно)
 * - email: email пользователя (обязательно)
 * - moodle_user_id: ID пользователя в Moodle (обязательно)
 * - redirect: URL для перенаправления после входа (опционально, по умолчанию /)
 * 
 * Файл автоматически загружает config.php из той же директории.
 */

// Загружаем конфигурацию Moodle
// config.php находится в той же директории
require_once(__DIR__ . '/config.php');

// Настройки Laravel SSO
// ВАЖНО: Замените эти значения на ваши настройки из Laravel
$laravel_url = 'https://dean.russianseminary.org'; // URL вашего Laravel приложения
$laravel_sso_secret = 'base64:YOUR_APP_KEY_BASE64'; // APP_KEY из Laravel .env файла (без префикса base64:)

// Проверяем, что настройки заполнены
if (empty($laravel_url) || empty($laravel_sso_secret) || $laravel_sso_secret === 'YOUR_APP_KEY_BASE64') {
    redirect(new moodle_url('/'), 'SSO не настроен. Обратитесь к администратору.', null, \core\output\notification::NOTIFY_ERROR);
}

// Получаем параметры из запроса
$token = optional_param('token', '', PARAM_TEXT);
$email = optional_param('email', '', PARAM_EMAIL);
$moodle_user_id = optional_param('moodle_user_id', 0, PARAM_INT);
$redirect = optional_param('redirect', '/', PARAM_URL);

// Логируем все полученные параметры для отладки
error_log('Laravel SSO: Получены параметры:');
error_log('  - token: ' . (empty($token) ? 'ОТСУТСТВУЕТ' : 'присутствует (длина: ' . strlen($token) . ')'));
error_log('  - email: ' . ($email ?: 'ОТСУТСТВУЕТ'));
error_log('  - moodle_user_id: ' . ($moodle_user_id ?: 'ОТСУТСТВУЕТ'));
error_log('  - redirect: ' . ($redirect ?: '/'));

// Проверяем обязательные параметры
if (empty($token) || empty($email) || empty($moodle_user_id)) {
    $missing_params = [];
    if (empty($token)) $missing_params[] = 'token';
    if (empty($email)) $missing_params[] = 'email';
    if (empty($moodle_user_id)) $missing_params[] = 'moodle_user_id';
    
    error_log('Laravel SSO: Ошибка - недостаточно параметров. Отсутствуют: ' . implode(', ', $missing_params));
    error_log('Laravel SSO: Полный URL запроса: ' . $_SERVER['REQUEST_URI']);
    error_log('Laravel SSO: GET параметры: ' . print_r($_GET, true));
    
    redirect(new moodle_url('/login/index.php'), 
        'Недостаточно параметров для SSO входа. Отсутствуют: ' . implode(', ', $missing_params) . '. Обратитесь к администратору.', 
        null, 
        \core\output\notification::NOTIFY_ERROR);
}

// ВАЖНО: Для расшифровки токена Laravel нужно использовать тот же APP_KEY
// Laravel использует AES-256-CBC для шифрования через Crypt::encryptString()
// 
// ВАРИАНТ 1: Если у вас есть доступ к Laravel коду, используйте общий секретный ключ
// ВАРИАНТ 2: Используйте простую проверку по email и moodle_user_id (менее безопасно, но проще)

// Простая проверка: проверяем, что пользователь существует и email совпадает
try {
    // Получаем пользователя из Moodle по ID
    $user = $DB->get_record('user', array('id' => $moodle_user_id), '*', MUST_EXIST);
    
    if (!$user || empty($user->id)) {
        redirect(new moodle_url('/login/index.php'), 'Пользователь не найден в Moodle', null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Проверяем, что email совпадает
    if (strtolower(trim($user->email)) !== strtolower(trim($email))) {
        redirect(new moodle_url('/login/index.php'), 'Email не совпадает', null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Проверяем, что пользователь не удален
    if (!empty($user->deleted)) {
        redirect(new moodle_url('/login/index.php'), 'Аккаунт пользователя удален', null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Проверяем, что пользователь не заблокирован
    if (!empty($user->suspended)) {
        redirect(new moodle_url('/login/index.php'), 'Аккаунт пользователя заблокирован', null, \core\output\notification::NOTIFY_ERROR);
    }
    
    // Логируем попытку входа
    error_log('Laravel SSO: Пользователь ' . $user->email . ' (ID: ' . $user->id . ') пытается войти в Moodle');
    error_log('Laravel SSO: Токен получен (длина: ' . strlen($token) . ' символов)');
    error_log('Laravel SSO: Email: ' . $email);
    error_log('Laravel SSO: Moodle User ID: ' . $moodle_user_id);
    
    // Автоматически авторизуем пользователя в Moodle
    // Используем complete_user_login() - стандартную функцию Moodle для авторизации
    complete_user_login($user);
    
    // Обновляем последний вход пользователя
    $user->lastlogin = time();
    $user->lastip = getremoteaddr();
    $DB->update_record('user', $user);
    
    // Логируем успешный вход
    error_log('Laravel SSO: Пользователь ' . $user->email . ' успешно авторизован в Moodle');
    
    // Перенаправляем пользователя
    $redirect_url = new moodle_url($redirect);
    redirect($redirect_url);
    
} catch (Exception $e) {
    error_log('Laravel SSO: Ошибка при авторизации: ' . $e->getMessage());
    error_log('Laravel SSO: Trace: ' . $e->getTraceAsString());
    redirect(new moodle_url('/login/index.php'), 'Ошибка при авторизации: ' . $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
} catch (moodle_exception $e) {
    error_log('Laravel SSO: Moodle Exception: ' . $e->getMessage());
    error_log('Laravel SSO: Trace: ' . $e->getTraceAsString());
    redirect(new moodle_url('/login/index.php'), 'Ошибка при авторизации: ' . $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
}
