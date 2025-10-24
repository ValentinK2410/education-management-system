<?php
// Простая диагностика ошибки 500
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Диагностика ошибки 500 - m.dekan.pro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .error-card {
            background: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .command {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .status {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        h1, h2 { color: #333; }
        .timestamp { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="error-card">
        <h1>🚨 Ошибка 500 Internal Server Error</h1>
        <p>Обнаружена критическая ошибка сервера. Выполняем диагностику...</p>
        <div class="timestamp">Время: <?php echo date('d.m.Y H:i:s'); ?></div>
    </div>

    <div class="info-card">
        <h2>🔍 Базовая диагностика</h2>
        
        <div class="status success">
            ✅ PHP работает: версия <?php echo PHP_VERSION; ?>
        </div>
        
        <div class="status success">
            ✅ Сервер: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно'; ?>
        </div>
        
        <div class="status success">
            ✅ Директория: <?php echo __DIR__; ?>
        </div>
        
        <?php
        // Проверка прав доступа
        $storage_writable = is_writable(__DIR__ . '/../storage/');
        $cache_writable = is_writable(__DIR__ . '/../bootstrap/cache/');
        $logs_writable = is_writable(__DIR__ . '/../storage/logs/');
        
        echo '<div class="status ' . ($storage_writable ? 'success' : 'error') . '">';
        echo ($storage_writable ? '✅' : '❌') . ' Storage доступен для записи';
        echo '</div>';
        
        echo '<div class="status ' . ($cache_writable ? 'success' : 'error') . '">';
        echo ($cache_writable ? '✅' : '❌') . ' Bootstrap/cache доступен для записи';
        echo '</div>';
        
        echo '<div class="status ' . ($logs_writable ? 'success' : 'error') . '">';
        echo ($logs_writable ? '✅' : '❌') . ' Storage/logs доступен для записи';
        echo '</div>';
        ?>
    </div>

    <div class="info-card">
        <h2>📁 Проверка файлов Laravel</h2>
        
        <?php
        $files_to_check = [
            '../artisan' => 'Artisan файл',
            '../composer.json' => 'Composer конфигурация',
            '../.env' => 'Переменные окружения',
            '../bootstrap/app.php' => 'Bootstrap приложения',
            '../app/Http/Kernel.php' => 'HTTP Kernel',
        ];
        
        foreach ($files_to_check as $file => $description) {
            $exists = file_exists(__DIR__ . '/' . $file);
            echo '<div class="status ' . ($exists ? 'success' : 'error') . '">';
            echo ($exists ? '✅' : '❌') . ' ' . $description;
            echo '</div>';
        }
        ?>
    </div>

    <div class="info-card">
        <h2>🔧 Команды для исправления</h2>
        <p>Выполните эти команды на сервере в директории проекта:</p>
        
        <div class="command">
# 1. Обновить код
git pull origin main
        </div>
        
        <div class="command">
# 2. Исправить права доступа
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
        </div>
        
        <div class="command">
# 3. Создать файл логов
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
        </div>
        
        <div class="command">
# 4. Очистить кэш
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
        </div>
        
        <div class="command">
# 5. Перезапустить PHP-FPM
sudo systemctl restart php8.4-fpm
        </div>
    </div>

    <div class="info-card">
        <h2>📊 Проверка логов</h2>
        <p>Проверьте логи ошибок:</p>
        
        <div class="command">
# Проверить логи Laravel
tail -f storage/logs/laravel.log
        </div>
        
        <div class="command">
# Проверить логи PHP-FPM
sudo tail -f /var/log/php8.4-fpm.log
        </div>
        
        <div class="command">
# Проверить логи Nginx
sudo tail -f /var/log/nginx/error.log
        </div>
    </div>

    <div class="info-card">
        <h2>🔗 Тестовые ссылки</h2>
        <p>Попробуйте эти ссылки после исправления:</p>
        <ul>
            <li><a href="/" target="_blank">Главная страница</a></li>
            <li><a href="/admin/dashboard" target="_blank">Админ панель</a></li>
            <li><a href="/seminary-style" target="_blank">Страница семинарии</a></li>
            <li><a href="/laravel-diagnostic.php" target="_blank">Детальная диагностика</a></li>
        </ul>
    </div>

    <script>
        // Автоматическое обновление каждые 30 секунд
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
