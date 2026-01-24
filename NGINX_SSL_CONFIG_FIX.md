# Исправление конфигурации Nginx для theologybooks.org

## Проблемы в текущей конфигурации

1. **Неправильный редирект**: `return 301 http://theologybooks.org$request_uri;` - редиректит на HTTP вместо HTTPS
2. **Отсутствует HTTPS блок**: Нет конфигурации для порта 443
3. **Нет SSL сертификатов**: Не указаны пути к SSL сертификатам
4. **Неполная цепочка сертификатов**: Может использоваться `cert.pem` вместо `fullchain.pem`

## Решение

### Вариант 1: Использование скрипта (рекомендуется)

```bash
cd /path/to/education-management-system
sudo scripts/fix-nginx-ssl-config.sh
```

Скрипт автоматически:
- Создаст резервную копию текущей конфигурации
- Определит текущие настройки (root, PHP socket)
- Создаст правильную конфигурацию с SSL
- Проверит синтаксис
- Перезагрузит Nginx

### Вариант 2: Ручное исправление

1. **Создайте резервную копию:**
   ```bash
   sudo cp /etc/nginx/sites-available/theologybooks.org /etc/nginx/sites-available/theologybooks.org.backup
   ```

2. **Отредактируйте конфигурацию:**
   ```bash
   sudo nano /etc/nginx/sites-available/theologybooks.org
   ```

3. **Используйте правильную конфигурацию** (см. файл `nginx-theologybooks-org.conf`)

4. **Проверьте синтаксис:**
   ```bash
   sudo nginx -t
   ```

5. **Перезагрузите Nginx:**
   ```bash
   sudo systemctl reload nginx
   ```

## Ключевые изменения

### 1. Правильный редирект HTTP → HTTPS

**Было:**
```nginx
return 301 http://theologybooks.org$request_uri;
```

**Должно быть:**
```nginx
return 301 https://theologybooks.org$request_uri;
```

### 2. Добавлен HTTPS блок

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name theologybooks.org www.theologybooks.org;
    
    # SSL сертификаты
    ssl_certificate /etc/letsencrypt/live/theologybooks.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/theologybooks.org/privkey.pem;
    
    # ... остальная конфигурация
}
```

### 3. Использование fullchain.pem

**Важно:** Используйте `fullchain.pem`, а не `cert.pem`!

- `fullchain.pem` - содержит полную цепочку сертификатов (основной + промежуточные)
- `cert.pem` - содержит только основной сертификат

Использование `fullchain.pem` решает проблему "unable to verify the first certificate".

## Проверка после исправления

1. **Проверьте SSL сертификат:**
   ```bash
   echo | openssl s_client -connect theologybooks.org:443 -servername theologybooks.org 2>&1 | openssl x509 -noout -dates
   ```

2. **Проверьте редирект:**
   ```bash
   curl -I http://theologybooks.org
   ```
   Должен быть редирект 301 на HTTPS

3. **Проверьте HTTPS:**
   ```bash
   curl -I https://theologybooks.org
   ```
   Должен вернуть HTTP 200 или 302

4. **Проверьте цепочку сертификатов:**
   ```bash
   echo | openssl s_client -connect theologybooks.org:443 -servername theologybooks.org 2>&1 | grep -A 5 "Certificate chain"
   ```

## Устранение проблем

### Проблема: "SSL certificate problem: unable to get local issuer certificate"

**Причина:** Используется `cert.pem` вместо `fullchain.pem`

**Решение:** Измените в конфигурации:
```nginx
ssl_certificate /etc/letsencrypt/live/theologybooks.org/fullchain.pem;
```

### Проблема: Редирект на HTTP вместо HTTPS

**Причина:** Неправильный редирект в блоке `listen 80`

**Решение:** Измените:
```nginx
return 301 https://theologybooks.org$request_uri;
```

### Проблема: Nginx не перезагружается

**Решение:**
1. Проверьте синтаксис: `sudo nginx -t`
2. Проверьте логи: `sudo tail -f /var/log/nginx/error.log`
3. Восстановите из резервной копии при необходимости

## Дополнительные настройки безопасности

Конфигурация включает:

- **HSTS** (HTTP Strict Transport Security)
- **Безопасные заголовки** (X-Frame-Options, X-XSS-Protection и др.)
- **Современные SSL протоколы** (TLSv1.2, TLSv1.3)
- **Оптимизация кеширования** для статических файлов

## Файлы

- `nginx-theologybooks-org.conf` - Пример правильной конфигурации
- `scripts/fix-nginx-ssl-config.sh` - Скрипт автоматического исправления
