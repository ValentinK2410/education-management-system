#!/bin/bash

# Скрипт автоматической настройки нового сервера
# Использование: ./setup-new-server.sh

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Настройка нового сервера ===${NC}\n"

# Проверка, что скрипт запущен от root или с sudo
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}✗ Скрипт должен быть запущен от root или с sudo${NC}"
    exit 1
fi

# Определение пути к проекту
read -p "Введите путь к проекту (по умолчанию: /var/www/www-root/data/www/m.dekan.pro): " PROJECT_PATH
PROJECT_PATH=${PROJECT_PATH:-/var/www/www-root/data/www/m.dekan.pro}

# Проверка существования директории проекта
if [ ! -d "$PROJECT_PATH" ]; then
    echo -e "${RED}✗ Директория проекта не найдена: ${PROJECT_PATH}${NC}"
    echo -e "${YELLOW}Используйте скрипт install-from-github.sh для установки через GitHub${NC}"
    exit 1
fi

cd "$PROJECT_PATH"

# Проверка, что это Git репозиторий
if [ ! -d ".git" ]; then
    echo -e "${YELLOW}⚠ Это не Git репозиторий. Рекомендуется использовать Git для управления версиями${NC}"
fi

# Определение пользователя PHP-FPM
PHP_USER=$(ps aux | grep "php-fpm: pool" | grep -v grep | head -1 | awk '{print $1}')
if [ -z "$PHP_USER" ]; then
    if id "www-data" &>/dev/null; then
        PHP_USER="www-data"
    elif id "www-root" &>/dev/null; then
        PHP_USER="www-root"
    else
        echo -e "${RED}✗ Не удалось определить пользователя PHP-FPM${NC}"
        exit 1
    fi
fi

echo -e "${GREEN}Используется пользователь PHP-FPM: ${PHP_USER}${NC}\n"

# Создание необходимых директорий
echo -e "${YELLOW}Создание директорий...${NC}"
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p storage/app/public/{avatars,certificate-templates,certificate-elements}
mkdir -p storage/app/backups/database
mkdir -p bootstrap/cache

# Установка прав доступа
echo -e "${YELLOW}Установка прав доступа...${NC}"
chown -R "$PHP_USER:$PHP_USER" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}✓ Права доступа установлены${NC}"

# Создание символической ссылки для storage
echo -e "${YELLOW}Создание символической ссылки storage...${NC}"
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Символическая ссылка создана${NC}"
else
    echo -e "${YELLOW}⚠ Символическая ссылка уже существует${NC}"
fi

# Очистка кэша
echo -e "${YELLOW}Очистка кэша...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Оптимизация для production
echo -e "${YELLOW}Оптимизация для production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo -e "${GREEN}✓ Оптимизация завершена${NC}"

# Проверка .env файла
echo -e "${YELLOW}Проверка .env файла...${NC}"
if [ ! -f ".env" ]; then
    echo -e "${RED}✗ Файл .env не найден${NC}"
    echo -e "${YELLOW}Создайте файл .env из .env.example и настройте его${NC}"
else
    echo -e "${GREEN}✓ Файл .env найден${NC}"
    
    # Проверка APP_KEY
    if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
        echo -e "${YELLOW}Генерация APP_KEY...${NC}"
        php artisan key:generate
    else
        echo -e "${GREEN}✓ APP_KEY настроен${NC}"
    fi
fi

echo ""
echo -e "${GREEN}=== Настройка сервера завершена ===${NC}"
echo -e "${YELLOW}Следующие шаги:${NC}"
echo -e "1. Настройте файл .env с правильными параметрами БД"
echo -e "2. Импортируйте базу данных"
echo -e "3. Перенесите файлы из storage/app/public"
echo -e "4. Настройте веб-сервер (Nginx/Apache)"
echo -e "5. Настройте SSL сертификат"

