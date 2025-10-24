<?php
// Простой тестовый файл для проверки сохранения цены программы
// Файл: public/test-price-simple.php

// Подключаем Laravel
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Получаем параметры из URL
    $isPaid = isset($_GET['is_paid']) && $_GET['is_paid'] === '1';
    $price = $_GET['price'] ?? null;
    $currency = $_GET['currency'] ?? 'RUB';
    
    // Находим программу с ID 3
    $program = \App\Models\Program::find(3);
    if (!$program) {
        echo json_encode(['error' => 'Программа не найдена']);
        exit;
    }
    
    // Сохраняем текущие значения
    $before = [
        'is_paid' => $program->is_paid,
        'price' => $program->price,
        'currency' => $program->currency,
    ];
    
    // Подготавливаем данные для сохранения
    $data = [
        'is_paid' => $isPaid,
        'price' => $isPaid ? $price : null,
        'currency' => $currency,
    ];
    
    // Сохраняем данные
    $program->update($data);
    
    // Получаем обновленные данные
    $program->refresh();
    
    $after = [
        'is_paid' => $program->is_paid,
        'price' => $program->price,
        'currency' => $program->currency,
    ];
    
    // Возвращаем результат
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Цена программы сохранена (PHP тест)',
        'before' => $before,
        'after' => $after,
        'input_data' => $data,
        'url_params' => $_GET
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
