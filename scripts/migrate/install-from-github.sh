#!/bin/bash

# Скрипт полной установки системы через GitHub
# Использование: ./install-from-github.sh [github-url] [install-path]

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Установка Education Management System через GitHub ===${NC}\n"

# Проверка, что скрипт запущен от root или с sudo
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}✗ Скрипт должен быть запущен от root или с sudo${NC}"
    exit 1
fi

# Получение URL репозитория GitHub
if [ -z "$1" ]; then
    read -p "Введите URL репозитория GitHub (например: https://github.com/username/education-management-system.git): " GITHUB_URL
else
    GITHUB_URL="$1"
fi

# Получение пути установки
if [ -z "$2" ]; then
    read -p "Введите путь для установки (по умолчанию: /var/www/www-root/data/www/m.dekan.pro): " INSTALL_PATH
    INSTALL_PATH=${INSTALL_PATH:-/var/www/www-root/data/www/m.dekan.pro}
else
    INSTALL_PATH="$2"
fi

# Проверка наличия Git
if ! command -v git &> /dev/null; then
    echo -e "${YELLOW}Установка Git...${NC}"
    apt-get update
    apt-get install -y git
fi

# Создание родительской директории, если её нет
PARENT_DIR=$(dirname "$INSTALL_PATH")
mkdir -p "$PARENT_DIR"

# Удаление директории, если она существует
if [ -d "$INSTALL_PATH" ]; then
    echo -e "${YELLOW}Директория ${INSTALL_PATH} уже существует${NC}"
    read -p "Удалить существующую директорию? (y/n): " REMOVE_EXISTING
    if [ "$REMOVE_EXISTING" = "y" ] || [ "$REMOVE_EXISTING" = "Y" ]; then
        rm -rf "$INSTALL_PATH"
        echo -e "${GREEN}✓ Старая директория удалена${NC}"
    else
        echo -e "${RED}✗ Установка отменена${NC}"
        exit 1
    fi
fi

# Клонирование репозитория
echo -e "${YELLOW}Клонирование репозитория из GitHub...${NC}"
git clone "$GITHUB_URL" "$INSTALL_PATH"

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Ошибка при клонировании репозитория${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Репозиторий успешно клонирован${NC}"

cd "$INSTALL_PATH"

# Проверка наличия Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Composer не найден. Установка Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Установка зависимостей
echo -e "${YELLOW}Установка зависимостей Composer...${NC}"
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Ошибка при установке зависимостей${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Зависимости установлены${NC}"

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

echo -e "${GREEN}Используется пользователь PHP-FPM: ${PHP_USER}${NC}"

# Создание необходимых директорий
echo -e "${YELLOW}Создание директорий...${NC}"
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p storage/app/public/{avatars,certificate-templates,certificate-elements}
mkdir -p storage/app/backups/database
mkdir -p bootstrap/cache
mkdir -p backups/migration

# Установка прав доступа
echo -e "${YELLOW}Установка прав доступа...${NC}"
chown -R "$PHP_USER:$PHP_USER" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo -e "${GREEN}✓ Права доступа установлены${NC}"

# Создание .env файла
echo -e "${YELLOW}Создание .env файла...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Файл .env создан из .env.example${NC}"
    else
        echo -e "${YELLOW}⚠ Файл .env.example не найден, создан пустой .env${NC}"
        touch .env
    fi
else
    echo -e "${YELLOW}⚠ Файл .env уже существует${NC}"
fi

# Генерация APP_KEY
echo -e "${YELLOW}Генерация APP_KEY...${NC}"
php artisan key:generate --force

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

echo ""
echo -e "${GREEN}=== Установка завершена ===${NC}"
echo -e "${YELLOW}Следующие шаги:${NC}"
echo -e "1. Настройте файл .env:"
echo -e "   nano ${INSTALL_PATH}/.env"
echo -e ""
echo -e "2. Создайте базу данных и пользователя MySQL"
echo -e ""
echo -e "3. Импортируйте базу данных:"
echo -e "   cd ${INSTALL_PATH}"
echo -e "   ./scripts/migrate/import-database.sh /path/to/backup.sql.gz"
echo -e ""
echo -e "4. Перенесите файлы из storage/app/public (если нужно)"
echo -e ""
echo -e "5. Настройте веб-сервер (Nginx/Apache)"
echo -e ""
echo -e "6. Настройте SSL сертификат"

