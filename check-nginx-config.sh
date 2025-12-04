#!/bin/bash

# Скрипт для проверки конфигурации Nginx - запускать НА СЕРВЕРЕ

echo "=== Проверка конфигурации Nginx ==="
echo ""

# Показываем текущую конфигурацию
echo "Текущая конфигурация:"
cat /etc/nginx/sites-available/m.dekan.pro

echo ""
echo "=== Проверка location /storage ==="
if grep -A 5 "location /storage" /etc/nginx/sites-available/m.dekan.pro; then
    echo "✅ location /storage найден"
else
    echo "❌ location /storage НЕ найден"
fi

echo ""
echo "=== Проверка синтаксиса ==="
nginx -t

echo ""
echo "=== Проверка файла ==="
cd /var/www/www-root/data/www/m.dekan.pro
TEST_FILE="63Mejz6n4St1hGlCTebjpHHPW7raXXGAHfslbnpp.jpg"
if [ -f "storage/app/public/avatars/$TEST_FILE" ]; then
    echo "✅ Файл существует: storage/app/public/avatars/$TEST_FILE"
    ls -lh "storage/app/public/avatars/$TEST_FILE"
    echo ""
    echo "Проверка через симлинк:"
    if [ -f "public/storage/avatars/$TEST_FILE" ]; then
        echo "✅ Файл доступен через симлинк"
        ls -lh "public/storage/avatars/$TEST_FILE"
    else
        echo "❌ Файл НЕ доступен через симлинк"
    fi
else
    echo "❌ Файл не найден"
fi

echo ""
echo "=== Тест через curl ==="
curl -I "http://localhost/storage/avatars/$TEST_FILE" 2>&1 | head -10














