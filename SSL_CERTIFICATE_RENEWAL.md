# Обновление SSL сертификата для theologybooks.org

## Проблема

SSL сертификат для сайта `theologybooks.org` истекает или уже истек (24 января 2026), что может вызывать проблемы с доступом к сайту.

## Решение

### Вариант 1: Автоматическое обновление через скрипт (рекомендуется)

1. **Подключитесь к серверу по SSH:**
   ```bash
   ssh user@your-server
   ```

2. **Скопируйте скрипт на сервер:**
   ```bash
   # Если скрипт уже в проекте
   cd /path/to/education-management-system
   chmod +x scripts/renew-ssl-certificate.sh
   
   # Или скопируйте скрипт на сервер
   scp scripts/renew-ssl-certificate.sh user@your-server:/tmp/
   ```

3. **Запустите скрипт с правами root:**
   ```bash
   sudo /path/to/renew-ssl-certificate.sh
   ```

   Скрипт автоматически:
   - Проверит текущий статус сертификата
   - Обновит сертификат через certbot
   - Проверит конфигурацию Nginx
   - Перезагрузит Nginx
   - Проверит новый сертификат

### Вариант 2: Ручное обновление через certbot

1. **Подключитесь к серверу:**
   ```bash
   ssh user@your-server
   ```

2. **Проверьте текущий статус сертификатов:**
   ```bash
   sudo certbot certificates
   ```

3. **Обновите сертификат:**
   ```bash
   sudo certbot renew --cert-name theologybooks.org
   ```

4. **Или получите новый сертификат (если обновление не работает):**
   ```bash
   sudo certbot --nginx -d theologybooks.org --non-interactive --agree-tos --email admin@theologybooks.org --redirect
   ```

5. **Проверьте конфигурацию Nginx:**
   ```bash
   sudo nginx -t
   ```

6. **Перезагрузите Nginx:**
   ```bash
   sudo systemctl reload nginx
   ```

### Вариант 3: Обновление через ISPmanager (если используется)

Если сайт управляется через ISPmanager:

1. Войдите в панель ISPmanager
2. Перейдите в раздел "Домены" → выберите `theologybooks.org`
3. Перейдите в раздел "SSL сертификаты"
4. Нажмите "Обновить сертификат" или "Получить Let's Encrypt"

## Настройка автоматического обновления

Let's Encrypt сертификаты действительны 90 дней. Рекомендуется настроить автоматическое обновление:

1. **Проверьте, что cron задача настроена:**
   ```bash
   sudo crontab -l | grep certbot
   ```

2. **Если задача отсутствует, добавьте её:**
   ```bash
   sudo crontab -e
   ```

   Добавьте строку:
   ```cron
   0 0,12 * * * /usr/bin/certbot renew --quiet --post-hook "systemctl reload nginx"
   ```

   Это будет проверять и обновлять сертификаты дважды в день.

3. **Проверьте тестовое обновление:**
   ```bash
   sudo certbot renew --dry-run
   ```

## Проверка после обновления

1. **Проверьте сертификат через браузер:**
   - Откройте https://theologybooks.org
   - Нажмите на значок замка в адресной строке
   - Проверьте дату истечения

2. **Проверьте через командную строку:**
   ```bash
   echo | openssl s_client -connect theologybooks.org:443 -servername theologybooks.org 2>&1 | openssl x509 -noout -dates -subject -issuer
   ```

3. **Проверьте через онлайн-сервисы:**
   - https://www.ssllabs.com/ssltest/analyze.html?d=theologybooks.org
   - https://www.sslshopper.com/ssl-checker.html#hostname=theologybooks.org

## Устранение проблем

### Проблема: Certbot не может получить сертификат

**Причины:**
- Домен не указывает на сервер
- Порты 80 и 443 закрыты
- Nginx не настроен правильно

**Решение:**
1. Проверьте DNS записи:
   ```bash
   dig theologybooks.org
   nslookup theologybooks.org
   ```

2. Проверьте доступность портов:
   ```bash
   sudo netstat -tlnp | grep -E ':80|:443'
   ```

3. Проверьте конфигурацию Nginx:
   ```bash
   sudo nginx -t
   sudo cat /etc/nginx/sites-available/theologybooks.org
   ```

### Проблема: Сертификат обновлен, но сайт не работает

**Решение:**
1. Проверьте, что Nginx перезагружен:
   ```bash
   sudo systemctl status nginx
   sudo systemctl reload nginx
   ```

2. Проверьте логи:
   ```bash
   sudo tail -f /var/log/nginx/error.log
   sudo tail -f /var/log/letsencrypt/letsencrypt.log
   ```

3. Проверьте, что сертификат правильно указан в конфигурации:
   ```bash
   sudo grep -E "ssl_certificate|ssl_certificate_key" /etc/nginx/sites-available/theologybooks.org
   ```

   Должны быть строки:
   ```nginx
   ssl_certificate /etc/letsencrypt/live/theologybooks.org/fullchain.pem;
   ssl_certificate_key /etc/letsencrypt/live/theologybooks.org/privkey.pem;
   ```

## Дополнительная информация

- **Let's Encrypt документация:** https://letsencrypt.org/docs/
- **Certbot документация:** https://certbot.eff.org/
- **Nginx SSL настройка:** https://nginx.org/en/docs/http/configuring_https_servers.html

## Контакты

Если возникли проблемы, проверьте:
1. Логи certbot: `/var/log/letsencrypt/letsencrypt.log`
2. Логи Nginx: `/var/log/nginx/error.log`
3. Статус сервисов: `systemctl status nginx certbot.timer`
