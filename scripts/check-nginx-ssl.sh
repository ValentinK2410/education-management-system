#!/bin/bash

# Скрипт для проверки SSL конфигурации Nginx

DOMAIN="theologybooks.org"
NGINX_CONFIG="/etc/nginx/sites-available/default"

echo "🔍 Проверка SSL конфигурации для ${DOMAIN}"
echo "=========================================="
echo ""

# Проверяем конфигурацию Nginx
echo "1. Проверка SSL сертификатов в конфигурации:"
echo "--------------------------------------------"
if grep -q "ssl_certificate" "$NGINX_CONFIG"; then
    grep "ssl_certificate" "$NGINX_CONFIG" | grep -v "^#"
    
    # Проверяем, используется ли fullchain.pem
    if grep -q "fullchain.pem" "$NGINX_CONFIG"; then
        echo "✅ Используется fullchain.pem (правильно)"
    else
        echo "❌ НЕ используется fullchain.pem!"
        echo "   Используйте fullchain.pem вместо cert.pem для полной цепочки сертификатов"
    fi
else
    echo "❌ SSL сертификаты не найдены в конфигурации!"
fi

echo ""
echo "2. Проверка файлов сертификатов:"
echo "--------------------------------------------"
if [ -f "/etc/letsencrypt/live/${DOMAIN}/fullchain.pem" ]; then
    echo "✅ fullchain.pem существует"
    ls -lh /etc/letsencrypt/live/${DOMAIN}/fullchain.pem
else
    echo "❌ fullchain.pem не найден!"
fi

if [ -f "/etc/letsencrypt/live/${DOMAIN}/cert.pem" ]; then
    echo "✅ cert.pem существует"
    ls -lh /etc/letsencrypt/live/${DOMAIN}/cert.pem
else
    echo "❌ cert.pem не найден!"
fi

echo ""
echo "3. Проверка цепочки сертификатов:"
echo "--------------------------------------------"
CHAIN_CHECK=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | grep -A 5 "Certificate chain")
if [ -n "$CHAIN_CHECK" ]; then
    echo "$CHAIN_CHECK"
else
    echo "⚠️  Не удалось получить информацию о цепочке сертификатов"
fi

echo ""
echo "4. Проверка SSL соединения:"
echo "--------------------------------------------"
VERIFY_RESULT=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | grep "Verify return code")
if [ -n "$VERIFY_RESULT" ]; then
    echo "$VERIFY_RESULT"
    if echo "$VERIFY_RESULT" | grep -q "0 (ok)"; then
        echo "✅ SSL сертификат проверен успешно"
    else
        echo "❌ Проблема с проверкой SSL сертификата"
    fi
else
    echo "⚠️  Не удалось проверить SSL соединение"
fi

echo ""
echo "5. Проверка HTTP редиректа:"
echo "--------------------------------------------"
HTTP_REDIRECT=$(curl -I http://${DOMAIN} 2>&1 | grep -E "HTTP|Location")
if [ -n "$HTTP_REDIRECT" ]; then
    echo "$HTTP_REDIRECT"
    if echo "$HTTP_REDIRECT" | grep -q "301\|302"; then
        if echo "$HTTP_REDIRECT" | grep -q "https"; then
            echo "✅ Правильный редирект на HTTPS"
        else
            echo "❌ Редирект на HTTP вместо HTTPS!"
        fi
    fi
else
    echo "⚠️  Не удалось проверить HTTP редирект"
fi

echo ""
echo "=========================================="
echo "Проверка завершена"
