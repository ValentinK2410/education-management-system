#!/bin/bash

# Скрипт для исправления настроек таймаутов для синхронизации Moodle
# Запускать НА СЕРВЕРЕ

echo "🔧 Настройка таймаутов для синхронизации Moodle..."
echo ""

# Путь к конфигурации Nginx
NGINX_CONFIG="/etc/nginx/sites-available/m.dekan.pro"
NGINX_BACKUP="/etc/nginx/sites-available/m.dekan.pro.backup.$(date +%Y%m%d_%H%M%S)"

# Путь к конфигурации PHP-FPM
PHP_FPM_CONFIG="/etc/php/8.4/fpm/pool.d/www.conf"
PHP_FPM_BACKUP="/etc/php/8.4/fpm/pool.d/www.conf.backup.$(date +%Y%m%d_%H%M%S)"

# Проверяем, существует ли конфигурация Nginx
if [ ! -f "$NGINX_CONFIG" ]; then
    echo "❌ Конфигурация Nginx не найдена: $NGINX_CONFIG"
    echo "Проверьте путь к конфигурации"
    exit 1
fi

# Создаем резервную копию конфигурации Nginx
cp "$NGINX_CONFIG" "$NGINX_BACKUP"
echo "✅ Резервная копия Nginx создана: $NGINX_BACKUP"

# Проверяем и обновляем настройки таймаутов в Nginx
echo ""
echo "=== Настройка Nginx ==="

# Проверяем, есть ли уже fastcgi_read_timeout
if grep -q "fastcgi_read_timeout" "$NGINX_CONFIG"; then
    echo "Обновляем существующий fastcgi_read_timeout..."
    sed -i 's/fastcgi_read_timeout.*/fastcgi_read_timeout 1800;/' "$NGINX_CONFIG"
else
    echo "Добавляем fastcgi_read_timeout..."
    # Ищем блок location ~ \.php$ и добавляем после него
    if grep -q "location ~ \\\.php\$" "$NGINX_CONFIG"; then
        sed -i '/location ~ \\\.php\$/,/^[[:space:]]*}/ {
            /^[[:space:]]*}/i\
        fastcgi_read_timeout 1800;
        }' "$NGINX_CONFIG"
    else
        # Если блока нет, добавляем в location /
        sed -i '/location \//a\
    fastcgi_read_timeout 1800;' "$NGINX_CONFIG"
    fi
fi

# Добавляем proxy_read_timeout для проксирования
if ! grep -q "proxy_read_timeout" "$NGINX_CONFIG"; then
    echo "Добавляем proxy_read_timeout..."
    sed -i '/fastcgi_read_timeout/a\
    proxy_read_timeout 1800;' "$NGINX_CONFIG"
fi

# Добавляем client_body_timeout и send_timeout
if ! grep -q "client_body_timeout" "$NGINX_CONFIG"; then
    sed -i '/^server {/a\
    client_body_timeout 1800;\
    send_timeout 1800;' "$NGINX_CONFIG"
fi

echo "✅ Настройки Nginx обновлены"

# Настройка PHP-FPM
echo ""
echo "=== Настройка PHP-FPM ==="

if [ -f "$PHP_FPM_CONFIG" ]; then
    cp "$PHP_FPM_CONFIG" "$PHP_FPM_BACKUP"
    echo "✅ Резервная копия PHP-FPM создана: $PHP_FPM_BACKUP"
    
    # Обновляем request_terminate_timeout
    if grep -q "^request_terminate_timeout" "$PHP_FPM_CONFIG"; then
        sed -i 's/^request_terminate_timeout.*/request_terminate_timeout = 1800/' "$PHP_FPM_CONFIG"
    else
        # Добавляем в конец файла
        echo "" >> "$PHP_FPM_CONFIG"
        echo "; Timeout для длительных операций синхронизации" >> "$PHP_FPM_CONFIG"
        echo "request_terminate_timeout = 1800" >> "$PHP_FPM_CONFIG"
    fi
    
    # Обновляем max_execution_time в php.ini
    PHP_INI="/etc/php/8.4/fpm/php.ini"
    if [ -f "$PHP_INI" ]; then
        if grep -q "^max_execution_time" "$PHP_INI"; then
            sed -i 's/^max_execution_time.*/max_execution_time = 1800/' "$PHP_INI"
        else
            echo "max_execution_time = 1800" >> "$PHP_INI"
        fi
        echo "✅ Настройки PHP.ini обновлены"
    fi
    
    echo "✅ Настройки PHP-FPM обновлены"
else
    echo "⚠️  Конфигурация PHP-FPM не найдена: $PHP_FPM_CONFIG"
    echo "Проверьте путь к конфигурации PHP-FPM"
fi

# Проверяем синтаксис Nginx
echo ""
echo "=== Проверка синтаксиса Nginx ==="
if nginx -t 2>&1; then
    echo "✅ Синтаксис Nginx правильный"
    echo ""
    echo "Перезагружаем Nginx..."
    systemctl reload nginx
    echo "✅ Nginx перезагружен"
else
    echo "❌ Ошибка в синтаксисе Nginx!"
    echo "Восстанавливаем из резервной копии..."
    cp "$NGINX_BACKUP" "$NGINX_CONFIG"
    echo "✅ Конфигурация восстановлена"
    exit 1
fi

# Перезапускаем PHP-FPM
if [ -f "$PHP_FPM_CONFIG" ]; then
    echo ""
    echo "Перезапускаем PHP-FPM..."
    systemctl restart php8.4-fpm 2>/dev/null || systemctl restart php84-php-fpm 2>/dev/null || echo "⚠️  PHP-FPM не перезапущен автоматически"
    echo "✅ PHP-FPM перезапущен"
fi

echo ""
echo "✅ Настройки таймаутов применены!"
echo ""
echo "Текущие настройки:"
echo "- Nginx fastcgi_read_timeout: 1800 секунд (30 минут)"
echo "- PHP-FPM request_terminate_timeout: 1800 секунд (30 минут)"
echo "- PHP max_execution_time: 1800 секунд (30 минут)"
echo ""
echo "Теперь синхронизация Moodle должна работать без таймаутов."
