# Инструкции по развертыванию Education Management System

## Создание репозитория на GitHub

1. Перейдите на [GitHub.com](https://github.com) и войдите в свой аккаунт
2. Нажмите кнопку "New repository" (зеленая кнопка)
3. Заполните форму:
    - Repository name: `education-management-system`
    - Description: `Education Management System with Laravel 10`
    - Выберите "Public" или "Private"
    - НЕ добавляйте README, .gitignore или лицензию (они уже есть)
4. Нажмите "Create repository"

## Загрузка кода на GitHub

После создания репозитория выполните следующие команды в терминале:

```bash
cd /Users/valentink2410/education-management-system
git remote set-url origin https://github.com/YOUR_USERNAME/education-management-system.git
git push -u origin master
```

Замените `YOUR_USERNAME` на ваш реальный username на GitHub.

## Установка и настройка проекта

### 1. Установка зависимостей

```bash
cd /Users/valentink2410/education-management-system
composer install
```

### 2. Настройка базы данных

Проект настроен для работы с SQLite по умолчанию. База данных уже создана в `database/database.sqlite`.

Если хотите использовать MySQL или PostgreSQL, отредактируйте файл `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=education_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Генерация ключа приложения

```bash
php artisan key:generate
```

### 4. Запуск миграций

```bash
php artisan migrate
```

### 5. Заполнение тестовыми данными

```bash
php artisan db:seed
```

### 6. Запуск сервера разработки

```bash
php artisan serve
```

Откройте браузер и перейдите по адресу: http://localhost:8000

## Тестовые аккаунты

После выполнения сидеров будут созданы следующие аккаунты:

-   **Администратор**: admin@example.com / password
-   **Преподаватель**: instructor@example.com / password
-   **Студент**: student@example.com / password

## Возможности системы

### 🔐 Аутентификация и авторизация

-   Регистрация и вход пользователей
-   Система ролей (Администратор, Преподаватель, Студент)
-   Разграничение прав доступа

### 🏫 Управление учебными заведениями

-   Создание и редактирование учебных заведений
-   Загрузка логотипов
-   Управление контактной информацией

### 📚 Управление образовательными программами

-   Создание программ обучения
-   Привязка к учебным заведениям
-   Указание требований для поступления
-   Управление стоимостью обучения

### 🎓 Управление курсами

-   Создание и редактирование курсов
-   Назначение преподавателей
-   Указание расписания и места проведения
-   Описание результатов обучения

### 👥 Управление пользователями

-   Создание пользователей
-   Назначение ролей
-   Управление профилями

### 🌐 Публичная часть

-   Просмотр учебных заведений
-   Просмотр программ и курсов
-   Информация о преподавателях

## Структура проекта

```
app/
├── Http/Controllers/
│   ├── Auth/           # Контроллеры аутентификации
│   ├── Admin/          # Административные контроллеры
│   └── PublicController.php
├── Models/             # Модели данных
└── Http/Middleware/    # Middleware для авторизации

database/
├── migrations/         # Миграции базы данных
└── seeders/          # Сидеры для тестовых данных

resources/views/
├── layouts/           # Основные шаблоны
├── auth/              # Формы входа/регистрации
├── admin/             # Административные страницы
└── public/            # Публичные страницы
```

## Технологии

-   **Backend**: Laravel 10, PHP 8.1+
-   **Frontend**: Bootstrap 5, Font Awesome
-   **База данных**: SQLite (по умолчанию), MySQL, PostgreSQL
-   **Аутентификация**: Laravel Sanctum

## Дополнительные настройки

### Настройка файлового хранилища

Для загрузки файлов (логотипы, аватары) создайте символическую ссылку:

```bash
php artisan storage:link
```

### Настройка очередей (опционально)

Если планируете использовать очереди, настройте Redis или другую систему очередей в `.env`:

```env
QUEUE_CONNECTION=redis
```

### Настройка почты (опционально)

Для отправки уведомлений настройте SMTP в `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

## Развертывание на продакшене

1. Настройте веб-сервер (Apache/Nginx)
2. Установите PHP 8.1+ и необходимые расширения
3. Настройте базу данных
4. Установите зависимости: `composer install --no-dev`
5. Настройте `.env` для продакшена
6. Запустите миграции: `php artisan migrate --force`
7. Очистите кэш: `php artisan config:cache && php artisan route:cache`

## Поддержка

Если у вас возникли вопросы или проблемы, создайте issue в репозитории GitHub.
