#!/bin/bash

# Скрипт переноса файлов со старого сервера на новый через rsync
# Использование: ./transfer-files.sh [user@new-server]

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Перенос файлов на новый сервер ===${NC}"

# Проверка аргументов
if [ -z "$1" ]; then
    read -p "Введите адрес нового сервера (user@host): " NEW_SERVER
else
    NEW_SERVER="$1"
fi

# Определение пути к проекту
read -p "Введите путь к проекту на старом сервере (по умолчанию: /var/www/www-root/data/www/m.dekan.pro): " OLD_PATH
OLD_PATH=${OLD_PATH:-/var/www/www-root/data/www/m.dekan.pro}

read -p "Введите путь к проекту на новом сервере (по умолчанию: /var/www/www-root/data/www/m.dekan.pro): " NEW_PATH
NEW_PATH=${NEW_PATH:-/var/www/www-root/data/www/m.dekan.pro}

echo -e "${YELLOW}Перенос файлов из ${OLD_PATH} на ${NEW_SERVER}:${NEW_PATH}${NC}"

# Проверка существования директории на старом сервере
if [ ! -d "$OLD_PATH" ]; then
    echo -e "${RED}✗ Директория не найдена: ${OLD_PATH}${NC}"
    exit 1
fi

# Создание директорий на новом сервере
echo -e "${YELLOW}Создание необходимых директорий на новом сервере...${NC}"
ssh "$NEW_SERVER" "mkdir -p ${NEW_PATH}/storage/app/public/{avatars,certificate-templates,certificate-elements}"

# Перенос файлов storage/app/public
echo -e "${YELLOW}Перенос загруженных файлов (storage/app/public)...${NC}"
rsync -avz --progress \
    --exclude '*.log' \
    "${OLD_PATH}/storage/app/public/" \
    "${NEW_SERVER}:${NEW_PATH}/storage/app/public/"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Файлы storage/app/public успешно перенесены${NC}"
else
    echo -e "${RED}✗ Ошибка при переносе файлов${NC}"
    exit 1
fi

# Перенос резервных копий (опционально)
read -p "Перенести резервные копии базы данных? (y/n, по умолчанию: n): " TRANSFER_BACKUPS
TRANSFER_BACKUPS=${TRANSFER_BACKUPS:-n}

if [ "$TRANSFER_BACKUPS" = "y" ] || [ "$TRANSFER_BACKUPS" = "Y" ]; then
    echo -e "${YELLOW}Перенос резервных копий...${NC}"
    ssh "$NEW_SERVER" "mkdir -p ${NEW_PATH}/storage/app/backups/database"
    
    rsync -avz --progress \
        "${OLD_PATH}/storage/app/backups/database/" \
        "${NEW_SERVER}:${NEW_PATH}/storage/app/backups/database/"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Резервные копии успешно перенесены${NC}"
    else
        echo -e "${YELLOW}⚠ Резервные копии не найдены или произошла ошибка${NC}"
    fi
fi

# Проверка размера перенесенных файлов
echo -e "${YELLOW}Проверка размера перенесенных файлов...${NC}"
ssh "$NEW_SERVER" "du -sh ${NEW_PATH}/storage/app/public/* 2>/dev/null | head -10"

echo -e "${GREEN}=== Перенос файлов завершен ===${NC}"

