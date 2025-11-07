#!/bin/bash

# Исправление конфигурации Nginx для работы storage

SERVER_USER="root"
SERVER_HOST="m.dekan.pro"
NGINX_CONFIG="/etc/nginx/sites-available/m.dekan.pro"
APP_PATH="/var/www/www-root/data/www/m.dekan.pro"

echo "🔧 Исправление конфигурации Nginx для storage..."

ssh ${SERVER_USER}@${SERVER_HOST} << 'EOF'
    cd /var/www/www-root/data/www/m.dekan.pro
    
    echo "=== Текущая конфигурация Nginx ==="
    if [ -f /etc/nginx/sites-available/m.dekan.pro ]; then
        cat /etc/nginx/sites-available/m.dekan.pro
    else
        echo "❌ Конфигурация не найдена"
        exit 1
    fi
    
    echo ""
    echo "=== Проверка, есть ли location /storage ==="
    if grep -q "location /storage" /etc/nginx/sites-available/m.dekan.pro; then
        echo "✅ location /storage уже есть"
        grep -A 5 "location /storage" /etc/nginx/sites-available/m.dekan.pro
    else
        echo "❌ location /storage отсутствует"
        echo ""
        echo "Нужно добавить location /storage в конфигурацию Nginx"
        echo "Пример конфигурации:"
        echo ""
        echo "location /storage {"
        echo "    alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;"
        echo "    try_files \$uri \$uri/ =404;"
        echo "}"
    fi
    
    echo ""
    echo "=== Проверка симлинка ==="
    if [ -L public/storage ]; then
        echo "✅ Симлинк существует"
        echo "Куда указывает:"
        readlink -f public/storage
    else
        echo "❌ Симлинк не существует"
    fi
    
    echo ""
    echo "=== Проверка файлов ==="
    TEST_FILE=$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
    if [ -n "$TEST_FILE" ]; then
        echo "Тестовый файл: $TEST_FILE"
        echo "Путь к файлу: storage/app/public/avatars/$TEST_FILE"
        echo "Доступен через симлинк:"
        if [ -f "public/storage/avatars/$TEST_FILE" ]; then
            echo "✅ Да: public/storage/avatars/$TEST_FILE"
        else
            echo "❌ Нет: public/storage/avatars/$TEST_FILE"
        fi
    fi
EOF














