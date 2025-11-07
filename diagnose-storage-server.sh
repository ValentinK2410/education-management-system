#!/bin/bash

# Скрипт для диагностики storage - запускать НА СЕРВЕРЕ
# Скопируйте этот скрипт на сервер и выполните: bash diagnose-storage-server.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "=== 1. Проверка симлинка ==="
if [ -L public/storage ]; then
    echo "✅ Симлинк существует"
    echo "Информация о симлинке:"
    ls -la public/storage
    echo ""
    echo "Куда указывает:"
    readlink public/storage
    echo ""
    echo "Полный путь:"
    readlink -f public/storage
    echo ""
    
    # Проверяем, существует ли целевая директория
    TARGET=$(readlink -f public/storage)
    if [ -d "$TARGET" ]; then
        echo "✅ Целевая директория существует: $TARGET"
    else
        echo "❌ Целевая директория НЕ существует: $TARGET"
    fi
else
    echo "❌ Симлинк НЕ существует"
fi

echo ""
echo "=== 2. Проверка файлов ==="
if [ -d storage/app/public/avatars ]; then
    echo "✅ Директория avatars существует"
    echo "Файлы в avatars:"
    ls -la storage/app/public/avatars/ | head -5
    TEST_FILE=$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
    if [ -n "$TEST_FILE" ]; then
        echo ""
        echo "Тестовый файл: $TEST_FILE"
        echo "Размер файла:"
        ls -lh storage/app/public/avatars/$TEST_FILE
    fi
else
    echo "❌ Директория avatars НЕ существует"
fi

echo ""
echo "=== 3. Проверка доступности через симлинк ==="
TEST_FILE=$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
if [ -n "$TEST_FILE" ]; then
    if [ -f "public/storage/avatars/$TEST_FILE" ]; then
        echo "✅ Файл доступен через симлинк: public/storage/avatars/$TEST_FILE"
        ls -lh "public/storage/avatars/$TEST_FILE"
    else
        echo "❌ Файл НЕ доступен через симлинк: public/storage/avatars/$TEST_FILE"
        echo "Проверяем, что находится в public/storage:"
        ls -la public/storage/ 2>/dev/null | head -5
    fi
fi

echo ""
echo "=== 4. Проверка прав доступа ==="
echo "Права на public/storage:"
ls -ld public/storage
echo ""
echo "Права на storage/app/public:"
ls -ld storage/app/public
echo ""
echo "Права на storage/app/public/avatars:"
ls -ld storage/app/public/avatars 2>/dev/null || echo "Директория не найдена"

echo ""
echo "=== 5. Проверка конфигурации Nginx ==="
if [ -f /etc/nginx/sites-available/m.dekan.pro ]; then
    echo "Проверяем root директорию:"
    grep "root" /etc/nginx/sites-available/m.dekan.pro | head -3
    echo ""
    echo "Проверяем, есть ли location для /storage:"
    if grep -q "location /storage" /etc/nginx/sites-available/m.dekan.pro; then
        echo "✅ location /storage найден:"
        grep -A 5 "location /storage" /etc/nginx/sites-available/m.dekan.pro
    else
        echo "❌ location /storage НЕ найден"
        echo "Нужно добавить в конфигурацию Nginx:"
        echo ""
        echo "location /storage {"
        echo "    alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;"
        echo "    try_files \$uri \$uri/ =404;"
        echo "}"
    fi
else
    echo "❌ Конфигурация Nginx не найдена"
fi

echo ""
echo "=== 6. Тест доступности через веб ==="
TEST_FILE=$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
if [ -n "$TEST_FILE" ]; then
    echo "Проверяем доступность файла:"
    echo "URL должен быть: http://m.dekan.pro/storage/avatars/$TEST_FILE"
    echo ""
    echo "Проверяем через curl (локально):"
    curl -I "http://localhost/storage/avatars/$TEST_FILE" 2>/dev/null | head -5 || echo "Не удалось проверить через curl"
fi

echo ""
echo "=== 7. Рекомендации ==="
echo "Если симлинк указывает неправильно, выполните:"
echo "  rm public/storage"
echo "  php artisan storage:link"
echo ""
echo "Если location /storage отсутствует в Nginx, добавьте его и перезагрузите Nginx:"
echo "  nginx -t"
echo "  systemctl reload nginx"














