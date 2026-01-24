#!/bin/bash

# Скрипт для поиска всех конфигураций Nginx для домена

DOMAIN="theologybooks.org"

echo "🔍 Поиск конфигураций Nginx для ${DOMAIN}"
echo "=========================================="
echo ""

echo "1. Поиск в sites-available:"
echo "---------------------------"
find /etc/nginx/sites-available/ -type f -exec grep -l "${DOMAIN}" {} \; 2>/dev/null | while read file; do
    echo "Найден: $file"
    grep -n "${DOMAIN}\|ssl_certificate" "$file" | head -5
    echo ""
done

echo "2. Поиск в sites-enabled:"
echo "-------------------------"
find /etc/nginx/sites-enabled/ -type f -exec grep -l "${DOMAIN}" {} \; 2>/dev/null | while read file; do
    echo "Найден (активен): $file"
    grep -n "${DOMAIN}\|ssl_certificate" "$file" | head -5
    echo ""
done

echo "3. Поиск конфигурации с /etc/nginx/ssl/:"
echo "----------------------------------------"
grep -r "/etc/nginx/ssl.*${DOMAIN}" /etc/nginx/ 2>/dev/null | head -10

echo ""
echo "4. Активная конфигурация из nginx -T:"
echo "--------------------------------------"
sudo nginx -T 2>&1 | grep -B 3 -A 10 "${DOMAIN}" | grep -E "server_name|ssl_certificate|listen" | head -20

echo ""
echo "5. Все включенные конфигурации:"
echo "-------------------------------"
ls -la /etc/nginx/sites-enabled/ 2>/dev/null || echo "Директория не найдена"

echo ""
echo "=========================================="
echo "Поиск завершен"
