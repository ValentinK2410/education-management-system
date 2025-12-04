#!/bin/bash

# Диагностика проблемы с storage

SERVER_USER="root"
SERVER_HOST="m.dekan.pro"
APP_PATH="/var/www/www-root/data/www/m.dekan.pro"

echo "🔍 Диагностика проблемы с storage..."

ssh ${SERVER_USER}@${SERVER_HOST} << 'EOF'
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
    echo "=== 3. Проверка прав доступа ==="
    echo "Права на public/storage:"
    ls -ld public/storage
    echo ""
    echo "Права на storage/app/public:"
    ls -ld storage/app/public
    echo ""
    echo "Права на storage/app/public/avatars:"
    ls -ld storage/app/public/avatars 2>/dev/null || echo "Директория не найдена"
    
    echo ""
    echo "=== 4. Проверка через веб ==="
    TEST_FILE=$(ls storage/app/public/avatars/ 2>/dev/null | head -1)
    if [ -n "$TEST_FILE" ]; then
        echo "Проверяем доступность файла через веб:"
        echo "URL: http://m.dekan.pro/storage/avatars/$TEST_FILE"
        echo ""
        echo "Проверяем через curl (локально):"
        curl -I http://localhost/storage/avatars/$TEST_FILE 2>/dev/null | head -3 || echo "Не удалось проверить через curl"
    fi
    
    echo ""
    echo "=== 5. Проверка конфигурации Nginx ==="
    if [ -f /etc/nginx/sites-available/m.dekan.pro ]; then
        echo "Конфигурация Nginx:"
        echo "---"
        cat /etc/nginx/sites-available/m.dekan.pro
        echo "---"
        echo ""
        echo "Проверяем, есть ли location для /storage:"
        grep -A 10 "location /storage" /etc/nginx/sites-available/m.dekan.pro || echo "❌ location /storage не найден"
    else
        echo "❌ Конфигурация Nginx не найдена"
    fi
    
    echo ""
    echo "=== 6. Проверка через файловую систему ==="
    echo "Путь к файлу через симлинк:"
    if [ -L public/storage ]; then
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
    fi
EOF














