#!/bin/bash

# Скрипт для исправления прав доступа к логам Laravel
# Выполните на сервере: bash fix-logs-permissions.sh

cd /var/www/www-root/data/www/m.dekan.pro

# Определяем пользователя PHP-FPM
WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')

if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
    else
        WORKER_USER="www-data"
    fi
fi

echo "Пользователь PHP-FPM: $WORKER_USER"

# Создаем директорию логов, если её нет
mkdir -p storage/logs

# Создаем файл логов, если его нет
touch storage/logs/laravel.log

# Устанавливаем владельца
chown -R $WORKER_USER:$WORKER_USER storage/logs

# Устанавливаем права доступа
chmod -R 775 storage/logs
chmod 664 storage/logs/laravel.log

# Проверяем права
echo "Проверка прав доступа:"
ls -la storage/logs/

# Проверяем возможность записи от имени пользователя PHP-FPM
echo "Проверка возможности записи:"
sudo -u $WORKER_USER touch storage/logs/test_write.log 2>&1
if [ -f storage/logs/test_write.log ]; then
    sudo -u $WORKER_USER rm storage/logs/test_write.log
    echo "✅ Права на запись работают"
else
    echo "❌ Ошибка прав доступа"
fi

echo "✅ Готово!"
