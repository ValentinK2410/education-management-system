#!/bin/bash

# Скрипт для исправления прав доступа к директориям storage Laravel
# Использование: ./fix-storage-permissions.sh

echo "🔧 Исправление прав доступа к storage директориям Laravel..."
echo "=========================================================="
echo ""

# Путь к проекту (измените если нужно)
PROJECT_PATH="/var/www/www-root/data/www/m.dekan.pro"

cd "$PROJECT_PATH" || exit 1

echo "📁 Текущая директория: $(pwd)"
echo ""

# Создать необходимые директории если их нет
echo "📂 Создание необходимых директорий..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p bootstrap/cache
echo "✅ Директории созданы"
echo ""

# Установить права доступа 775 для всех директорий storage
echo "🔐 Установка прав доступа 775 для storage..."
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
echo "✅ Права доступа установлены"
echo ""

# Назначить владельца www-data для всех директорий storage
echo "👤 Назначение владельца www-data:www-data..."
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
echo "✅ Владелец назначен"
echo ""

# Создать файл логов если его нет
if [ ! -f storage/logs/laravel.log ]; then
    echo "📄 Создание файла логов..."
    touch storage/logs/laravel.log
    chmod 664 storage/logs/laravel.log
    chown www-data:www-data storage/logs/laravel.log
    echo "✅ Файл логов создан"
    echo ""
fi

# Проверка прав доступа
echo "🔍 Проверка прав доступа..."
echo "Storage доступен: $([ -w storage ] && echo '✅' || echo '❌')"
echo "Storage/framework доступен: $([ -w storage/framework ] && echo '✅' || echo '❌')"
echo "Storage/framework/views доступен: $([ -w storage/framework/views ] && echo '✅' || echo '❌')"
echo "Bootstrap/cache доступен: $([ -w bootstrap/cache ] && echo '✅' || echo '❌')"
echo ""

# Очистка кэша Laravel
echo "🧹 Очистка кэша Laravel..."
php artisan cache:clear 2>/dev/null || echo "⚠️ Не удалось очистить кэш"
php artisan config:clear 2>/dev/null || echo "⚠️ Не удалось очистить config кэш"
php artisan view:clear 2>/dev/null || echo "⚠️ Не удалось очистить view кэш"
php artisan route:clear 2>/dev/null || echo "⚠️ Не удалось очистить route кэш"
echo "✅ Кэш очищен"
echo ""

echo "✅ Исправление прав доступа завершено!"
echo ""
echo "🔄 Рекомендуется перезапустить PHP-FPM:"
echo "   sudo systemctl restart php8.4-fpm"
echo ""
