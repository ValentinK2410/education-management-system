#!/bin/bash

# Скрипт для просмотра текущей конфигурации Nginx

NGINX_CONFIG="/etc/nginx/sites-available/default"

echo "📋 Текущая конфигурация Nginx: $NGINX_CONFIG"
echo "=========================================="
echo ""

if [ -f "$NGINX_CONFIG" ]; then
    cat "$NGINX_CONFIG"
else
    echo "❌ Файл не найден: $NGINX_CONFIG"
    echo ""
    echo "Доступные конфигурационные файлы:"
    ls -la /etc/nginx/sites-available/ 2>/dev/null || echo "Директория не найдена"
fi
