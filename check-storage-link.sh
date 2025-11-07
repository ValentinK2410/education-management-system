#!/bin/bash

# Скрипт для проверки и создания симлинка storage на сервере

echo "🔍 Проверяем симлинк storage..."

SERVER_USER="root"
SERVER_HOST="m.dekan.pro"
APP_PATH="/var/www/www-root/data/www/m.dekan.pro"

# Проверяем существование симлинка
echo "Проверяем существование public/storage..."
ssh ${SERVER_USER}@${SERVER_HOST} "
    cd ${APP_PATH}
    if [ -L public/storage ]; then
        echo '✅ Симлинк public/storage существует'
        echo 'Проверяем, куда он указывает:'
        ls -la public/storage
    else
        echo '❌ Симлинк public/storage не найден'
        echo 'Создаем симлинк...'
        php artisan storage:link
        if [ -L public/storage ]; then
            echo '✅ Симлинк успешно создан'
        else
            echo '❌ Ошибка при создании симлинка'
            echo 'Пробуем создать вручную...'
            ln -s ../storage/app/public public/storage
            if [ -L public/storage ]; then
                echo '✅ Симлинк создан вручную'
            else
                echo '❌ Не удалось создать симлинк'
            fi
        fi
    fi
    
    echo ''
    echo 'Проверяем права доступа:'
    ls -la public/ | grep storage
    ls -la storage/app/public/ | head -5
    
    echo ''
    echo 'Проверяем наличие файлов в avatars:'
    ls -la storage/app/public/avatars/ | head -5
"














