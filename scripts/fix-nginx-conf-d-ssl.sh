#!/bin/bash

# Скрипт для исправления SSL сертификатов в /etc/nginx/conf.d/default.conf

DOMAIN="theologybooks.org"
CONFIG_FILE="/etc/nginx/conf.d/default.conf"
BACKUP_FILE="${CONFIG_FILE}.backup.$(date +%Y%m%d_%H%M%S)"

echo "🔧 Исправление SSL сертификатов в ${CONFIG_FILE}"
echo "================================================"
echo ""

# Проверяем, запущен ли скрипт от root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Ошибка: Скрипт должен быть запущен с правами root (используйте sudo)"
    exit 1
fi

# Проверяем наличие файла
if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ Файл не найден: $CONFIG_FILE"
    exit 1
fi

# Создаем резервную копию
cp "$CONFIG_FILE" "$BACKUP_FILE"
echo "✅ Резервная копия создана: $BACKUP_FILE"
echo ""

# Проверяем текущие пути
echo "Текущие пути к SSL сертификатам:"
grep -E "ssl_certificate" "$CONFIG_FILE" | grep -v "^#"
echo ""

# Заменяем пути на правильные
echo "Заменяем пути на Let's Encrypt сертификаты..."
sed -i 's|/etc/nginx/ssl/theologybooks.org.crt|/etc/letsencrypt/live/theologybooks.org/fullchain.pem|g' "$CONFIG_FILE"
sed -i 's|/etc/nginx/ssl/theologybooks.org.key|/etc/letsencrypt/live/theologybooks.org/privkey.pem|g' "$CONFIG_FILE"

echo "✅ Пути заменены"
echo ""

# Показываем новые пути
echo "Новые пути к SSL сертификатам:"
grep -E "ssl_certificate" "$CONFIG_FILE" | grep -v "^#"
echo ""

# Проверяем синтаксис
echo "🔍 Проверка синтаксиса Nginx..."
if nginx -t; then
    echo "✅ Синтаксис правильный"
    echo ""
    echo "🔄 Перезагрузка Nginx..."
    if systemctl reload nginx; then
        echo "✅ Nginx успешно перезагружен"
        echo ""
        echo "================================================"
        echo "✅ Конфигурация успешно исправлена!"
        echo ""
        echo "Проверьте работу сайта:"
        echo "  https://${DOMAIN}"
        echo ""
        echo "Если что-то пошло не так, восстановите из резервной копии:"
        echo "  sudo cp $BACKUP_FILE $CONFIG_FILE"
        echo "  sudo nginx -t && sudo systemctl reload nginx"
    else
        echo "❌ Ошибка при перезагрузке Nginx"
        exit 1
    fi
else
    echo "❌ Ошибка в синтаксисе конфигурации!"
    echo "Восстанавливаем из резервной копии..."
    cp "$BACKUP_FILE" "$CONFIG_FILE"
    nginx -t
    exit 1
fi
