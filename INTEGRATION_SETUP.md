# Инструкция по настройке интеграции WordPress → Moodle → Laravel

## Последовательность создания пользователя

1. **WordPress** (site.dekan.pro) - пользователь регистрируется
2. **Moodle** (class.dekan.pro) - пользователь подтверждает email и устанавливает пароль, создается в Moodle
3. **Laravel** (m.dekan.pro) - после успешного создания в Moodle, WordPress вызывает API Laravel для создания пользователя

## Настройка WordPress плагина

В настройках плагина "Курсы Про" добавьте:

1. **Laravel API URL**: `https://m.dekan.pro`
2. **Laravel API Token**: токен для доступа к API (см. ниже)

Эти настройки можно добавить через опции WordPress:
```php
update_option('laravel_api_url', 'https://m.dekan.pro');
update_option('laravel_api_token', 'your-secret-token-here');
```

## Настройка Laravel приложения

### 1. Добавьте в `.env`:

```env
WORDPRESS_API_TOKEN=your-secret-token-here
LARAVEL_API_URL=https://m.dekan.pro
```

**ВАЖНО**: Токен `WORDPRESS_API_TOKEN` должен совпадать с токеном в WordPress настройках!

### 2. Выполните миграцию:

```bash
php artisan migrate
```

Это добавит поле `moodle_user_id` в таблицу `users`.

### 3. Сгенерируйте API токен:

Вы можете использовать любой безопасный токен, например:

```bash
php artisan tinker
>>> Str::random(60)
```

Или используйте готовый токен и добавьте его в оба `.env` файла (WordPress и Laravel).

## API Endpoint

Laravel предоставляет endpoint:
- **URL**: `POST https://m.dekan.pro/api/users/sync-from-wordpress`
- **Headers**: 
  - `Content-Type: application/json`
  - `X-API-Token: your-secret-token-here`
- **Body**:
```json
{
  "name": "Имя Фамилия",
  "email": "user@example.com",
  "password": "password123",
  "moodle_user_id": 123,
  "phone": "+1234567890"
}
```

## Проверка работы

1. Зарегистрируйте пользователя на WordPress (site.dekan.pro)
2. Пользователь подтверждает email и устанавливает пароль
3. Пользователь создается в Moodle (class.dekan.pro)
4. WordPress автоматически вызывает API Laravel
5. Пользователь создается в Laravel (m.dekan.pro)

Все операции логируются в:
- WordPress: `wp-content/course-registration-debug.log`
- Laravel: `storage/logs/laravel.log`

