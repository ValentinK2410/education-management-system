#!/bin/bash

# Скрипт для увеличения тайм-аутов в Nginx для длительных операций синхронизации Moodle
# Запускать НА СЕРВЕРЕ с правами root

echo "🔧 Увеличение тайм-аутов в Nginx для синхронизации Moodle..."

# Определяем конфигурационный файл Nginx
# Проверяем несколько возможных путей
NGINX_CONFIGS=(
    "/etc/nginx/sites-available/dean.russianseminary.org"
    "/etc/nginx/sites-available/default"
    "/etc/nginx/nginx.conf"
)

NGINX_CONFIG=""
for config in "${NGINX_CONFIGS[@]}"; do
    if [ -f "$config" ]; then
        NGINX_CONFIG="$config"
        echo "✅ Найден конфигурационный файл: $NGINX_CONFIG"
        break
    fi
done

if [ -z "$NGINX_CONFIG" ]; then
    echo "❌ Не найден конфигурационный файл Nginx"
    echo "Проверьте один из следующих путей:"
    for config in "${NGINX_CONFIGS[@]}"; do
        echo "  - $config"
    done
    exit 1
fi

# Создаем резервную копию
BACKUP_CONFIG="${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
echo "✅ Резервная копия создана: $BACKUP_CONFIG"

# Проверяем, есть ли уже настройки тайм-аутов
if grep -q "fastcgi_read_timeout" "$NGINX_CONFIG"; then
    echo "⚠️  Настройки fastcgi_read_timeout уже существуют, обновляем..."
    # Обновляем существующие значения
    sed -i 's/fastcgi_read_timeout.*/fastcgi_read_timeout 600;/g' "$NGINX_CONFIG"
else
    echo "➕ Добавляем настройки тайм-аутов..."
    # Находим блок location ~ \.php$ и добавляем настройки тайм-аутов
    if grep -q "location ~ \\\.php\$" "$NGINX_CONFIG"; then
        # Добавляем после fastcgi_pass или внутри блока location ~ \.php$
        sed -i '/location ~ \\\.php\$/,/^[[:space:]]*}/ {
            /fastcgi_pass/a\
            fastcgi_read_timeout 600;\
            fastcgi_send_timeout 600;\
            fastcgi_connect_timeout 600;
        }' "$NGINX_CONFIG"
    else
        # Если нет блока location ~ \.php$, добавляем в server блок
        sed -i '/server {/a\
    fastcgi_read_timeout 600;\
    fastcgi_send_timeout 600;\
    fastcgi_connect_timeout 600;
' "$NGINX_CONFIG"
    fi
fi

# Также добавляем в http блок, если его там нет
if ! grep -q "proxy_read_timeout" /etc/nginx/nginx.conf 2>/dev/null; then
    echo "➕ Добавляем настройки тайм-аутов в основной конфиг nginx.conf..."
    if [ -f /etc/nginx/nginx.conf ]; then
        cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup.$(date +%Y%m%d_%H%M%S)
        # Добавляем в http блок
        sed -i '/http {/a\
    proxy_read_timeout 600;\
    proxy_connect_timeout 600;\
    proxy_send_timeout 600;
' /etc/nginx/nginx.conf
    fi
fi

echo ""
echo "=== Проверка синтаксиса Nginx ==="
if nginx -t 2>&1; then
    echo "✅ Синтаксис правильный"
    echo ""
    echo "Перезагружаем Nginx..."
    systemctl reload nginx
    if [ $? -eq 0 ]; then
        echo "✅ Nginx успешно перезагружен"
    else
        echo "❌ Ошибка при перезагрузке Nginx"
        exit 1
    fi
else
    echo "❌ Ошибка в синтаксисе! Восстанавливаем из резервной копии..."
    cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
    if [ -f /etc/nginx/nginx.conf.backup.* ]; then
        RESTORE_NGINX=$(ls -t /etc/nginx/nginx.conf.backup.* | head -1)
        cp "$RESTORE_NGINX" /etc/nginx/nginx.conf
    fi
    exit 1
fi

echo ""
echo "=== Текущие настройки тайм-аутов ==="
grep -E "(fastcgi_read_timeout|fastcgi_send_timeout|fastcgi_connect_timeout|proxy_read_timeout)" "$NGINX_CONFIG" /etc/nginx/nginx.conf 2>/dev/null | head -10

echo ""
echo "✅ Настройка завершена!"
echo "Тайм-ауты увеличены до 600 секунд (10 минут) для длительных операций синхронизации."
