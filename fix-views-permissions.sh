#!/bin/bash

# Скрипт для исправления прав доступа к storage/framework/views
# Выполните на сервере: bash fix-views-permissions.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "🔧 Исправление прав доступа к storage/framework/views..."

# Определяем пользователя PHP-FPM
WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')

if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
        echo "✅ Используем пользователя: www-root"
    else
        WORKER_USER="www-data"
        echo "✅ Используем пользователя: www-data"
    fi
else
    echo "✅ Определен пользователь PHP-FPM: $WORKER_USER"
fi

# Если активный пользователь www-data, но PHP работает от www-root, используем www-root
if [ "$WORKER_USER" = "www-data" ] && id "www-root" &>/dev/null; then
    if ps aux | grep "php-fpm" | grep -q "www-root"; then
        echo "⚠️  Обнаружен пользователь www-root, используем его"
        WORKER_USER="www-root"
    fi
fi

echo "📁 Создание директории storage/framework/views..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p storage/app/public/certificate-templates

echo "🧹 Очистка старых скомпилированных представлений..."
rm -rf storage/framework/views/*

echo "🔐 Установка прав доступа..."
chown -R $WORKER_USER:$WORKER_USER storage/framework/views
chown -R $WORKER_USER:$WORKER_USER storage/framework/cache
chown -R $WORKER_USER:$WORKER_USER storage/framework/sessions
chown -R $WORKER_USER:$WORKER_USER storage/logs
chown -R $WORKER_USER:$WORKER_USER storage/app/public

chmod -R 775 storage/framework/views
chmod -R 775 storage/framework/cache
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/logs
chmod -R 775 storage/app/public

echo "✅ Проверка прав доступа:"
ls -la storage/framework/views/ | head -5

echo "🧪 Тест записи..."
sudo -u $WORKER_USER touch storage/framework/views/test_write_$(date +%s).php 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Запись работает!"
    sudo -u $WORKER_USER rm storage/framework/views/test_write_*.php 2>/dev/null
else
    echo "❌ Ошибка записи. Попробуйте выполнить от root:"
    echo "   chown -R $WORKER_USER:$WORKER_USER storage/framework/views"
    echo "   chmod -R 775 storage/framework/views"
fi

echo "🧹 Очистка кеша Laravel..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear

echo "✅ Готово!"
