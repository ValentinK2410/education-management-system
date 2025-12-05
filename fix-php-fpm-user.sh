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

# Определяем всех возможных пользователей PHP-FPM
echo "👥 Поиск всех пользователей PHP-FPM..."
ALL_USERS=$(ps aux | grep "php-fpm: pool" | grep -v grep | awk '{print $1}' | sort -u)
echo "Найденные пользователи: $ALL_USERS"

# Определяем пользователя из активного процесса
ACTIVE_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')

# Также проверяем через тестовый PHP скрипт (если доступен)
if [ -f public/test-views-write.php ]; then
    echo "Проверка пользователя через PHP скрипт..."
    PHP_USER=$(php -r "echo posix_getpwuid(posix_geteuid())['name'];")
    echo "Пользователь PHP (через CLI): $PHP_USER"
fi

echo "👤 Основной активный пользователь PHP-FPM worker: $ACTIVE_USER"

# Если не удалось определить, пробуем www-root (ISPmanager часто использует этот пользователь)
if [ -z "$ACTIVE_USER" ]; then
    echo "⚠️  Не удалось определить пользователя PHP-FPM из процессов"
    # Проверяем, существует ли www-root
    if id "www-root" &>/dev/null; then
        echo "Найден пользователь www-root, используем его"
        ACTIVE_USER="www-root"
    else
        echo "Используем www-data по умолчанию"
        ACTIVE_USER="www-data"
    fi
fi

# Если активный пользователь www-data, но PHP работает от www-root, используем www-root
if [ "$ACTIVE_USER" = "www-data" ] && id "www-root" &>/dev/null; then
    echo "⚠️  Обнаружен пользователь www-root, проверяем его использование..."
    # Проверяем, есть ли процессы PHP-FPM от www-root
    if ps aux | grep "php-fpm" | grep -q "www-root"; then
        echo "✅ Найдены процессы PHP-FPM от www-root, используем www-root"
        ACTIVE_USER="www-root"
    fi
fi

echo ""
echo "🔧 Исправление прав доступа для пользователя: $ACTIVE_USER"
echo "=================================================="

# Удаляем старые файлы представлений
echo "Очистка старых представлений..."
rm -rf storage/framework/views/*

# Создаем все необходимые директории
echo "Создание директорий..."
mkdir -p storage/app/public/certificate-templates
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Устанавливаем права для родительских директорий (важно!)
echo "Установка прав для родительских директорий..."
chmod 755 storage
chmod 755 storage/framework

# Устанавливаем владельца для всех директорий
echo "Установка владельца: $ACTIVE_USER"
chown -R $ACTIVE_USER:$ACTIVE_USER storage
chown -R $ACTIVE_USER:$ACTIVE_USER bootstrap/cache

# Устанавливаем права доступа (775 для директорий, 664 для файлов)
echo "Установка прав доступа..."
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Особое внимание к директории views - используем 777 для гарантии
chmod 777 storage/framework/views
chown $ACTIVE_USER:$ACTIVE_USER storage/framework/views

# Если есть другие пользователи PHP-FPM, добавляем их в группу
for user in $ALL_USERS; do
    if [ "$user" != "$ACTIVE_USER" ]; then
        echo "Добавляем права для дополнительного пользователя: $user"
        # Добавляем пользователя в группу www-data или создаем общую группу
        usermod -a -G www-data $user 2>/dev/null || true
    fi
done

# Определяем группу пользователя
ACTIVE_GROUP=$(id -gn $ACTIVE_USER 2>/dev/null || echo $ACTIVE_USER)

# Устанавливаем группу для всех файлов
echo "Установка группы: $ACTIVE_GROUP"
chgrp -R $ACTIVE_GROUP storage 2>/dev/null || chgrp -R $ACTIVE_USER storage
chgrp -R $ACTIVE_GROUP bootstrap/cache 2>/dev/null || chgrp -R $ACTIVE_USER bootstrap/cache

# Устанавливаем setgid бит для директорий (новые файлы будут наследовать группу)
find storage -type d -exec chmod g+s {} \;
find bootstrap/cache -type d -exec chmod g+s {} \;

echo "✅ Права доступа установлены"
echo ""
echo "Проверка:"
ls -la storage/framework/ | grep views
ls -la storage/framework/views | head -3

# Тестируем запись от имени пользователя
echo ""
echo "🧪 Тест записи от имени $ACTIVE_USER:"
sudo -u $ACTIVE_USER touch storage/framework/views/.test_write 2>&1
if [ -f storage/framework/views/.test_write ]; then
    sudo -u $ACTIVE_USER rm storage/framework/views/.test_write
    echo "✅ Тест записи успешен!"
else
    echo "❌ Тест записи не удался"
    echo "Попробуем с правами 777..."
    chmod 777 storage/framework/views
    sudo -u $ACTIVE_USER touch storage/framework/views/.test_write 2>&1
    if [ -f storage/framework/views/.test_write ]; then
        sudo -u $ACTIVE_USER rm storage/framework/views/.test_write
        echo "✅ Тест записи успешен с правами 777!"
        echo "⚠️  ВНИМАНИЕ: Используются права 777 (небезопасно, но работает)"
    fi
fi

# Очищаем кэш Laravel
echo ""
echo "Очистка кэша Laravel..."
php artisan view:clear
php artisan config:clear
php artisan cache:clear

echo ""
echo "✅ Готово! Попробуйте обновить страницу."
