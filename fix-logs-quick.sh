#!/bin/bash

# Быстрое исправление прав доступа к логам Laravel
# Выполните на сервере: bash fix-logs-quick.sh

cd /var/www/www-root/data/www/m.dekan.pro

echo "🔧 Исправление прав доступа к логам Laravel..."

# Определяем пользователя PHP-FPM
WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')

if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
        echo "✅ Используем пользователя: www-root"
    else
        WORKER_USER="www-data"
        echo "✅ Используем пользователя: www-data"
    fi
else
    echo "✅ Определен пользователь PHP-FPM: $WORKER_USER"
fi

# Если активный пользователь www-data, но PHP работает от www-root, используем www-root
if [ "$WORKER_USER" = "www-data" ] && id "www-root" &>/dev/null; then
    if ps aux | grep "php-fpm" | grep -q "www-root"; then
        echo "⚠️  Обнаружен пользователь www-root, используем его"
        WORKER_USER="www-root"
    fi
fi

echo "📁 Создание директории и файла логов..."
mkdir -p storage/logs
touch storage/logs/laravel.log

echo "🔐 Установка прав доступа..."
chown -R $WORKER_USER:$WORKER_USER storage/logs
chmod -R 775 storage/logs
chmod 664 storage/logs/laravel.log

echo "✅ Проверка прав доступа:"
ls -la storage/logs/ | head -5

echo "🧪 Тест записи..."
sudo -u $WORKER_USER touch storage/logs/test_write_$(date +%s).log 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Запись работает!"
    sudo -u $WORKER_USER rm storage/logs/test_write_*.log 2>/dev/null
else
    echo "❌ Ошибка записи. Попробуйте выполнить от root:"
    echo "   chown -R $WORKER_USER:$WORKER_USER storage/logs"
    echo "   chmod -R 775 storage/logs"
fi

echo "✅ Готово!"
