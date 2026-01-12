#!/bin/bash

# Скрипт экспорта базы данных со старого сервера
# Использование: ./export-database.sh

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Экспорт базы данных со старого сервера ===${NC}"

# Запрос параметров подключения к БД
read -p "Введите имя пользователя MySQL (по умолчанию: root): " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Введите пароль MySQL: " DB_PASS
echo ""

read -p "Введите имя базы данных (по умолчанию: education_system): " DB_NAME
DB_NAME=${DB_NAME:-education_system}

read -p "Введите хост MySQL (по умолчанию: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Введите порт MySQL (по умолчанию: 3306): " DB_PORT
DB_PORT=${DB_PORT:-3306}

# Создание директории для бэкапов, если её нет
BACKUP_DIR="./backups/migration"
mkdir -p "$BACKUP_DIR"

# Имя файла бэкапа
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"
BACKUP_FILE_GZ="${BACKUP_FILE}.gz"

echo -e "${YELLOW}Экспорт базы данных ${DB_NAME}...${NC}"

# Экспорт базы данных
if [ -z "$DB_PASS" ]; then
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" \
        --single-transaction \
        --routines \
        --triggers \
        --quick \
        --lock-tables=false \
        "$DB_NAME" > "$BACKUP_FILE"
else
    mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
        --single-transaction \
        --routines \
        --triggers \
        --quick \
        --lock-tables=false \
        "$DB_NAME" > "$BACKUP_FILE"
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ База данных успешно экспортирована${NC}"
    
    # Сжатие файла
    echo -e "${YELLOW}Сжатие файла...${NC}"
    gzip "$BACKUP_FILE"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Файл сжат: ${BACKUP_FILE_GZ}${NC}"
        echo -e "${GREEN}Размер файла: $(du -h "$BACKUP_FILE_GZ" | cut -f1)${NC}"
    else
        echo -e "${RED}✗ Ошибка при сжатии файла${NC}"
        exit 1
    fi
else
    echo -e "${RED}✗ Ошибка при экспорте базы данных${NC}"
    exit 1
fi

echo -e "${GREEN}=== Экспорт завершен ===${NC}"
echo -e "${YELLOW}Файл резервной копии: ${BACKUP_FILE_GZ}${NC}"
echo -e "${YELLOW}Для переноса на новый сервер используйте:${NC}"
echo -e "scp ${BACKUP_FILE_GZ} user@new-server:/tmp/"

