#!/bin/bash

# Скрипт для исправления настроек таймаутов для синхронизации Moodle
# Для ISPmanager/панели управления
# Запускать НА СЕРВЕРЕ

echo "🔧 Настройка таймаутов для синхронизации Moodle..."
echo ""

# Путь к конфигурации Nginx
NGINX_CONFIG="/etc/nginx/vhosts-resources/dean.russianseminary.org/dynamic/php.conf"
NGINX_CONFIG_MAIN="/etc/nginx/vhosts-resources/dean.russianseminary.org/php.conf"
NGINX_SITE_CONFIG="/etc/nginx/vhosts-resources/dean.russianseminary.org/dean.russianseminary.org.conf"

# Путь к конфигурации PHP-FPM
PHP_FPM_CONFIG="/etc/php/8.4/fpm/pool.d/www.conf"
PHP_FPM_CONFIG_ALT="/var/www/php-fpm/5.conf"

# Проверяем, существует ли директория с конфигурациями
if [ ! -d "/etc/nginx/vhosts-resources/dean.russianseminary.org" ]; then
    echo "❌ Директория конфигураций не найдена"
    echo "Создаем директорию..."
    mkdir -p /etc/nginx/vhosts-resources/dean.russianseminary.org/dynamic
fi

# Создаем или обновляем конфигурацию fastcgi таймаутов
echo "=== Настройка Nginx FastCGI таймаутов ==="

# Проверяем, есть ли файл dynamic/php.conf
if [ -f "$NGINX_CONFIG" ]; then
    echo "Обновляем существующий файл: $NGINX_CONFIG"
    # Добавляем таймауты, если их еще нет
    if ! grep -q "fastcgi_read_timeout" "$NGINX_CONFIG"; then
        echo "fastcgi_read_timeout 1800;" >> "$NGINX_CONFIG"
        echo "✅ Добавлен fastcgi_read_timeout в $NGINX_CONFIG"
    else
        sed -i 's/fastcgi_read_timeout.*/fastcgi_read_timeout 1800;/' "$NGINX_CONFIG"
        echo "✅ Обновлен fastcgi_read_timeout в $NGINX_CONFIG"
    fi
    
    if ! grep -q "fastcgi_send_timeout" "$NGINX_CONFIG"; then
        echo "fastcgi_send_timeout 1800;" >> "$NGINX_CONFIG"
        echo "✅ Добавлен fastcgi_send_timeout"
    else
        sed -i 's/fastcgi_send_timeout.*/fastcgi_send_timeout 1800;/' "$NGINX_CONFIG"
        echo "✅ Обновлен fastcgi_send_timeout"
    fi
else
    echo "Создаем новый файл: $NGINX_CONFIG"
    cat > "$NGINX_CONFIG" << 'EOF'
# Таймауты для длительных операций синхронизации Moodle
fastcgi_read_timeout 1800;
fastcgi_send_timeout 1800;
EOF
    echo "✅ Файл создан: $NGINX_CONFIG"
fi

# Проверяем основной файл конфигурации сайта
if [ -f "$NGINX_SITE_CONFIG" ]; then
    echo ""
    echo "Обновляем основной файл конфигурации: $NGINX_SITE_CONFIG"
    
    # Добавляем таймауты в блок server, если их нет
    if ! grep -q "client_body_timeout" "$NGINX_SITE_CONFIG"; then
        # Ищем блок server и добавляем после него
        sed -i '/^server {/a\
    client_body_timeout 1800;\
    send_timeout 1800;' "$NGINX_SITE_CONFIG"
        echo "✅ Добавлены client_body_timeout и send_timeout"
    else
        sed -i 's/client_body_timeout.*/client_body_timeout 1800;/' "$NGINX_SITE_CONFIG"
        sed -i 's/send_timeout.*/send_timeout 1800;/' "$NGINX_SITE_CONFIG"
        echo "✅ Обновлены client_body_timeout и send_timeout"
    fi
fi

# Настройка PHP-FPM
echo ""
echo "=== Настройка PHP-FPM ==="

# Проверяем разные возможные пути к конфигурации PHP-FPM
PHP_FPM_FOUND=false

if [ -f "$PHP_FPM_CONFIG" ]; then
    PHP_FPM_CONFIG_FILE="$PHP_FPM_CONFIG"
    PHP_FPM_FOUND=true
elif [ -f "$PHP_FPM_CONFIG_ALT" ]; then
    PHP_FPM_CONFIG_FILE="$PHP_FPM_CONFIG_ALT"
    PHP_FPM_FOUND=true
else
    echo "⚠️  Конфигурация PHP-FPM не найдена в стандартных местах"
    echo "Ищем конфигурацию PHP-FPM..."
    
    # Ищем конфигурацию PHP 5 (судя по пути /var/www/php-fpm/5.sock)
    if [ -f "/var/www/php-fpm/5.conf" ]; then
        PHP_FPM_CONFIG_FILE="/var/www/php-fpm/5.conf"
        PHP_FPM_FOUND=true
    elif [ -f "/etc/php/5.6/fpm/pool.d/www.conf" ]; then
        PHP_FPM_CONFIG_FILE="/etc/php/5.6/fpm/pool.d/www.conf"
        PHP_FPM_FOUND=true
    fi
fi

if [ "$PHP_FPM_FOUND" = true ]; then
    echo "Найдена конфигурация PHP-FPM: $PHP_FPM_CONFIG_FILE"
    
    # Создаем резервную копию
    cp "$PHP_FPM_CONFIG_FILE" "${PHP_FPM_CONFIG_FILE}.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Обновляем request_terminate_timeout
    if grep -q "^request_terminate_timeout" "$PHP_FPM_CONFIG_FILE"; then
        sed -i 's/^request_terminate_timeout.*/request_terminate_timeout = 1800/' "$PHP_FPM_CONFIG_FILE"
        echo "✅ Обновлен request_terminate_timeout"
    else
        echo "" >> "$PHP_FPM_CONFIG_FILE"
        echo "; Timeout для длительных операций синхронизации" >> "$PHP_FPM_CONFIG_FILE"
        echo "request_terminate_timeout = 1800" >> "$PHP_FPM_CONFIG_FILE"
        echo "✅ Добавлен request_terminate_timeout"
    fi
    
    # Определяем версию PHP из пути
    PHP_VERSION=$(echo "$PHP_FPM_CONFIG_FILE" | grep -oP 'php/\K[0-9.]+' | head -1)
    if [ -z "$PHP_VERSION" ]; then
        # Пробуем определить из пути к сокету
        if [ -f "/var/www/php-fpm/5.sock" ]; then
            PHP_VERSION="5.6"
        fi
    fi
    
    # Обновляем max_execution_time в php.ini
    if [ -n "$PHP_VERSION" ]; then
        PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
        if [ -f "$PHP_INI" ]; then
            if grep -q "^max_execution_time" "$PHP_INI"; then
                sed -i 's/^max_execution_time.*/max_execution_time = 1800/' "$PHP_INI"
                echo "✅ Обновлен max_execution_time в $PHP_INI"
            else
                echo "max_execution_time = 1800" >> "$PHP_INI"
                echo "✅ Добавлен max_execution_time в $PHP_INI"
            fi
        fi
    fi
else
    echo "⚠️  Не удалось найти конфигурацию PHP-FPM"
    echo "Пожалуйста, настройте вручную:"
    echo "  - request_terminate_timeout = 1800"
    echo "  - max_execution_time = 1800"
fi

# Проверяем синтаксис Nginx
echo ""
echo "=== Проверка синтаксиса Nginx ==="
if nginx -t 2>&1; then
    echo "✅ Синтаксис Nginx правильный"
    echo ""
    echo "Перезагружаем Nginx..."
    systemctl reload nginx 2>/dev/null || service nginx reload 2>/dev/null || /etc/init.d/nginx reload 2>/dev/null
    echo "✅ Nginx перезагружен"
else
    echo "❌ Ошибка в синтаксисе Nginx!"
    echo "Проверьте конфигурацию вручную"
    exit 1
fi

# Перезапускаем PHP-FPM
if [ "$PHP_FPM_FOUND" = true ]; then
    echo ""
    echo "Перезапускаем PHP-FPM..."
    
    # Пробуем разные способы перезапуска
    if systemctl restart php8.4-fpm 2>/dev/null; then
        echo "✅ PHP 8.4-FPM перезапущен"
    elif systemctl restart php5.6-fpm 2>/dev/null; then
        echo "✅ PHP 5.6-FPM перезапущен"
    elif systemctl restart php-fpm 2>/dev/null; then
        echo "✅ PHP-FPM перезапущен"
    elif service php-fpm restart 2>/dev/null; then
        echo "✅ PHP-FPM перезапущен (через service)"
    else
        echo "⚠️  PHP-FPM не перезапущен автоматически"
        echo "Перезапустите вручную через панель управления"
    fi
fi

echo ""
echo "✅ Настройки таймаутов применены!"
echo ""
echo "Текущие настройки:"
echo "- Nginx fastcgi_read_timeout: 1800 секунд (30 минут)"
echo "- Nginx fastcgi_send_timeout: 1800 секунд (30 минут)"
echo "- Nginx client_body_timeout: 1800 секунд (30 минут)"
echo "- Nginx send_timeout: 1800 секунд (30 минут)"
if [ "$PHP_FPM_FOUND" = true ]; then
    echo "- PHP-FPM request_terminate_timeout: 1800 секунд (30 минут)"
    echo "- PHP max_execution_time: 1800 секунд (30 минут)"
fi
echo ""
echo "Теперь синхронизация Moodle должна работать без таймаутов."
