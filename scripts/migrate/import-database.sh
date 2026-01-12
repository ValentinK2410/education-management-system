#!/bin/bash

# Скрипт импорта базы данных на новый сервер
# Использование: ./import-database.sh [путь_к_файлу.sql.gz]

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Импорт базы данных на новый сервер ===${NC}"

# Проверка аргументов
if [ -z "$1" ]; then
    read -p "Введите путь к файлу резервной копии: " BACKUP_FILE
else
    BACKUP_FILE="$1"
fi

# Проверка существования файла
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}✗ Файл не найден: ${BACKUP_FILE}${NC}"
    exit 1
fi

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

echo -e "${YELLOW}Импорт базы данных ${DB_NAME} из ${BACKUP_FILE}...${NC}"

# Распаковка, если файл сжат
TEMP_SQL="/tmp/import_${DB_NAME}_$(date +%s).sql"
if [[ "$BACKUP_FILE" == *.gz ]]; then
    echo -e "${YELLOW}Распаковка файла...${NC}"
    gunzip -c "$BACKUP_FILE" > "$TEMP_SQL"
else
    cp "$BACKUP_FILE" "$TEMP_SQL"
fi

# Импорт базы данных
if [ -z "$DB_PASS" ]; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" < "$TEMP_SQL"
else
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$TEMP_SQL"
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ База данных успешно импортирована${NC}"
    
    # Удаление временного файла
    rm -f "$TEMP_SQL"
    
    # Проверка количества таблиц
    if [ -z "$DB_PASS" ]; then
        TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")
    else
        TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")
    fi
    
    echo -e "${GREEN}✓ Количество таблиц в базе данных: ${TABLE_COUNT}${NC}"
else
    echo -e "${RED}✗ Ошибка при импорте базы данных${NC}"
    rm -f "$TEMP_SQL"
    exit 1
fi

echo -e "${GREEN}=== Импорт завершен ===${NC}"

