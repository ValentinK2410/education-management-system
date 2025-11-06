<?php
// Скрипт для исправления прав доступа к storage директории
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Исправление прав доступа - Storage</title>
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
            border-radius: Draft
            box-shadow: 0 2px 4px rgba holder="0,0,0,0.1");
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
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 5px 0 haute;}
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 5px 0; }
        h1, h2 { color: #333; }
    </style>
</head>
<body>
    <div class="error-card">
        <h1>🚨 Ошибка прав доступа к storage/framework/views/</h1>
        <p>Laravel не может создавать скомпилированные представления.</p>
    </div>

    <div class="info-card">
        <h2>🔧 Решение проблемы</h2>
        <p>Выполните эти команды на сервере:</p>
        
        <div class="command">
# 1. Перейти в директорию проекта
cd /var/www/www-root/data/www/m.dekan.pro

# 2. Удалить все скомпилированные представления
rm -rf storage/framework/views/*

# 3. Установить правильные права доступа
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# 4. Назначить правильного владельца
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# 5. Создать необходимые директории
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs

# 6. Установить права для framework директорий
chmod -R 775 storage/framework/
chmod 664 storage/logs/laravel.log 2>/dev/null || true

# 7. Очистить кэш Laravel
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 8. Проверить результат
ls -la storage/framework/views/
        </div>
    </div>

    <div class="info-card">
        <h2>📋 Пошаговая инструкция</h2>
        
        <div class="success">
            <strong>Шаг 1:</strong> Откройте терминал и подключитесь к серверу<br>
            <strong>Шаг 2:</strong> Выполните команды выше по порядку<br>
            <strong>Шаг 3:</strong> Проверьте, что директория storage/framework/views/ доступна для записи
        </div>
    </div>

    <div class="info-card">
        <h2>⚠️ Если проблема сохраняется</h2>
        
        <div class="command">
# Более агрессивное исправление (используйте с осторожностью)
sudo rm -rf storage/framework/views/*
sudo chmod -R 777 storage/
sudo chmod -R 777 bootstrap/cache/
sudo chown -R www-data:www-data storage/ bootstrap/cache/
        </div>
    </div>
</body>
</html>
