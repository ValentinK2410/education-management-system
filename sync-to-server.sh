#!/bin/bash

# Простой скрипт для синхронизации с сервером
# Использование: ./sync-to-server.sh

echo "🔄 Синхронизируем проект с сервером..."

# Подключаемся к серверу и обновляем код
ssh -o StrictHostKeyChecking=no root@82.146.39.18 << 'REMOTE_SCRIPT'
cd /var/www/www-root/data/www/m.dekan.pro
echo "📥 Обновляем код с GitHub..."
git fetch origin
git reset --hard origin/main
echo "📦 Обновляем зависимости..."
composer install --no-dev --optimize-autoloader
echo "🧹 Очищаем кэш..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "🗄️ Проверяем миграции..."
php artisan migrate --force
echo "⚡ Оптимизируем приложение..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "🔐 Устанавливаем права доступа..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
echo "✅ Синхронизация завершена!"
REMOTE_SCRIPT

echo "🌐 Проект обновлен на сервере: http://82.146.39.18"
