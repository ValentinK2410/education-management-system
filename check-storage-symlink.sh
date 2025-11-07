#!/bin/bash

# Скрипт для детальной проверки симлинка storage на сервере

echo "🔍 Детальная проверка симлинка storage..."

SERVER_USER="root"
SERVER_HOST="m.dekan.pro"
APP_PATH="/var/www/www-root/data/www/m.dekan.pro"

ssh ${SERVER_USER}@${SERVER_HOST} "
    cd ${APP_PATH}
    
    echo '=== Проверка симлинка ==='
    if [ -L public/storage ]; then
        echo '✅ Симлинк существует'
        echo 'Куда указывает:'
        ls -la public/storage
        echo ''
        echo 'Реальный путь:'
        readlink -f public/storage
        echo ''
        echo 'Проверяем, существует ли целевая директория:'
        if [ -d storage/app/public ]; then
            echo '✅ Директория storage/app/public существует'
        else
            echo '❌ Директория storage/app/public НЕ существует'
        fi
    else
        echo '❌ Симлинк НЕ существует'
    fi
    
    echo ''
    echo '=== Проверка файлов ==='
    echo 'Файлы в storage/app/public/avatars/:'
    ls -la storage/app/public/avatars/ 2>/dev/null | head -10 || echo 'Директория не найдена или пуста'
    
    echo ''
    echo '=== Проверка прав доступа ==='
    ls -la public/ | grep storage
    ls -ld storage/app/public/
    ls -ld storage/app/public/avatars/ 2>/dev/null || echo 'Директория avatars не найдена'
    
    echo ''
    echo '=== Проверка через веб-сервер ==='
    echo 'Проверяем, доступен ли файл через веб:'
    TEST_FILE=\$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
    if [ -n \"\$TEST_FILE\" ]; then
        echo \"Тестовый файл: \$TEST_FILE\"
        echo \"Проверяем доступность: http://m.dekan.pro/storage/avatars/\$TEST_FILE\"
    else
        echo 'Нет файлов для тестирования'
    fi
    
    echo ''
    echo '=== Проверка конфигурации Nginx ==='
    echo 'Проверяем, есть ли location для /storage в Nginx:'
    grep -A 5 'location /storage' /etc/nginx/sites-available/m.dekan.pro 2>/dev/null || echo 'Конфигурация не найдена или location /storage не настроен'
"














