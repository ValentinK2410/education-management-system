-- SQL скрипт для исправления проблемы с миграцией course_activities
-- Выполните этот скрипт на сервере, если таблица уже создана без индекса

-- Вариант 1: Если таблица пустая - удалите её
DROP TABLE IF EXISTS course_activities;

-- Вариант 2: Если таблица содержит данные - добавьте индекс вручную
-- Сначала проверьте, существует ли индекс:
-- SHOW INDEXES FROM course_activities WHERE Key_name = 'course_activities_unique';

-- Если индекса нет, добавьте его:
-- ALTER TABLE course_activities 
-- ADD UNIQUE INDEX course_activities_unique (course_id, moodle_activity_id, activity_type);

-- После выполнения скрипта запустите:
-- php artisan migrate

