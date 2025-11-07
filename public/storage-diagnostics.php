<?php
declare(strict_types=1);

/**
 * Временный диагностический скрипт для проверки доступа к файлам storage.
 *
 * Запуск: https://<домен>/storage-diagnostics.php?file=avatars/имя_файла.jpg
 * После завершения диагностики удалите этот файл.
 */

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);

function line(string $label, string $value): void
{
    echo $label . ': ' . $value . "\n";
}

$publicPath = __DIR__;
$storageAliasPath = $publicPath . DIRECTORY_SEPARATOR . 'storage';
$storageRealPath = realpath($storageAliasPath) ?: '(недоступно)';
$storageAppPublic = realpath($publicPath . '/../storage/app/public') ?: '(недоступно)';

echo "=== Storage Diagnostics ===\n";
line('Дата/время сервера', date('Y-m-d H:i:s')); 
line('PHP version', PHP_VERSION);
line('Document root (__DIR__)', $publicPath);
line('storage существует', file_exists($storageAliasPath) ? 'да' : 'нет');
line('storage является симлинком', is_link($storageAliasPath) ? 'да' : 'нет');
line('readlink(storage)', is_link($storageAliasPath) ? (readlink($storageAliasPath) ?: '(пусто)') : '(не симлинк)');
line('realpath(storage)', $storageRealPath);
line('realpath(storage/app/public)', $storageAppPublic);

if ($storageRealPath !== '(недоступно)' && is_dir($storageRealPath)) {
    $entries = scandir($storageRealPath) ?: [];
    $visibleEntries = array_values(array_filter($entries, static fn($entry) => !in_array($entry, ['.', '..'], true)));
    $preview = array_slice($visibleEntries, 0, 5);
    line('Первые элементы в storage', $preview ? implode(', ', $preview) : '(пусто)');
} else {
    line('Первые элементы в storage', '(недоступно)');
}

$relativeFile = isset($_GET['file']) ? trim((string) $_GET['file']) : '';
if ($relativeFile !== '') {
    $relativeFile = ltrim($relativeFile, '/');
}

if ($relativeFile === '') {
    echo "\nПередайте параметр file, например ?file=avatars/пример.jpg, чтобы проверить конкретный файл.\n";
    exit;
}

line('\nЗапрошенный файл', $relativeFile);

$publicFile = $storageAliasPath . DIRECTORY_SEPARATOR . $relativeFile;
$diskFile = $publicPath . '/../storage/app/public/' . $relativeFile;

line('Путь через /public/storage', $publicFile);
line('Путь в storage/app/public', $diskFile);

$checks = [
    'file_exists' => file_exists($publicFile) ? 'да' : 'нет',
    'is_readable' => is_readable($publicFile) ? 'да' : 'нет',
];

foreach ($checks as $label => $result) {
    line($label, $result);
}

if (file_exists($publicFile)) {
    $size = filesize($publicFile);
    line('Размер файла (байт)', $size !== false ? (string) $size : '(не определён)');

    $info = pathinfo($publicFile);
    line('Directory name', $info['dirname'] ?? '(нет)');
    line('Basename', $info['basename'] ?? '(нет)');

    $mimeType = function_exists('mime_content_type') ? mime_content_type($publicFile) : '(mime_content_type недоступен)';
    line('MIME type', $mimeType ?: '(не определён)');

    $handle = @fopen($publicFile, 'rb');
    if ($handle === false) {
        line('Чтение файла', 'ошибка: не удалось открыть файл');
    } else {
        $sample = fread($handle, 32);
        fclose($handle);
        $sampleHex = $sample === false ? '(ошибка чтения)' : bin2hex($sample);
        line('Первые 32 байта (hex)', $sampleHex);
    }
} else {
    line('Размер файла (байт)', '(файл не найден)');
}

if (file_exists($diskFile) && $diskFile !== $publicFile) {
    line('\nФайл в storage/app/public', 'найден');
    line('Совпадает ли содержимое', md5_file($publicFile) === md5_file($diskFile) ? 'да' : 'нет');
} elseif (!file_exists($diskFile)) {
    line('\nФайл в storage/app/public', 'не найден');
}

echo "\nДиагностика завершена.\n";


