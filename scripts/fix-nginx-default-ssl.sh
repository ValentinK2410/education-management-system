#!/bin/bash
# Использование: bash fix-nginx-default-ssl.sh (не sh!)

# Скрипт для исправления конфигурации Nginx в файле default
# Для theologybooks.org с SSL сертификатом

DOMAIN="theologybooks.org"
NGINX_CONFIG="/etc/nginx/sites-available/default"
BACKUP_CONFIG="${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

echo "🔧 Исправление конфигурации Nginx в файле default для ${DOMAIN}"
echo "=============================================================="
echo ""

# Проверяем, запущен ли скрипт от root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Ошибка: Скрипт должен быть запущен с правами root (используйте sudo)"
    exit 1
fi

# Проверяем наличие конфигурационного файла
if [ ! -f "$NGINX_CONFIG" ]; then
    echo "❌ Файл конфигурации не найден: $NGINX_CONFIG"
    exit 1
fi

# Создаем резервную копию
cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
echo "✅ Резервная копия создана: $BACKUP_CONFIG"
echo ""

# Определяем текущие настройки
ROOT_PATH=$(grep -E "^\s*root\s+" "$NGINX_CONFIG" | head -1 | awk '{print $2}' | tr -d ';')
PHP_SOCKET=$(grep -E "fastcgi_pass\s+" "$NGINX_CONFIG" | grep -v "#" | head -1 | awk '{print $2}' | tr -d ';')

if [ -z "$ROOT_PATH" ]; then
    if [ -d "/var/www/public" ]; then
        ROOT_PATH="/var/www/public"
    elif [ -d "/var/www/html" ]; then
        ROOT_PATH="/var/www/html"
    else
        read -p "Введите путь к корневой директории проекта: " ROOT_PATH
    fi
fi

if [ -z "$PHP_SOCKET" ]; then
    PHP_VERSION=$(php -v 2>/dev/null | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    if [ -n "$PHP_VERSION" ]; then
        PHP_SOCKET="unix:/var/run/php/php${PHP_VERSION}-fpm.sock"
    else
        PHP_SOCKET="unix:/var/run/php/php8.2-fpm.sock"
    fi
fi

echo "Текущий root: $ROOT_PATH"
echo "Текущий PHP socket: $PHP_SOCKET"
echo ""

# Проверяем наличие SSL сертификатов
if [ ! -f "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]; then
    echo "❌ SSL сертификат не найден: /etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
    echo "Получите сертификат командой:"
    echo "  sudo certbot --nginx -d ${DOMAIN}"
    exit 1
fi

echo "✅ SSL сертификат найден"
echo ""

# Проверяем, есть ли уже блок для домена
if grep -q "server_name.*${DOMAIN}" "$NGINX_CONFIG"; then
    echo "⚠️  В конфигурации уже есть блок для ${DOMAIN}"
    echo "Продолжаем обновление конфигурации..."
fi

# Читаем текущий файл
CURRENT_CONFIG=$(cat "$NGINX_CONFIG")

# Проверяем, есть ли уже HTTPS блок
if echo "$CURRENT_CONFIG" | grep -q "listen 443"; then
    echo "⚠️  В конфигурации уже есть HTTPS блок (443 порт)"
    echo "Обновляем существующий блок..."
fi

# Создаем новую конфигурацию
cat > "$NGINX_CONFIG" << 'NGINX_EOF'
# Конфигурация Nginx для theologybooks.org
# С SSL сертификатом Let's Encrypt
# Обновлено: $(date)

# Редирект HTTP на HTTPS
server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;
    server_name theologybooks.org www.theologybooks.org _;
    
    # Редирект на HTTPS (только для theologybooks.org)
    if ($host = theologybooks.org) {
        return 301 https://theologybooks.org$request_uri;
    }
    if ($host = www.theologybooks.org) {
        return 301 https://theologybooks.org$request_uri;
    }
    
    # Для других доменов - обычная обработка
    root /var/www/public;
    index index.php index.html index.htm;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { 
        access_log off; 
        log_not_found off; 
    }
    
    location = /robots.txt { 
        access_log off; 
        log_not_found off; 
    }
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# HTTPS конфигурация для theologybooks.org
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name theologybooks.org www.theologybooks.org;
    
    root /var/www/public;
    index index.php index.html index.htm;
    
    charset utf-8;

    # SSL сертификаты Let's Encrypt
    ssl_certificate /etc/letsencrypt/live/theologybooks.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/theologybooks.org/privkey.pem;
    
    # SSL настройки безопасности
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Безопасность заголовков
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Логирование
    access_log /var/log/nginx/theologybooks.org.access.log;
    error_log /var/log/nginx/theologybooks.org.error.log;

    # Основной location блок
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Favicon и robots.txt
    location = /favicon.ico { 
        access_log off; 
        log_not_found off; 
    }
    
    location = /robots.txt { 
        access_log off; 
        log_not_found off; 
    }

    # Защита phpMyAdmin (если используется)
    location /pma_library {
        auth_basic "Restricted Access";
        auth_basic_user_file /etc/nginx/.htpasswd;
    }

    # Обработка PHP файлов
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Защита скрытых файлов
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Обработка 404 ошибок
    error_page 404 /index.php;

    # Отключение логирования для статических файлов
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
NGINX_EOF

# Заменяем плейсхолдеры
sed -i "s|/var/www/public|${ROOT_PATH}|g" "$NGINX_CONFIG"
sed -i "s|unix:/var/run/php/php8.2-fpm.sock|${PHP_SOCKET}|g" "$NGINX_CONFIG"

echo "✅ Конфигурация обновлена"
echo ""

# Проверяем синтаксис
echo "🔍 Проверка синтаксиса Nginx..."
if nginx -t; then
    echo "✅ Синтаксис правильный"
    echo ""
    echo "🔄 Перезагрузка Nginx..."
    if systemctl reload nginx; then
        echo "✅ Nginx успешно перезагружен"
        echo ""
        echo "=============================================="
        echo "✅ Конфигурация успешно обновлена!"
        echo ""
        echo "Проверьте работу сайта:"
        echo "  https://${DOMAIN}"
        echo ""
        echo "Если что-то пошло не так, восстановите из резервной копии:"
        echo "  sudo cp $BACKUP_CONFIG $NGINX_CONFIG"
        echo "  sudo nginx -t && sudo systemctl reload nginx"
    else
        echo "❌ Ошибка при перезагрузке Nginx"
        exit 1
    fi
else
    echo "❌ Ошибка в синтаксисе конфигурации!"
    echo "Восстанавливаем из резервной копии..."
    cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
    nginx -t
    exit 1
fi
