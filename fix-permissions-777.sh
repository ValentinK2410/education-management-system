#!/bin/bash
# Скрипт для установки прав 777 (для быстрого исправления)

echo "🔧 Установка прав 777 для storage и bootstrap/cache"
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

# Создать необходимые директории
echo "📁 Создание директорий..."
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/logs

# Установить права 777 для storage и bootstrap/cache
echo "🔐 Установка прав 777..."
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

# Назначить правильного владельца
echo "👤 Назначение владельца..."
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Создать файл логов если не существует
touch storage/logs/laravel.log
chmod 777 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log

# Очистить кэш Laravel
echo "🧹 Очистка кэша Laravel..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Проверить результат
echo ""
echo "✅ Проверка результата:"
ls -la storage/
ls -la storage/framework/views/

echo ""
echo "✅ Готово! Теперь сайт должен работать."
echo "⚠️ Примечание: Права 777 небезопасны для продакшена. После проверки рекомендуется вернуть 775."
