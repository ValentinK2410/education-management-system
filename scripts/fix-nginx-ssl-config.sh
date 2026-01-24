#!/bin/bash

# Скрипт для исправления конфигурации Nginx с SSL сертификатом
# Для theologybooks.org

DOMAIN="theologybooks.org"
NGINX_CONFIG="/etc/nginx/sites-available/${DOMAIN}"
BACKUP_CONFIG="${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"

echo "🔧 Исправление конфигурации Nginx для ${DOMAIN}"
echo "=============================================="
echo ""

# Проверяем, запущен ли скрипт от root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Ошибка: Скрипт должен быть запущен с правами root (используйте sudo)"
    exit 1
fi

# Проверяем наличие конфигурационного файла
if [ ! -f "$NGINX_CONFIG" ]; then
    echo "⚠️  Файл конфигурации не найден: $NGINX_CONFIG"
    echo "Создаем новый файл конфигурации..."
    
    # Определяем путь к проекту
    if [ -d "/var/www/public" ]; then
        ROOT_PATH="/var/www/public"
    elif [ -d "/var/www/html" ]; then
        ROOT_PATH="/var/www/html"
    else
        read -p "Введите путь к корневой директории проекта: " ROOT_PATH
    fi
    
    # Определяем версию PHP
    PHP_VERSION=$(php -v | head -n 1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    PHP_SOCKET="unix:/var/run/php/php${PHP_VERSION}-fpm.sock"
    
    echo "Используется PHP: $PHP_VERSION"
    echo "PHP Socket: $PHP_SOCKET"
else
    # Создаем резервную копию
    cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
    echo "✅ Резервная копия создана: $BACKUP_CONFIG"
    
    # Определяем текущие настройки
    ROOT_PATH=$(grep -E "^\s*root\s+" "$NGINX_CONFIG" | head -1 | awk '{print $2}' | tr -d ';')
    PHP_SOCKET=$(grep -E "fastcgi_pass\s+" "$NGINX_CONFIG" | head -1 | awk '{print $2}' | tr -d ';')
    
    echo "Текущий root: $ROOT_PATH"
    echo "Текущий PHP socket: $PHP_SOCKET"
fi

# Проверяем наличие SSL сертификатов
if [ ! -f "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]; then
    echo "❌ SSL сертификат не найден: /etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
    echo "Получите сертификат командой:"
    echo "  sudo certbot --nginx -d ${DOMAIN}"
    exit 1
fi

echo ""
echo "✅ SSL сертификат найден"
echo ""

# Создаем новую конфигурацию
cat > "$NGINX_CONFIG" << EOF
# Конфигурация Nginx для ${DOMAIN}
# С SSL сертификатом Let's Encrypt
# Обновлено: $(date)

# Редирект HTTP на HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    
    # Редирект на HTTPS
    return 301 https://${DOMAIN}\$request_uri;
}

# HTTPS конфигурация
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN} www.${DOMAIN};
    
    root ${ROOT_PATH};
    index index.php index.html index.htm;
    
    charset utf-8;

    # SSL сертификаты Let's Encrypt
    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;
    
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
    access_log /var/log/nginx/${DOMAIN}.access.log;
    error_log /var/log/nginx/${DOMAIN}.error.log;

    # Основной location блок
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
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
    location ~ \.php\$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass ${PHP_SOCKET};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
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
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
EOF

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
        if [ -f "$BACKUP_CONFIG" ]; then
            echo "  sudo cp $BACKUP_CONFIG $NGINX_CONFIG"
            echo "  sudo nginx -t && sudo systemctl reload nginx"
        fi
    else
        echo "❌ Ошибка при перезагрузке Nginx"
        exit 1
    fi
else
    echo "❌ Ошибка в синтаксисе конфигурации!"
    if [ -f "$BACKUP_CONFIG" ]; then
        echo "Восстанавливаем из резервной копии..."
        cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
        nginx -t
    fi
    exit 1
fi
