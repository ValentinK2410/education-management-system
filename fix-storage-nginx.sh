#!/bin/bash

# Скрипт для проверки и исправления конфигурации Nginx для storage

echo "🔍 Проверяем конфигурацию Nginx для storage..."

SERVER_USER="root"
SERVER_HOST="m.dekan.pro"
NGINX_CONFIG="/etc/nginx/sites-available/m.dekan.pro"

ssh ${SERVER_USER}@${SERVER_HOST} "
    echo '=== Текущая конфигурация Nginx ==='
    cat ${NGINX_CONFIG}
    
    echo ''
    echo '=== Проверка симлинка ==='
    cd /var/www/www-root/data/www/m.dekan.pro
    echo 'Симлинк:'
    ls -la public/storage
    echo ''
    echo 'Реальный путь:'
    readlink -f public/storage
    echo ''
    echo 'Проверка файла:'
    TEST_FILE=\$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
    if [ -n \"\$TEST_FILE\" ]; then
        echo \"Тестовый файл существует: storage/app/public/avatars/\$TEST_FILE\"
        ls -la storage/app/public/avatars/\$TEST_FILE
    fi
"














