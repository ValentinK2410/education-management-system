#!/bin/bash
# Скрипт для исправления пользователя PHP-FPM и прав доступа
# Выполните на сервере

cd /var/www/www-root/data/www/m.dekan.pro

echo "🔍 Поиск конфигурации PHP-FPM..."
echo "=================================="

# Ищем все конфигурационные файлы PHP-FPM
FPM_CONFIGS=$(find /opt/php84 /etc/php -name "*.conf" -path "*/php-fpm.d/*" -o -name "www.conf" 2>/dev/null)

if [ -z "$FPM_CONFIGS" ]; then
    echo "⚠️  Конфигурационные файлы не найдены"
    echo "Проверяем активные процессы PHP-FPM:"
    ps aux | grep php-fpm | grep -v grep
else
    echo "Найденные конфигурационные файлы:"
    echo "$FPM_CONFIGS"
    echo ""

    for config in $FPM_CONFIGS; do
        echo "📄 Файл: $config"
        grep -E "^user|^group" "$config" 2>/dev/null | head -2
        echo ""
    done
fi

# Определяем пользователя из активного процесса
ACTIVE_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')
echo "👤 Активный пользователь PHP-FPM worker: $ACTIVE_USER"

if [ -z "$ACTIVE_USER" ]; then
    echo "❌ Не удалось определить пользователя PHP-FPM"
    exit 1
fi

echo ""
echo "🔧 Исправление прав доступа для пользователя: $ACTIVE_USER"
echo "=================================================="

# Устанавливаем владельца
chown -R $ACTIVE_USER:$ACTIVE_USER storage
chown -R $ACTIVE_USER:$ACTIVE_USER bootstrap/cache

# Устанавливаем права доступа
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Убеждаемся, что директория views существует
mkdir -p storage/framework/views
chmod 775 storage/framework/views
chown $ACTIVE_USER:$ACTIVE_USER storage/framework/views

echo "✅ Права доступа установлены"
echo ""
echo "Проверка:"
ls -la storage/framework/ | grep views
