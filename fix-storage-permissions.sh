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

# Установить права доступа для всех директорий storage
echo "🔐 Установка прав доступа для storage..."
# Используем 777 для views и cache, так как PHP-FPM требует полные права для записи скомпилированных представлений
chmod -R 777 storage/framework/views
chmod -R 777 storage/framework/cache
chmod -R 777 storage/framework/sessions
chmod -R 775 storage/logs
chmod -R 777 bootstrap/cache/
# Родительские директории могут иметь более ограниченные права
chmod 775 storage/
chmod 775 storage/framework/
echo "✅ Права доступа установлены"
echo ""

# Назначить владельца www-data для всех директорий storage
echo "👤 Назначение владельца www-data:www-data..."
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/
echo "✅ Владелец назначен"
echo ""

# Создать файл логов если его нет и установить правильные права
echo "📄 Проверка и настройка файла логов..."
mkdir -p storage/logs
if [ ! -f storage/logs/laravel.log ]; then
    touch storage/logs/laravel.log
fi
chmod 666 storage/logs/laravel.log
chown www-data:www-data storage/logs/laravel.log
chmod 777 storage/logs/
echo "✅ Файл логов настроен"
echo ""

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

# Попытка перезапустить PHP-FPM (автоматический поиск правильного сервиса)
echo "🔄 Попытка перезапустить PHP-FPM..."
PHP_FPM_SERVICE=$(systemctl list-units --type=service --all | grep -i "php.*fpm" | head -1 | awk '{print $1}')

if [ -n "$PHP_FPM_SERVICE" ]; then
    echo "   Найден сервис: $PHP_FPM_SERVICE"
    systemctl restart "$PHP_FPM_SERVICE" 2>/dev/null && echo "   ✅ PHP-FPM перезапущен" || echo "   ⚠️ Не удалось перезапустить PHP-FPM (может потребоваться sudo)"
else
    echo "   ⚠️ Сервис PHP-FPM не найден автоматически"
    echo "   Попробуйте вручную:"
    echo "   - systemctl restart php-fpm"
    echo "   - systemctl restart php8.1-fpm"
    echo "   - systemctl restart php8.2-fpm"
    echo "   - systemctl restart php8.3-fpm"
    echo "   - systemctl restart php8.4-fpm"
    echo "   Или перезапустите веб-сервер (nginx/apache)"
fi
echo ""

echo "✅ Исправление прав доступа завершено!"
echo ""

