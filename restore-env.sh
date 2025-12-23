#!/bin/bash

# Скрипт для восстановления файла .env на удаленном сервере
# Путь к проекту на сервере
PROJECT_PATH="/var/www/www-root/data/www/m.dekan.pro"

echo "🔧 Восстановление файла .env на сервере..."
echo "📁 Путь к проекту: $PROJECT_PATH"

# Переход в директорию проекта
cd $PROJECT_PATH || exit 1

# Проверка существования .env.example
if [ ! -f ".env.example" ]; then
    echo "❌ Ошибка: файл .env.example не найден!"
    exit 1
fi

# Создание .env из .env.example (если .env не существует)
if [ ! -f ".env" ]; then
    echo "📋 Копирование .env.example в .env..."
    cp .env.example .env
    echo "✅ Файл .env создан из .env.example"
else
    echo "⚠️  Файл .env уже существует. Пропускаем создание."
fi

# Определение пользователя PHP-FPM
WORKER_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')
if [ -z "$WORKER_USER" ]; then
    if id "www-root" &>/dev/null; then
        WORKER_USER="www-root"
    else
        WORKER_USER="www-data"
    fi
fi
echo "👤 Пользователь PHP-FPM: $WORKER_USER"

# Установка прав доступа на .env
chown $WORKER_USER:$WORKER_USER .env
chmod 640 .env
echo "🔒 Права доступа установлены на .env"

# Генерация APP_KEY (если его нет)
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Генерация APP_KEY..."
    php artisan key:generate --force
    echo "✅ APP_KEY сгенерирован"
else
    echo "✅ APP_KEY уже существует"
fi

# Очистка кеша конфигурации
echo "🧹 Очистка кеша конфигурации..."
php artisan config:clear
php artisan cache:clear

echo ""
echo "✅ Восстановление .env завершено!"
echo ""
echo "⚠️  ВАЖНО: Теперь нужно отредактировать .env файл и настроить:"
echo "   - APP_NAME"
echo "   - APP_URL"
echo "   - DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo "   - MAIL настройки (если используются)"
echo "   - Другие необходимые переменные окружения"
echo ""
echo "📝 Для редактирования используйте:"
echo "   nano $PROJECT_PATH/.env"
echo "   или"
echo "   vi $PROJECT_PATH/.env"
