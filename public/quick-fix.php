<?php
// Быстрое исправление прав доступа к логам
header('Content-Type: text/plain; charset=utf-8');

echo "🔧 Исправление прав доступа к логам Laravel\n";
echo "==========================================\n\n";

$project_root = dirname(__DIR__);
$storage_dir = $project_root . '/storage';
$logs_dir = $storage_dir . '/logs';
$cache_dir = $project_root . '/bootstrap/cache';
$log_file = $logs_dir . '/laravel.log';

echo "📁 Директория проекта: $project_root\n";
echo "📁 Storage директория: $storage_dir\n";
echo "📁 Logs директория: $logs_dir\n";
echo "📁 Cache директория: $cache_dir\n";
echo "📄 Файл логов: $log_file\n\n";

$fixes = [];

// Создать директории
if (!is_dir($logs_dir)) {
    if (mkdir($logs_dir, 0755, true)) {
        $fixes[] = "✅ Создана директория logs/";
    } else {
        $fixes[] = "❌ Не удалось создать директорию logs/";
    }
}

if (!is_dir($cache_dir)) {
    if (mkdir($cache_dir, 0755, true)) {
        $fixes[] = "✅ Создана директория bootstrap/cache/";
    } else {
        $fixes[] = "❌ Не удалось создать директорию bootstrap/cache/";
    }
}

// Создать файл логов
if (!file_exists($log_file)) {
    if (touch($log_file)) {
        $fixes[] = "✅ Создан файл laravel.log";
    } else {
        $fixes[] = "❌ Не удалось создать файл laravel.log";
    }
}

// Установить права доступа
if (is_dir($storage_dir)) {
    if (chmod($storage_dir, 0775)) {
        $fixes[] = "✅ Установлены права 775 для storage/";
    } else {
        $fixes[] = "❌ Не удалось изменить права для storage/";
    }
}

if (is_dir($logs_dir)) {
    if (chmod($logs_dir, 0775)) {
        $fixes[] = "✅ Установлены права 775 для logs/";
    } else {
        $fixes[] = "❌ Не удалось изменить права для logs/";
    }
}

if (is_dir($cache_dir)) {
    if (chmod($cache_dir, 0775)) {
        $fixes[] = "✅ Установлены права 775 для bootstrap/cache/";
    } else {
        $fixes[] = "❌ Не удалось изменить права для bootstrap/cache/";
    }
}

if (file_exists($log_file)) {
    if (chmod($log_file, 0664)) {
        $fixes[] = "✅ Установлены права 664 для laravel.log";
    } else {
        $fixes[] = "❌ Не удалось изменить права для laravel.log";
    }
}

echo "🛠️ Выполненные исправления:\n";
foreach ($fixes as $fix) {
    echo "$fix\n";
}

echo "\n📊 Текущее состояние:\n";
echo "Storage доступен: " . (is_writable($storage_dir) ? "✅" : "❌") . "\n";
echo "Logs доступны: " . (is_writable($logs_dir) ? "✅" : "❌") . "\n";
echo "Cache доступен: " . (is_writable($cache_dir) ? "✅" : "❌") . "\n";
echo "Log файл доступен: " . (is_writable($log_file) ? "✅" : "❌") . "\n";

echo "\n⚠️ Если автоматическое исправление не помогло, выполните команды:\n";
echo "sudo chmod -R 775 $storage_dir\n";
echo "sudo chmod -R 775 $cache_dir\n";
echo "sudo chown -R www-data:www-data $storage_dir\n";
echo "sudo chown -R www-data:www-data $cache_dir\n";
echo "sudo chmod 664 $log_file\n";
echo "sudo chown www-data:www-data $log_file\n";

echo "\n🔄 Перезапустите PHP-FPM:\n";
echo "sudo systemctl restart php8.4-fpm\n";

echo "\n✅ Готово! Проверьте работу сайта.\n";
?>
