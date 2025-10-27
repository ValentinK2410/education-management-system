<?php
// Скрипт для исправления прав доступа к логам Laravel
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Исправление прав доступа - Laravel Logs</title>
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="error-card">
        <h1>🚨 Ошибка прав доступа к логам Laravel</h1>
        <p><strong>Проблема:</strong> Laravel не может записать в файл логов из-за недостаточных прав доступа.</p>
        <div class="timestamp">Время: <?php echo date('d.m.Y H:i:s'); ?></div>
    </div>

    <div class="info-card">
        <h2>🔍 Текущее состояние</h2>
        
        <?php
        $log_file = __DIR__ . '/../storage/logs/laravel.log';
        $logs_dir = __DIR__ . '/../storage/logs/';
        $storage_dir = __DIR__ . '/../storage/';
        
        echo '<div class="status ' . (is_dir($storage_dir) ? 'success' : 'error') . '">';
        echo (is_dir($storage_dir) ? '✅' : '❌') . ' Storage директория существует';
        echo '</div>';
        
        echo '<div class="status ' . (is_dir($logs_dir) ? 'success' : 'error') . '">';
        echo (is_dir($logs_dir) ? '✅' : '❌') . ' Logs директория существует';
        echo '</div>';
        
        echo '<div class="status ' . (file_exists($log_file) ? 'success' : 'warning') . '">';
        echo (file_exists($log_file) ? '✅' : '⚠️') . ' Файл laravel.log ' . (file_exists($log_file) ? 'существует' : 'не существует');
        echo '</div>';
        
        if (file_exists($log_file)) {
            $writable = is_writable($log_file);
            echo '<div class="status ' . ($writable ? 'success' : 'error') . '">';
            echo ($writable ? '✅' : '❌') . ' Файл laravel.log доступен для записи';
            echo '</div>';
        }
        
        $logs_writable = is_writable($logs_dir);
        echo '<div class="status ' . ($logs_writable ? 'success' : 'error') . '">';
        echo ($logs_writable ? '✅' : '❌') . ' Logs директория доступна для записи';
        echo '</div>';
        
        $storage_writable = is_writable($storage_dir);
        echo '<div class="status ' . ($storage_writable ? 'success' : 'error') . '">';
        echo ($storage_writable ? '✅' : '❌') . ' Storage директория доступна для записи';
        echo '</div>';
        ?>
    </div>

    <div class="info-card">
        <h2>🛠️ Автоматическое исправление</h2>
        <p>Попытка исправить права доступа через PHP:</p>
        
        <?php
        $fixes_applied = [];
        
        // Создать директории если не существуют
        if (!is_dir($logs_dir)) {
            if (mkdir($logs_dir, 0755, true)) {
                $fixes_applied[] = "✅ Создана директория logs/";
            } else {
                $fixes_applied[] = "❌ Не удалось создать директорию logs/";
            }
        }
        
        // Создать файл логов если не существует
        if (!file_exists($log_file)) {
            if (touch($log_file)) {
                $fixes_applied[] = "✅ Создан файл laravel.log";
            } else {
                $fixes_applied[] = "❌ Не удалось создать файл laravel.log";
            }
        }
        
        // Попытка изменить права доступа
        if (file_exists($log_file)) {
            if (chmod($log_file, 0664)) {
                $fixes_applied[] = "✅ Установлены права 664 для laravel.log";
            } else {
                $fixes_applied[] = "❌ Не удалось изменить права для laravel.log";
            }
        }
        
        if (is_dir($logs_dir)) {
            if (chmod($logs_dir, 0775)) {
                $fixes_applied[] = "✅ Установлены права 775 для logs/";
            } else {
                $fixes_applied[] = "❌ Не удалось изменить права для logs/";
            }
        }
        
        if (is_dir($storage_dir)) {
            if (chmod($storage_dir, 0775)) {
                $fixes_applied[] = "✅ Установлены права 775 для storage/";
            } else {
                $fixes_applied[] = "❌ Не удалось изменить права для storage/";
            }
        }
        
        foreach ($fixes_applied as $fix) {
            echo '<div class="status">' . $fix . '</div>';
        }
        
        if (empty($fixes_applied)) {
            echo '<div class="status warning">⚠️ Автоматическое исправление не требуется или недоступно</div>';
        }
        ?>
    </div>

    <div class="info-card">
        <h2>🔧 Ручное исправление (рекомендуется)</h2>
        <p>Выполните эти команды на сервере в директории проекта:</p>
        
        <div class="command">
# Перейти в директорию проекта
cd /var/www/www-root/data/www/m.dekan.pro
        </div>
        
        <div class="command">
# Создать директории если не существуют
mkdir -p storage/logs
mkdir -p bootstrap/cache
        </div>
        
        <div class="command">
# Установить правильные права доступа
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
        </div>
        
        <div class="command">
# Назначить правильного владельца
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
        </div>
        
        <div class="command">
# Создать файл логов если не существует
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log
        </div>
        
        <div class="command">
# Очистить кэш Laravel
php artisan cache:clear
php artisan config:clear
        </div>
        
        <div class="command">
# Перезапустить PHP-FPM (опционально)
# Попробуйте одну из этих команд:
sudo service php8.4-fpm restart
sudo service php8.3-fpm restart
sudo service php8.2-fpm restart
# Или выполните перезапуск через веб-панель хостинга
        </div>
    </div>

    <div class="info-card">
        <h2>🔍 Проверка после исправления</h2>
        <p>После выполнения команд проверьте:</p>
        
        <div class="command">
# Проверить права доступа
ls -la storage/
ls -la storage/logs/
ls -la bootstrap/cache/
        </div>
        
        <div class="command">
# Проверить логи ошибок
tail -f storage/logs/laravel.log
        </div>
        
        <div class="command">
# Проверить логи Nginx
sudo tail -f /var/log/nginx/error.log
        </div>
    </div>

    <div class="info-card">
        <h2>🔗 Тестовые ссылки</h2>
        <p>Проверьте работу сайта после исправления:</p>
        <a href="/" class="btn">🏠 Главная страница</a>
        <a href="/admin/dashboard" class="btn">⚙️ Админ панель</a>
        <a href="/laravel-test.php" class="btn">🧪 Тест Laravel</a>
        <a href="/server-config-check.php" class="btn">📊 Конфигурация</a>
    </div>

    <script>
        // Автоматическое обновление каждые 30 секунд
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
