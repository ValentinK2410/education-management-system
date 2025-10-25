#!/bin/bash

# Скрипт для автоматического деплоя проекта на сервер
# Использование: ./deploy.sh

set -e  # Остановить выполнение при ошибке

# Конфигурация
SERVER_HOST="82.146.39.18"
SERVER_USER="root"
SERVER_PASSWORD="lShzBqBqnsHcR2sUos8v4XT4"
SERVER_PATH="/var/www/www-root/data/www/m.dekan.pro"

echo "🚀 Начинаем деплой проекта на сервер..."

# Функция для выполнения команд на сервере
run_on_server() {
    sshpass -p "$SERVER_PASSWORD" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" "$1"
}

# 1. Подключаемся к серверу и обновляем код
echo "📥 Обновляем код с GitHub..."
run_on_server "cd $SERVER_PATH && git fetch origin && git reset --hard origin/main"

# 2. Обновляем зависимости
echo "📦 Обновляем зависимости..."
run_on_server "cd $SERVER_PATH && composer install --no-dev --optimize-autoloader"

# 3. Очищаем кэш
echo "🧹 Очищаем кэш..."
run_on_server "cd $SERVER_PATH && php artisan config:clear && php artisan cache:clear && php artisan view:clear"

# 4. Запускаем миграции (если есть новые)
echo "🗄️ Проверяем миграции..."
run_on_server "cd $SERVER_PATH && php artisan migrate --force"

# 5. Оптимизируем приложение
echo "⚡ Оптимизируем приложение..."
run_on_server "cd $SERVER_PATH && php artisan config:cache && php artisan route:cache && php artisan view:cache"

# 6. Устанавливаем правильные права доступа
echo "🔐 Устанавливаем права доступа..."
run_on_server "cd $SERVER_PATH && chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"

echo "✅ Деплой завершен успешно!"
echo "🌐 Проект доступен по адресу: http://82.146.39.18"
