#!/bin/bash
# Скрипт для исправления прав доступа на сервере
# Выполните: ssh root@82.146.39.18 "bash -s" < fix-permissions.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "Создаю необходимые директории..."
mkdir -p storage/app/public/certificate-templates
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p bootstrap/cache

echo "Устанавливаю права доступа 775..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "Устанавливаю владельца www-data:www-data..."
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache

echo "Проверяю права доступа..."
ls -la storage/framework/views | head -5
ls -la storage/framework/cache | head -5

echo "Очищаю кэш Laravel..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear

echo "✅ Готово! Права доступа исправлены."
