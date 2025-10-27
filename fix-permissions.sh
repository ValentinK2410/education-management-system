#!/bin/bash
# Скрипт для исправления прав доступа к storage директории

echo "🔧 Исправление прав доступа к storage/framework/views/"
echo "===================================================="
echo ""

# Перейти в директорию проекта
cd /var/www/www-root/data/www/m.dekan.pro

# Обновить код
echo "📥 Обновление кода с GitHub..."
git pull origin main

# Удалить все скомпилированные представления
echo "🗑️ Удаление скомпилированных представлений..."
rm -rf storage/framework/views/*

# Установить правильные права доступа
echo "🔐 Установка прав доступа..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Назначить правильного владельца
echo "👤 Назначение владельца..."
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Создать необходимые директории
echo "📁 Создание директорий..."
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs

# Установить права для framework директорий
chmod -R 775 storage/framework/
chmod 664 storage/logs/laravel.log 2>/dev/null || touch storage/logs/laravel.log && chmod 664 storage/logs/laravel.log

# Очистить кэш Laravel
echo "🧹 Очистка кэша Laravel..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Проверить результат
echo ""
echo "✅ Проверка результата:"
ls -la storage/framework/views/

echo ""
echo "✅ Готово! Проверьте работу сайта."
