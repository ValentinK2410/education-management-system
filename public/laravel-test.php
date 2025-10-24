<?php
// Простая проверка Laravel без полной загрузки
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Laravel Test</title></head><body>";
echo "<h1>🔧 Тест Laravel</h1>";

// Проверка базовых файлов
$files = [
    '../artisan',
    '../composer.json',
    '../bootstrap/app.php',
    '../app/Http/Kernel.php',
    '../.env'
];

echo "<h2>Проверка файлов:</h2>";
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? "✅" : "❌") . " " . $file . "<br>";
}

// Проверка прав доступа
echo "<h2>Проверка прав доступа:</h2>";
$dirs = [
    '../storage/',
    '../bootstrap/cache/',
    '../storage/logs/'
];

foreach ($dirs as $dir) {
    $writable = is_writable(__DIR__ . '/' . $dir);
    echo ($writable ? "✅" : "❌") . " " . $dir . " - " . ($writable ? "доступен" : "недоступен") . "<br>";
}

// Попытка загрузить Laravel
echo "<h2>Попытка загрузки Laravel:</h2>";
try {
    if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
        echo "✅ Bootstrap файл найден<br>";
        
        // Проверка синтаксиса
        $content = file_get_contents(__DIR__ . '/../bootstrap/app.php');
        if (strpos($content, 'Application::configure') !== false) {
            echo "✅ Bootstrap файл выглядит корректно<br>";
        } else {
            echo "❌ Bootstrap файл поврежден<br>";
        }
    } else {
        echo "❌ Bootstrap файл не найден<br>";
    }
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "<br>";
}

echo "<h2>Информация о сервере:</h2>";
echo "PHP версия: " . PHP_VERSION . "<br>";
echo "Сервер: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Неизвестно') . "<br>";
echo "Время: " . date('Y-m-d H:i:s') . "<br>";

echo "</body></html>";
?>
