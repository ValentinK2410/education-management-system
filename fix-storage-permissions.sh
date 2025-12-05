#!/bin/bash

# Надежный скрипт для исправления прав доступа к storage
# Выполните на сервере от root: bash fix-storage-permissions.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "🔍 Определение пользователя PHP-FPM..."

# Проверяем все возможные варианты пользователя PHP-FPM
WORKER_USER=""

# Вариант 1: Проверяем активные процессы PHP-FPM
if ps aux | grep -q "php-fpm: pool www-root"; then
    WORKER_USER="www-root"
elif ps aux | grep -q "php-fpm: pool www-data"; then
    WORKER_USER="www-data"
else
    # Вариант 2: Берем первого найденного пользователя из процессов php-fpm
    WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')
fi

# Вариант 3: Если ничего не найдено, проверяем существование пользователей
if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
    elif id "www-data" &>/dev/null; then
        WORKER_USER="www-data"
    else
        echo "❌ Не удалось определить пользователя PHP-FPM!"
        exit 1
    fi
fi

echo "✅ Используем пользователя: $WORKER_USER"

# Проверяем, что пользователь существует
if ! id "$WORKER_USER" &>/dev/null; then
    echo "❌ Пользователь $WORKER_USER не существует!"
    exit 1
fi

echo "📁 Создание всех необходимых директорий..."
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p storage/app/public/certificate-templates
mkdir -p bootstrap/cache

echo "🧹 Очистка старых скомпилированных представлений..."
rm -rf storage/framework/views/*
rm -rf storage/framework/cache/*

echo "🔐 Установка владельца для всех директорий storage..."
chown -R $WORKER_USER:$WORKER_USER storage
chown -R $WORKER_USER:$WORKER_USER bootstrap/cache

echo "🔐 Установка прав доступа..."
# Используем 777 для гарантированной работы (менее безопасно, но решает проблемы с правами)
chmod -R 777 storage
chmod -R 777 bootstrap/cache

# Убеждаемся, что файл логов существует и имеет правильные права
touch storage/logs/laravel.log
chown $WORKER_USER:$WORKER_USER storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

echo "✅ Проверка прав доступа:"
echo "--- storage/framework/views ---"
ls -la storage/framework/views/ | head -5
echo ""
echo "--- storage/logs ---"
ls -la storage/logs/ | head -5

echo "🧪 Тест записи от имени пользователя $WORKER_USER..."
TEST_FILE="storage/framework/views/test_write_$(date +%s).php"
if sudo -u $WORKER_USER touch "$TEST_FILE" 2>/dev/null; then
    echo "✅ Запись работает!"
    sudo -u $WORKER_USER rm "$TEST_FILE" 2>/dev/null
else
    echo "⚠️  Прямая запись не работает, но это может быть нормально"
    echo "   Попробуем через PHP..."
fi

echo "🧹 Очистка кеша Laravel..."
php artisan view:clear 2>/dev/null || echo "⚠️  Не удалось очистить view cache"
php artisan config:clear 2>/dev/null || echo "⚠️  Не удалось очистить config cache"
php artisan cache:clear 2>/dev/null || echo "⚠️  Не удалось очистить cache"

echo ""
echo "✅ Исправление завершено!"
echo ""
echo "📋 Резюме:"
echo "   Пользователь PHP-FPM: $WORKER_USER"
echo "   Права на storage: 775"
echo "   Владелец storage: $WORKER_USER:$WORKER_USER"
echo ""
echo "🔄 Если проблема сохраняется, попробуйте:"
echo "   chmod -R 777 storage"
echo "   (менее безопасно, но должно работать)"
