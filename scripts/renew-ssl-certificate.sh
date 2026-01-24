#!/bin/bash

# Скрипт для обновления SSL сертификата для theologybooks.org
# Использует Let's Encrypt через certbot

DOMAIN="theologybooks.org"
NGINX_CONFIG="/etc/nginx/sites-available/${DOMAIN}"
CERTBOT_LOG="/var/log/letsencrypt/letsencrypt.log"

echo "🔒 Обновление SSL сертификата для ${DOMAIN}"
echo "=========================================="
echo ""

# Проверяем, запущен ли скрипт от root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Ошибка: Скрипт должен быть запущен с правами root (используйте sudo)"
    exit 1
fi

# Проверяем наличие certbot
if ! command -v certbot &> /dev/null; then
    echo "❌ Certbot не установлен. Устанавливаем..."
    apt update
    apt install -y certbot python3-certbot-nginx
fi

# Проверяем текущий статус сертификата
echo "📋 Проверка текущего статуса сертификата..."
echo ""

CERT_INFO=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | openssl x509 -noout -dates -subject -issuer 2>/dev/null)

if [ -z "$CERT_INFO" ]; then
    echo "⚠️  Не удалось получить информацию о текущем сертификате"
else
    echo "Текущий сертификат:"
    echo "$CERT_INFO"
    echo ""
fi

# Проверяем дату истечения через certbot (более надежно)
CERTBOT_EXPIRY=$(certbot certificates 2>/dev/null | grep -A 5 "theologybooks.org" | grep "Expiry Date" | awk '{print $3, $4, $5}')

if [ -n "$CERTBOT_EXPIRY" ]; then
    echo "📅 Дата истечения (из certbot): $CERTBOT_EXPIRY"
    # Проверяем через certbot, нуждается ли сертификат в обновлении
    CERTBOT_STATUS=$(certbot certificates 2>/dev/null | grep -A 5 "theologybooks.org" | grep "Certificate Name" -A 3 | grep -E "Expiry|VALID")
    echo "$CERTBOT_STATUS"
    echo ""
fi

# Проверяем дату истечения через openssl (дополнительная проверка)
EXPIRY_DATE=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
EXPIRY_EPOCH=$(date -d "$EXPIRY_DATE" +%s 2>/dev/null || date -j -f "%b %d %H:%M:%S %Y %Z" "$EXPIRY_DATE" +%s 2>/dev/null)
CURRENT_EPOCH=$(date +%s)

if [ -n "$EXPIRY_EPOCH" ] && [ -n "$CURRENT_EPOCH" ]; then
    DAYS_UNTIL_EXPIRY=$(( ($EXPIRY_EPOCH - $CURRENT_EPOCH) / 86400 ))
    
    if [ $DAYS_UNTIL_EXPIRY -lt 0 ]; then
        echo "❌ Сертификат ИСТЕК! Необходимо срочное обновление."
    elif [ $DAYS_UNTIL_EXPIRY -lt 30 ]; then
        echo "⚠️  Сертификат истекает через $DAYS_UNTIL_EXPIRY дней. Рекомендуется обновление."
    elif [ $DAYS_UNTIL_EXPIRY -lt 60 ]; then
        echo "✅ Сертификат действителен еще $DAYS_UNTIL_EXPIRY дней. Можно обновить заранее."
        read -p "Продолжить обновление? (y/n): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "Обновление отменено. Сертификат будет автоматически обновлен при приближении к дате истечения."
            exit 0
        fi
    else
        echo "✅ Сертификат действителен еще $DAYS_UNTIL_EXPIRY дней."
        echo "ℹ️  Certbot автоматически обновит сертификат за 30 дней до истечения."
        read -p "Принудительно обновить сейчас? (y/n): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "Обновление отменено. Сертификат будет автоматически обновлен при необходимости."
            exit 0
        fi
    fi
fi

echo ""
echo "🔄 Начинаем обновление сертификата..."
echo ""

# Создаем резервную копию конфигурации nginx
if [ -f "$NGINX_CONFIG" ]; then
    BACKUP_CONFIG="${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
    cp "$NGINX_CONFIG" "$BACKUP_CONFIG"
    echo "✅ Резервная копия конфигурации создана: $BACKUP_CONFIG"
fi

# Обновляем сертификат
echo "Запускаем certbot для обновления сертификата..."
if certbot renew --cert-name ${DOMAIN} --nginx --non-interactive --agree-tos; then
    echo "✅ Сертификат успешно обновлен!"
else
    echo "⚠️  Certbot renew не сработал, пробуем получить новый сертификат..."
    
    # Если обновление не сработало, пробуем получить новый сертификат
    if certbot --nginx -d ${DOMAIN} --non-interactive --agree-tos --email admin@${DOMAIN} --redirect; then
        echo "✅ Новый сертификат успешно получен!"
    else
        echo "❌ Ошибка при получении сертификата. Проверьте логи: $CERTBOT_LOG"
        exit 1
    fi
fi

# Проверяем синтаксис nginx
echo ""
echo "🔍 Проверка конфигурации Nginx..."
if nginx -t; then
    echo "✅ Конфигурация Nginx корректна"
else
    echo "❌ Ошибка в конфигурации Nginx!"
    if [ -f "$BACKUP_CONFIG" ]; then
        echo "Восстанавливаем резервную копию..."
        cp "$BACKUP_CONFIG" "$NGINX_CONFIG"
        nginx -t
    fi
    exit 1
fi

# Перезагружаем nginx
echo ""
echo "🔄 Перезагрузка Nginx..."
if systemctl reload nginx; then
    echo "✅ Nginx успешно перезагружен"
else
    echo "❌ Ошибка при перезагрузке Nginx"
    exit 1
fi

# Проверяем новый сертификат
echo ""
echo "🔍 Проверка нового сертификата..."
sleep 2

NEW_CERT_INFO=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | openssl x509 -noout -dates -subject -issuer 2>/dev/null)

if [ -n "$NEW_CERT_INFO" ]; then
    echo "Новый сертификат:"
    echo "$NEW_CERT_INFO"
    echo ""
    
    NEW_EXPIRY_DATE=$(echo | openssl s_client -connect ${DOMAIN}:443 -servername ${DOMAIN} 2>&1 | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
    NEW_EXPIRY_EPOCH=$(date -d "$NEW_EXPIRY_DATE" +%s 2>/dev/null || date -j -f "%b %d %H:%M:%S %Y %Z" "$NEW_EXPIRY_DATE" +%s 2>/dev/null)
    NEW_DAYS_UNTIL_EXPIRY=$(( ($NEW_EXPIRY_EPOCH - $CURRENT_EPOCH) / 86400 ))
    
    if [ -n "$NEW_DAYS_UNTIL_EXPIRY" ] && [ $NEW_DAYS_UNTIL_EXPIRY -gt 0 ]; then
        echo "✅ Сертификат действителен до: $NEW_EXPIRY_DATE ($NEW_DAYS_UNTIL_EXPIRY дней)"
    fi
else
    echo "⚠️  Не удалось проверить новый сертификат"
fi

# Проверяем автоматическое обновление
echo ""
echo "🔍 Проверка автоматического обновления..."
if certbot renew --dry-run; then
    echo "✅ Автоматическое обновление настроено корректно"
else
    echo "⚠️  Проблема с автоматическим обновлением. Проверьте настройки cron."
fi

echo ""
echo "=========================================="
echo "✅ Обновление SSL сертификата завершено!"
echo ""
echo "Полезные команды:"
echo "  - Проверить статус: certbot certificates"
echo "  - Тест обновления: certbot renew --dry-run"
echo "  - Логи: tail -f $CERTBOT_LOG"
echo ""
