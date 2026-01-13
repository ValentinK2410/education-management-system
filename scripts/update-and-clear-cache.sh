#!/bin/bash

# Скрипт для обновления кода с GitHub и очистки кэша Laravel
# Использование: ./scripts/update-and-clear-cache.sh

echo "🔄 Обновление кода и очистка кэша Laravel..."

# Определяем путь к проекту (можно передать как аргумент)
PROJECT_PATH=${1:-"/var/www/www-root/data/www/dean.russianseminary.org"}

if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ Директория проекта не найдена: $PROJECT_PATH"
    exit 1
fi

cd "$PROJECT_PATH" || { echo "❌ Не удалось перейти в директорию $PROJECT_PATH"; exit 1; }

echo "📁 Рабочая директория: $PROJECT_PATH"

# Обновляем код с GitHub
echo ""
echo "📥 Обновление кода с GitHub..."
git pull origin main
if [ $? -ne 0 ]; then
    echo "⚠️  Предупреждение: git pull завершился с ошибкой или нет изменений"
fi

# Очищаем все кэши Laravel
echo ""
echo "🧹 Очистка кэша Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Пересобираем кэш для оптимизации
echo ""
echo "⚡ Пересборка кэша для оптимизации..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo ""
echo "✅ Обновление и очистка кэша завершены!"
