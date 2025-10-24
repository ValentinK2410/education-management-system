<?php
// Простая диагностика Laravel приложения
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Диагностика Laravel - m.dekan.pro</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .status-card {
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid;
        }
        .success { background: #d4edda; border-color: #28a745; }
        .error { background: #f8d7da; border-color: #dc3545; }
        .warning { background: #fff3cd; border-color: #ffc107; }
        .info { background: #d1ecf1; border-color: #17a2b8; }
        
        .check-item {
            margin: 10px 0;
            padding: 8px 12px;
            background: rgba(255,255,255,0.7);
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        .icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
        .ok { color: #28a745; }
        .error-icon { color: #dc3545; }
        .warning-icon { color: #ffc107; }
        
        .command-box {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        
        .timestamp {
            text-align: center;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        
        .refresh-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
            transition: background 0.3s;
        }
        .refresh-btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Диагностика Laravel</h1>
        <div class="timestamp">Время проверки: <?php echo date('d.m.Y H:i:s'); ?></div>
        
        <div class="status-grid">
            <div class="status-card success">
                <h3>✅ Базовые проверки</h3>
                <div class="check-item">
                    <span class="icon ok">✓</span>
                    PHP версия: <?php echo PHP_VERSION; ?>
                </div>
                <div class="check-item">
                    <span class="icon ok">✓</span>
                    Сервер: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно'; ?>
                </div>
                <div class="check-item">
                    <span class="icon ok">✓</span>
                    Время работы: <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
            
            <div class="status-card warning">
                <h3>⚠️ Файловая система</h3>
                <?php
                $checks = [
                    'storage/' => is_writable(__DIR__ . '/../storage/'),
                    'bootstrap/cache/' => is_writable(__DIR__ . '/../bootstrap/cache/'),
                    'storage/logs/' => is_writable(__DIR__ . '/../storage/logs/'),
                ];
                
                foreach ($checks as $path => $writable) {
                    $icon = $writable ? 'ok' : 'error-icon';
                    $text = $writable ? '✓' : '✗';
                    $status = $writable ? 'Доступен' : 'Нет доступа';
                    echo "<div class='check-item'><span class='icon $icon'>$text</span> $path - $status</div>";
                }
                ?>
            </div>
            
            <div class="status-card info">
                <h3>📁 Структура проекта</h3>
                <?php
                $paths = [
                    'app/' => is_dir(__DIR__ . '/../app/'),
                    'config/' => is_dir(__DIR__ . '/../config/'),
                    'database/' => is_dir(__DIR__ . '/../database/'),
                    'resources/views/' => is_dir(__DIR__ . '/../resources/views/'),
                    'routes/' => is_dir(__DIR__ . '/../routes/'),
                ];
                
                foreach ($paths as $path => $exists) {
                    $icon = $exists ? 'ok' : 'error-icon';
                    $text = $exists ? '✓' : '✗';
                    $status = $exists ? 'Существует' : 'Отсутствует';
                    echo "<div class='check-item'><span class='icon $icon'>$text</span> $path - $status</div>";
                }
                ?>
            </div>
            
            <div class="status-card error">
                <h3>❌ Laravel проверки</h3>
                <div class="check-item">
                    <span class="icon error-icon">✗</span>
                    Laravel не загружен (статический режим)
                </div>
                <div class="check-item">
                    <span class="icon error-icon">✗</span>
                    База данных недоступна
                </div>
                <div class="check-item">
                    <span class="icon error-icon">✗</span>
                    Маршруты не работают
                </div>
            </div>
        </div>
        
        <div class="status-card info">
            <h3>🛠️ Команды для исправления</h3>
            <p>Выполните эти команды на сервере:</p>
            
            <div class="command-box">
# 1. Обновить код
git pull origin main

# 2. Исправить права доступа
sudo chmod -R 775 storage/
sudo chmod -R 775 bootstrap/cache/
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/

# 3. Создать файл логов
sudo touch storage/logs/laravel.log
sudo chmod 664 storage/logs/laravel.log
sudo chown www-data:www-data storage/logs/laravel.log

# 4. Очистить кэш Laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# 5. Перезапустить PHP-FPM
sudo systemctl restart php8.4-fpm
            </div>
        </div>
        
        <a href="?" class="refresh-btn">🔄 Обновить диагностику</a>
        
        <div class="status-card">
            <h3>🔗 Тестовые ссылки</h3>
            <div class="check-item">
                <a href="/" target="_blank">🏠 Главная страница</a>
            </div>
            <div class="check-item">
                <a href="/admin/dashboard" target="_blank">⚙️ Админ панель</a>
            </div>
            <div class="check-item">
                <a href="/seminary-style" target="_blank">🎓 Страница семинарии</a>
            </div>
            <div class="check-item">
                <a href="/status-check.html" target="_blank">📊 Статус проверка</a>
            </div>
        </div>
    </div>
    
    <script>
        // Автоматическое обновление каждые 60 секунд
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
