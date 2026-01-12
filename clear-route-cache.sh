#!/bin/bash

# Скрипт для очистки кэша маршрутов на сервере
# Использование: ./clear-route-cache.sh [path-to-project]

set -e

PROJECT_PATH=${1:-/var/www/www-root/data/www/m.dekan.pro}

echo "🧹 Очистка кэша маршрутов в $PROJECT_PATH..."

cd "$PROJECT_PATH" || { echo "❌ Ошибка: Не удалось перейти в директорию $PROJECT_PATH"; exit 1; }

echo "Очистка кэша маршрутов..."
php artisan route:clear

echo "Очистка кэша конфигурации..."
php artisan config:clear

echo "Очистка общего кэша..."
php artisan cache:clear

echo "Очистка кэша представлений..."
php artisan view:clear

echo "Пересборка кэша маршрутов..."
php artisan route:cache

echo "Пересборка кэша конфигурации..."
php artisan config:cache

echo "✅ Кэш успешно очищен и пересобран"
