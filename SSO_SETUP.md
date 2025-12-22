# Настройка Single Sign-On (SSO) в Laravel

## Что это такое?

SSO позволяет пользователям автоматически входить в Laravel после входа в WordPress без необходимости вводить пароль повторно.

## Настройка:

### 1. Добавьте в `.env`:

```env
WORDPRESS_URL=https://site.dekan.pro
WORDPRESS_SSO_API_KEY=ваш-sso-api-key-из-wordpress
```

**ВАЖНО**: 
- `WORDPRESS_URL` - URL вашего WordPress сайта
- `WORDPRESS_SSO_API_KEY` - должен совпадать с SSO API Key в WordPress настройках

### 2. Получите SSO API Key из WordPress:

1. Войдите в админку WordPress: `https://site.dekan.pro/wp-admin`
2. Перейдите в **Настройки → Moodle Sync**
3. Найдите раздел **"Настройки Single Sign-On (SSO)"**
4. Скопируйте значение поля **"SSO API Key"**

### 3. Проверьте настройки:

После добавления в `.env`, выполните:

```bash
php artisan config:clear
```

## Использование:

После входа в WordPress, пользователь может перейти в Laravel по ссылке:

```
https://m.dekan.pro/sso/login?token=SSO_TOKEN
```

Или использовать JavaScript функцию:

```javascript
goToLaravel();
```

## Как это работает:

1. Пользователь входит в WordPress
2. WordPress генерирует SSO токен (действителен 1 час)
3. Пользователь переходит по ссылке `/sso/login?token=...`
4. Laravel проверяет токен через WordPress API
5. Если токен валиден, пользователь автоматически входит в Laravel

## Безопасность:

- SSO токены действительны только 1 час
- Требуется SSO API ключ для проверки токенов
- Токены привязаны к конкретному пользователю
- Проверка происходит через защищенный API endpoint

## Troubleshooting:

### Проблема: "SSO не настроен"
- Проверьте, что `WORDPRESS_URL` и `WORDPRESS_SSO_API_KEY` указаны в `.env`
- Выполните `php artisan config:clear`

### Проблема: "Unauthorized"
- Проверьте, что `WORDPRESS_SSO_API_KEY` совпадает с ключом в WordPress
- Проверьте логи Laravel: `storage/logs/laravel.log`

### Проблема: "Пользователь не найден"
- Убедитесь, что пользователь был создан в Laravel через синхронизацию
- Проверьте, что email совпадает в WordPress и Laravel

