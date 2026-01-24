#!/bin/bash

# Скрипт для диагностики цепочки SSL сертификатов

DOMAIN="theologybooks.org"
CERT_PATH="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"

echo "🔍 Диагностика SSL цепочки сертификатов для ${DOMAIN}"
echo "====================================================="
echo ""

# 1. Проверка файла fullchain.pem
echo "1. Проверка файла fullchain.pem:"
echo "--------------------------------"
if [ -f "$CERT_PATH" ]; then
    echo "✅ Файл существует: $CERT_PATH"
    ls -lh "$CERT_PATH"
    
    # Подсчитываем количество сертификатов в цепочке
    CERT_COUNT=$(openssl crl2pkcs7 -nocrl -certfile "$CERT_PATH" 2>/dev/null | openssl pkcs7 -print_certs -text -noout 2>/dev/null | grep -c "Subject:" || echo "0")
    echo "Количество сертификатов в цепочке: $CERT_COUNT"
    
    if [ "$CERT_COUNT" -ge 2 ]; then
        echo "✅ Цепочка содержит промежуточные сертификаты (хорошо)"
    else
        echo "⚠️  Цепочка может быть неполной"
    fi
else
    echo "❌ Файл не найден: $CERT_PATH"
    exit 1
fi

echo ""
echo "2. Проверка цепочки через openssl s_client:"
echo "-------------------------------------------"
CHAIN_OUTPUT=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1)
CHAIN_INFO=$(echo "$CHAIN_OUTPUT" | grep -A 10 "Certificate chain")

if [ -n "$CHAIN_INFO" ]; then
    echo "$CHAIN_INFO"
else
    echo "⚠️  Не удалось получить информацию о цепочке"
fi

VERIFY_CODE=$(echo "$CHAIN_OUTPUT" | grep "Verify return code" | awk '{print $4}')
echo ""
echo "Код проверки: $VERIFY_CODE"
if [ "$VERIFY_CODE" = "0" ]; then
    echo "✅ Сертификат проверен успешно"
elif [ "$VERIFY_CODE" = "21" ]; then
    echo "❌ Проблема: unable to verify the first certificate"
    echo "   Это может означать, что промежуточные сертификаты не отправляются"
elif [ -n "$VERIFY_CODE" ]; then
    echo "⚠️  Код ошибки: $VERIFY_CODE"
fi

echo ""
echo "3. Проверка конфигурации Nginx:"
echo "--------------------------------"
NGINX_CONFIG="/etc/nginx/sites-available/default"
if grep -q "ssl_certificate.*fullchain.pem" "$NGINX_CONFIG"; then
    echo "✅ Используется fullchain.pem в конфигурации"
    grep "ssl_certificate.*fullchain.pem" "$NGINX_CONFIG"
else
    echo "❌ fullchain.pem не найден в конфигурации!"
    echo "Найдено:"
    grep "ssl_certificate" "$NGINX_CONFIG" || echo "SSL сертификаты не найдены"
fi

echo ""
echo "4. Проверка активной конфигурации Nginx:"
echo "----------------------------------------"
ACTIVE_CONFIG=$(sudo nginx -T 2>&1 | grep -A 3 "ssl_certificate.*${DOMAIN}" | head -5)
if [ -n "$ACTIVE_CONFIG" ]; then
    echo "$ACTIVE_CONFIG"
else
    echo "⚠️  Не удалось получить активную конфигурацию"
fi

echo ""
echo "5. Тест подключения:"
echo "--------------------"
HTTP_TEST=$(curl -k -I https://${DOMAIN} 2>&1 | head -3)
if echo "$HTTP_TEST" | grep -q "HTTP"; then
    echo "✅ Сайт отвечает через HTTPS"
    echo "$HTTP_TEST" | grep "HTTP"
else
    echo "❌ Проблема с подключением"
fi

echo ""
echo "6. Рекомендации:"
echo "----------------"
if [ "$VERIFY_CODE" != "0" ]; then
    echo "Если код проверки не 0, попробуйте:"
    echo "1. Перезагрузить Nginx: sudo systemctl reload nginx"
    echo "2. Проверить, что используется fullchain.pem (уже проверено ✅)"
    echo "3. Убедиться, что сертификат не истек: sudo certbot certificates"
    echo ""
    echo "Примечание: curl может показывать ошибку из-за отсутствия CA сертификатов"
    echo "на клиентской машине, но сайт может работать нормально в браузерах."
fi

echo ""
echo "====================================================="
echo "Диагностика завершена"
