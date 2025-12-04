#!/bin/bash

# Скрипт для добавления location /storage в конфигурацию Nginx
# Запускать НА СЕРВЕРЕ

NGINX_CONFIG="/etc/nginx/sites-available/m.dekan.pro"
BACKUP_CONFIG="/etc/nginx/sites-available/m.dekan.pro.backup.$(date +%Y%m%d_%H%M%S)"

echo "🔧 Исправление конфигурации Nginx для storage..."

# Создаем резервную копию
cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
echo "✅ Резервная копия создана: $BACKUP_CONFIG"

# Проверяем, есть ли уже location /storage
if grep -q "location /storage" "$NGINX_CONFIG"; then
    echo "⚠️  location /storage уже существует"
    exit 0
fi

# Читаем текущую конфигурацию
CONFIG_CONTENT=$(cat "$NGINX_CONFIG")

# Проверяем, есть ли блок location /
if echo "$CONFIG_CONTENT" | grep -q "location /"; then
    # Добавляем location /storage перед location /
    # Используем sed для вставки перед первым location /
    sed -i '/location \//i\    location /storage {\n        alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;\n        try_files $uri $uri/ =404;\n    }\n' "$NGINX_CONFIG"
else
    # Если нет location /, добавляем после root
    sed -i '/root.*public;/a\    location /storage {\n        alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;\n        try_files $uri $uri/ =404;\n    }' "$NGINX_CONFIG"
fi

echo "✅ location /storage добавлен в конфигурацию"

# Проверяем синтаксис
echo "Проверяем синтаксис Nginx..."
if nginx -t; then
    echo "✅ Синтаксис правильный"
    echo "Перезагружаем Nginx..."
    systemctl reload nginx
    echo "✅ Nginx перезагружен"
else
    echo "❌ Ошибка в синтаксисе! Восстанавливаем из резервной копии..."
    cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
    echo "✅ Конфигурация восстановлена"
    exit 1
fi

echo ""
echo "✅ Готово! Проверьте работу: http://m.dekan.pro/storage/avatars/63Mejz6n4St1hGlCTebjpHHPW7raXXGAHfslbnpp.jpg"














