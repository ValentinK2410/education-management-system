# Как получить информацию о том, что студент сдал тест и получил оценку в Moodle

## Важно: `mod_quiz_get_quizzes_by_courses` не возвращает информацию о студентах

Функция `mod_quiz_get_quizzes_by_courses` возвращает **только информацию о самих тестах** (настройки, параметры, структуру), но **НЕ возвращает** информацию о:
- Попытках студентов
- Оценках студентов
- Статусе сдачи теста студентом

## Как получить информацию о попытках и оценках студента

Для получения информации о том, что студент сдал тест и получил оценку, нужно использовать **другие функции Moodle API**:

### 1. Получение попыток студента: `mod_quiz_get_user_attempts`

**Функция:** `mod_quiz_get_user_attempts`

**Параметры:**
- `quizid` - ID теста в Moodle (НЕ локальный ID!)
- `userid` - Moodle User ID студента (НЕ локальный ID!)
- `status` - Статус попыток: 'all', 'finished', 'unfinished', 'inprogress'

**Что возвращает:**
```json
{
  "attempts": [
    {
      "id": 123,
      "quiz": 89,
      "userid": 49,
      "attempt": 1,
      "uniqueid": 456,
      "layout": "1,2,3",
      "currentpage": 0,
      "preview": 0,
      "state": "finished",
      "timestart": 1705672800,
      "timefinish": 1705673100,
      "timemodified": 1705673100,
      "timecheckstate": null,
      "sumgrades": 8.5,
      "gradednotificationsenttime": null
    }
  ]
}
```

**Ключевые поля:**
- `state: "finished"` - тест завершен (сдан)
- `timefinish` - время завершения теста (timestamp)
- `sumgrades` - сумма баллов за тест
- `timestart` - время начала попытки

**Пример использования в коде:**
```php
$result = $moodleApi->call('mod_quiz_get_user_attempts', [
    'quizid' => 93,  // Moodle Quiz ID
    'userid' => 49,  // Moodle User ID студента
    'status' => 'all'
]);

// Проверка, сдан ли тест
if (isset($result['attempts']) && !empty($result['attempts'])) {
    foreach ($result['attempts'] as $attempt) {
        if ($attempt['state'] === 'finished') {
            // Тест сдан!
            $grade = $attempt['sumgrades'] ?? null;
            $finishedAt = $attempt['timefinish'] ?? null;
        }
    }
}
```

### 2. Получение лучшей оценки студента: `mod_quiz_get_user_best_grade`

**Функция:** `mod_quiz_get_user_best_grade`

**Параметры:**
- `quizid` - ID теста в Moodle
- `userid` - Moodle User ID студента

**Что возвращает:**
```json
{
  "grade": 8.5,
  "hasgrade": true,
  "attempts": [
    {
      "attempt": 1,
      "grade": 8.5,
      "timestart": 1705672800,
      "timefinish": 1705673100
    }
  ]
}
```

**Ключевые поля:**
- `grade` - лучшая оценка студента за тест
- `hasgrade` - есть ли оценка
- `attempts` - массив попыток с оценками

**Пример использования в коде:**
```php
$result = $moodleApi->call('mod_quiz_get_user_best_grade', [
    'quizid' => 93,  // Moodle Quiz ID
    'userid' => 49   // Moodle User ID студента
]);

if (isset($result['hasgrade']) && $result['hasgrade']) {
    $grade = $result['grade'];
    // Студент получил оценку!
}
```

## Как это реализовано в системе

В системе уже есть методы, которые используют эти функции:

### 1. `getStudentQuizAttempts()`

**Расположение:** `app/Services/MoodleApiService.php:792`

**Что делает:**
- Получает все попытки студента по всем тестам курса
- Использует `mod_quiz_get_user_attempts` для каждого теста
- Возвращает массив попыток, сгруппированных по ID теста

**Пример использования:**
```php
$attempts = $moodleApi->getStudentQuizAttempts(
    $courseId,        // Moodle Course ID
    $studentMoodleId, // Moodle User ID студента
    $quizzes          // Массив тестов (опционально)
);

// $attempts[93] - массив попыток студента по тесту с ID 93
foreach ($attempts[93] as $attempt) {
    if ($attempt['state'] === 'finished') {
        // Тест сдан
        $finishedAt = date('Y-m-d H:i:s', $attempt['timefinish']);
        $grade = $attempt['sumgrades'] ?? null;
    }
}
```

### 2. `getStudentQuizGrades()`

**Расположение:** `app/Services/MoodleApiService.php:851`

**Что делает:**
- Получает лучшие оценки студента по всем тестам курса
- Использует `mod_quiz_get_user_best_grade` для каждого теста
- Возвращает массив оценок, сгруппированных по ID теста

**Пример использования:**
```php
$grades = $moodleApi->getStudentQuizGrades(
    $courseId,        // Moodle Course ID
    $studentMoodleId, // Moodle User ID студента
    $quizzes          // Массив тестов (опционально)
);

// $grades[93] - лучшая оценка студента за тест с ID 93
if (isset($grades[93])) {
    $grade = $grades[93]['grade'] ?? null;
    $hasGrade = $grades[93]['hasgrade'] ?? false;
    
    if ($hasGrade && $grade !== null) {
        // Студент получил оценку!
    }
}
```

## Полный процесс проверки: сдан ли тест и есть ли оценка

```php
// 1. Получаем тесты курса
$quizzes = $moodleApi->getCourseQuizzes($courseId);

// 2. Получаем попытки студента
$attempts = $moodleApi->getStudentQuizAttempts($courseId, $studentMoodleId, $quizzes);

// 3. Получаем оценки студента
$grades = $moodleApi->getStudentQuizGrades($courseId, $studentMoodleId, $quizzes);

// 4. Проверяем конкретный тест (например, ID = 93)
$quizId = 93;

// Есть ли попытки?
$quizAttempts = $attempts[$quizId] ?? [];
$isFinished = false;
$finishedAt = null;

foreach ($quizAttempts as $attempt) {
    if ($attempt['state'] === 'finished') {
        $isFinished = true;
        $finishedAt = $attempt['timefinish'] ?? null;
        break;
    }
}

// Есть ли оценка?
$quizGrade = $grades[$quizId] ?? null;
$hasGrade = false;
$gradeValue = null;

if ($quizGrade && isset($quizGrade['hasgrade']) && $quizGrade['hasgrade']) {
    $hasGrade = true;
    $gradeValue = $quizGrade['grade'] ?? null;
}

// Результат
if ($isFinished && $hasGrade) {
    echo "Тест сдан! Оценка: {$gradeValue}";
} elseif ($isFinished && !$hasGrade) {
    echo "Тест сдан, но оценка еще не выставлена";
} else {
    echo "Тест не сдан";
}
```

## Важные замечания

1. **Всегда используйте Moodle ID**, а не локальные ID из системы:
   - `quizid` - это Moodle Quiz ID (из поля `id` в результате `mod_quiz_get_quizzes_by_courses`)
   - `userid` - это Moodle User ID (из поля `moodle_user_id` в таблице `users`)

2. **Проверка статуса попытки:**
   - `state: "finished"` - тест завершен (сдан)
   - `state: "inprogress"` - тест в процессе
   - `state: "overdue"` - тест просрочен

3. **Оценка может быть 0:**
   - `grade: 0` - это валидная оценка (студент получил 0 баллов)
   - Проверяйте `hasgrade: true`, а не только наличие значения `grade`

4. **Права доступа:**
   - Токен Moodle API должен иметь права на выполнение:
     - `mod/quiz:view` - для просмотра тестов
     - `mod/quiz:attempt` - для просмотра попыток
     - `mod/quiz:viewreports` - для просмотра оценок

## Где это используется в системе

1. **StudentReviewController::syncCourseData()** - синхронизация данных о тестах
2. **StudentReviewController::index()** - отображение статусов тестов
3. **MoodleTestController::testQuizzes()** - тестирование API

Все эти методы используют правильные Moodle ID и корректно обрабатывают информацию о попытках и оценках.
