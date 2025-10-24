<?php
// Проверка конфигурации веб-сервера
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Проверка конфигурации сервера</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #ffe7e7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #e7ffe7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Проверка конфигурации сервера</h1>
    
    <div class="info">
        <h3>Информация о сервере:</h3>
        <p><strong>PHP версия:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>Сервер:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно'; ?></p>
        <p><strong>Документ рут:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Неизвестно'; ?></p>
        <p><strong>Скрипт:</strong> <?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Неизвестно'; ?></p>
        <p><strong>Время:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="info">
        <h3>Переменные окружения:</h3>
        <pre><?php
        $env_vars = ['APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST'];
        foreach ($env_vars as $var) {
            echo $var . ' = ' . (getenv($var) ?: 'не установлено') . "\n";
        }
        ?></pre>
    </div>
    
    <div class="info">
        <h3>Проверка файлов:</h3>
        <?php
        $files = [
            '../.env' => 'Файл переменных окружения',
            '../artisan' => 'Artisan CLI',
            '../composer.json' => 'Composer конфигурация',
            '../bootstrap/app.php' => 'Bootstrap приложения',
        ];
        
        foreach ($files as $file => $desc) {
            $exists = file_exists(__DIR__ . '/' . $file);
            echo '<p>' . ($exists ? '✅' : '❌') . ' ' . $desc . '</p>';
        }
        ?>
    </div>
    
    <div class="info">
        <h3>Проверка директорий:</h3>
        <?php
        $dirs = [
            '../storage/' => 'Storage директория',
            '../bootstrap/cache/' => 'Bootstrap cache',
            '../storage/logs/' => 'Logs директория',
            '../app/' => 'App директория',
            '../config/' => 'Config директория',
        ];
        
        foreach ($dirs as $dir => $desc) {
            $exists = is_dir(__DIR__ . '/' . $dir);
            $writable = $exists ? is_writable(__DIR__ . '/' . $dir) : false;
            echo '<p>' . ($exists ? '✅' : '❌') . ' ' . $desc;
            if ($exists) {
                echo ' - ' . ($writable ? 'доступен для записи' : 'только чтение');
            }
            echo '</p>';
        }
        ?>
    </div>
    
    <div class="info">
        <h3>Рекомендации:</h3>
        <ol>
            <li>Проверьте права доступа к storage/ и bootstrap/cache/</li>
            <li>Убедитесь, что файл .env существует и настроен</li>
            <li>Проверьте логи ошибок PHP и веб-сервера</li>
            <li>Убедитесь, что PHP-FPM запущен</li>
        </ol>
    </div>
    
    <div class="info">
        <h3>Команды для диагностики:</h3>
        <pre>
# Проверить статус PHP-FPM
sudo systemctl status php8.4-fpm

# Проверить логи PHP-FPM
sudo tail -f /var/log/php8.4-fpm.log

# Проверить логи Nginx
sudo tail -f /var/log/nginx/error.log

# Проверить права доступа
ls -la storage/
ls -la bootstrap/cache/
        </pre>
    </div>
</body>
</html>
