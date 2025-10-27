#!/bin/bash
# Упрощенный скрипт для исправления прав доступа без перезапуска PHP-FPM

echo "🔧 Исправление прав доступа к логам Laravel"
echo "==========================================="

# Перейти в директорию проекта
cd /var/www/www-root/data/www/m.dekan.pro || exit 1

# Обновить код с GitHub
echo "📥 Обновление кода с GitHub..."
git pull origin main

# Создать директории если не существуют
echo "📁 Создание директорий..."
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Установить правильные права доступа
echo "🔐 Установка прав доступа..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Назначить правильного владельца
echo "👤 Назначение владельца..."
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Создать файл логов если не существует
echo "📄 Создание файла логов..."
touch storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log

# Очистить кэш Laravel
echo "🧹 Очистка кэша Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Готово! Проверьте работу сайта."
echo "⚠️ Примечание: Если ошибка сохраняется, перезапустите веб-сервер вручную."
