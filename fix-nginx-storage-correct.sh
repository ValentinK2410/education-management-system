#!/bin/bash

# Правильное исправление конфигурации Nginx для storage
# Запускать НА СЕРВЕРЕ

NGINX_CONFIG="/etc/nginx/sites-available/m.dekan.pro"
BACKUP_CONFIG="/etc/nginx/sites-available/m.dekan.pro.backup.$(date +%Y%m%d_%H%M%S)"

echo "🔧 Исправление конфигурации Nginx для storage..."

# Создаем резервную копию
cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
echo "✅ Резервная копия: $BACKUP_CONFIG"

# Проверяем, есть ли уже location /storage
if grep -q "location /storage" "$NGINX_CONFIG"; then
    echo "⚠️  location /storage уже существует, удаляем старый..."
    # Удаляем старый location /storage
    sed -i '/location \/storage/,/^[[:space:]]*}/d' "$NGINX_CONFIG"
fi

# Находим строку с "location /" и добавляем ПЕРЕД ней location /storage
# Важно: location /storage должен быть ПЕРЕД location /, чтобы обрабатываться первым
sed -i '/^[[:space:]]*location \//i\
    location /storage {\
        alias /var/www/www-root/data/www/m.dekan.pro/storage/app/public;\
        try_files $uri $uri/ =404;\
        access_log off;\
    }\
' "$NGINX_CONFIG"

echo "✅ location /storage добавлен ПЕРЕД location /"

# Показываем добавленный блок
echo ""
echo "Добавленный блок:"
grep -A 5 "location /storage" "$NGINX_CONFIG"

# Проверяем синтаксис
echo ""
echo "Проверяем синтаксис Nginx..."
if nginx -t 2>&1; then
    echo "✅ Синтаксис правильный"
    echo "Перезагружаем Nginx..."
    systemctl reload nginx
    if [ $? -eq 0 ]; then
        echo "✅ Nginx перезагружен"
        echo ""
        echo "Проверьте работу:"
        echo "http://m.dekan.pro/storage/avatars/63Mejz6n4St1hGlCTebjpHHPW7raXXGAHfslbnpp.jpg"
    else
        echo "❌ Ошибка при перезагрузке Nginx"
        exit 1
    fi
else
    echo "❌ Ошибка в синтаксисе! Восстанавливаем из резервной копии..."
    cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
    echo "✅ Конфигурация восстановлена"
    exit 1
fi














