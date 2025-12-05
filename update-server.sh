#!/bin/bash

# Скрипт для обновления кода на сервере
# Выполните на сервере: bash update-server.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "🔄 Обновление кода из Git..."
git pull origin main

# Определение пользователя PHP-FPM
WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')
if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
    else
        WORKER_USER="www-data"
    fi
fi
echo "✅ Пользователь PHP-FPM: $WORKER_USER"

echo "🧹 Очистка кеша..."
rm -rf storage/framework/views/*
rm -rf storage/framework/cache/*

echo "📁 Создание необходимых директорий..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p storage/app/public/certificate-templates
mkdir -p bootstrap/cache

echo "🔐 Установка прав доступа..."
chown -R $WORKER_USER:$WORKER_USER storage
chown -R $WORKER_USER:$WORKER_USER bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Убеждаемся, что файл логов существует и имеет правильные права
touch storage/logs/laravel.log
chown $WORKER_USER:$WORKER_USER storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

echo "🧹 Очистка кеша Laravel..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "🔄 Перезапуск PHP-FPM..."
# Проверка и перезапуск PHP 8.4
if systemctl is-active --quiet php84-php-fpm; then
    systemctl restart php84-php-fpm
    echo "✅ PHP 8.4-FPM перезапущен"
fi

# Проверка и перезапуск PHP 8.3
if systemctl is-active --quiet php8.3-fpm; then
    systemctl restart php8.3-fpm
    echo "✅ PHP 8.3-FPM перезапущен"
fi

echo "✅ Обновление завершено!"
