#!/bin/bash

# Скрипт проверки готовности сервера для миграции
# Использование: ./check-server-requirements.sh

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Проверка готовности сервера ===${NC}\n"

ERRORS=0
WARNINGS=0

# Проверка PHP
echo -e "${YELLOW}Проверка PHP...${NC}"
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
    PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)
    
    if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
        echo -e "${GREEN}✓ PHP ${PHP_VERSION} установлен${NC}"
    else
        echo -e "${RED}✗ Требуется PHP 8.2+, установлен ${PHP_VERSION}${NC}"
        ((ERRORS++))
    fi
    
    # Проверка расширений PHP
    REQUIRED_EXTENSIONS=("pdo_mysql" "mbstring" "xml" "curl" "zip" "gd" "fileinfo")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -q "^${ext}$"; then
            echo -e "${GREEN}  ✓ Расширение ${ext} установлено${NC}"
        else
            echo -e "${RED}  ✗ Расширение ${ext} не установлено${NC}"
            ((ERRORS++))
        fi
    done
else
    echo -e "${RED}✗ PHP не установлен${NC}"
    ((ERRORS++))
fi

echo ""

# Проверка MySQL
echo -e "${YELLOW}Проверка MySQL...${NC}"
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version | awk '{print $5}' | cut -d, -f1)
    echo -e "${GREEN}✓ MySQL ${MYSQL_VERSION} установлен${NC}"
else
    echo -e "${RED}✗ MySQL не установлен${NC}"
    ((ERRORS++))
fi

if command -v mysqldump &> /dev/null; then
    echo -e "${GREEN}✓ mysqldump доступен${NC}"
else
    echo -e "${RED}✗ mysqldump не найден${NC}"
    ((ERRORS++))
fi

echo ""

# Проверка Composer
echo -e "${YELLOW}Проверка Composer...${NC}"
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | awk '{print $3}')
    echo -e "${GREEN}✓ Composer ${COMPOSER_VERSION} установлен${NC}"
else
    echo -e "${RED}✗ Composer не установлен${NC}"
    ((ERRORS++))
fi

echo ""

# Проверка Git
echo -e "${YELLOW}Проверка Git...${NC}"
if command -v git &> /dev/null; then
    GIT_VERSION=$(git --version | awk '{print $3}')
    echo -e "${GREEN}✓ Git ${GIT_VERSION} установлен${NC}"
else
    echo -e "${YELLOW}⚠ Git не установлен (не критично для rsync)${NC}"
    ((WARNINGS++))
fi

echo ""

# Проверка rsync
echo -e "${YELLOW}Проверка rsync...${NC}"
if command -v rsync &> /dev/null; then
    RSYNC_VERSION=$(rsync --version | head -1 | awk '{print $3}')
    echo -e "${GREEN}✓ rsync ${RSYNC_VERSION} установлен${NC}"
else
    echo -e "${RED}✗ rsync не установлен${NC}"
    ((ERRORS++))
fi

echo ""

# Проверка веб-сервера
echo -e "${YELLOW}Проверка веб-сервера...${NC}"
if command -v nginx &> /dev/null; then
    NGINX_VERSION=$(nginx -v 2>&1 | awk '{print $3}' | cut -d/ -f2)
    echo -e "${GREEN}✓ Nginx ${NGINX_VERSION} установлен${NC}"
elif command -v apache2 &> /dev/null; then
    APACHE_VERSION=$(apache2 -v | head -1 | awk '{print $3}' | cut -d/ -f2)
    echo -e "${GREEN}✓ Apache ${APACHE_VERSION} установлен${NC}"
else
    echo -e "${YELLOW}⚠ Веб-сервер не обнаружен${NC}"
    ((WARNINGS++))
fi

echo ""

# Проверка PHP-FPM
echo -e "${YELLOW}Проверка PHP-FPM...${NC}"
if systemctl is-active --quiet php8.2-fpm || systemctl is-active --quiet php-fpm; then
    echo -e "${GREEN}✓ PHP-FPM запущен${NC}"
else
    echo -e "${YELLOW}⚠ PHP-FPM не запущен${NC}"
    ((WARNINGS++))
fi

echo ""

# Проверка прав доступа
echo -e "${YELLOW}Проверка прав доступа...${NC}"
if [ -w "/var/www" ] || [ -w "/var/www/www-root" ] || [ -w "/var/www/www-root/data/www" ]; then
    echo -e "${GREEN}✓ Права на запись в /var/www есть${NC}"
else
    echo -e "${YELLOW}⚠ Нет прав на запись в /var/www (может потребоваться sudo)${NC}"
    ((WARNINGS++))
fi

echo ""

# Итоги
echo -e "${BLUE}=== Итоги проверки ===${NC}"
if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ Сервер готов к миграции!${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ Сервер готов, но есть предупреждения (${WARNINGS})${NC}"
    exit 0
else
    echo -e "${RED}✗ Найдено ошибок: ${ERRORS}, предупреждений: ${WARNINGS}${NC}"
    echo -e "${RED}Исправьте ошибки перед продолжением миграции${NC}"
    exit 1
fi

