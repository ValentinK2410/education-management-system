@extends('layouts.admin')

@section('title', 'Синхронизация с Moodle')
@section('page-title', 'Синхронизация с Moodle')

@push('styles')
<style>
    /* Основная карточка */
    [data-theme="dark"] .container-fluid .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-header {
        background: var(--card-bg) !important;
        border-bottom-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card-body {
        background: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    /* Заголовки */
    [data-theme="dark"] .container-fluid h3,
    [data-theme="dark"] .container-fluid h5,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
    }

    /* Статистические карточки */
    [data-theme="dark"] .container-fluid .card.bg-info {
        background-color: rgba(59, 130, 246, 0.2) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-info.text-white,
    [data-theme="dark"] .container-fluid .card.bg-info .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-info .card-body,
    [data-theme="dark"] .container-fluid .card.bg-info .card-title,
    [data-theme="dark"] .container-fluid .card.bg-info h2,
    [data-theme="dark"] .container-fluid .card.bg-info h5 {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-success {
        background-color: rgba(16, 185, 129, 0.2) !important;
        border-color: rgba(16, 185, 129, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-success.text-white,
    [data-theme="dark"] .container-fluid .card.bg-success .text-white {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-success .card-body,
    [data-theme="dark"] .container-fluid .card.bg-success .card-title,
    [data-theme="dark"] .container-fluid .card.bg-success h2,
    [data-theme="dark"] .container-fluid .card.bg-success h5 {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .card.bg-light {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* Таблица */
    [data-theme="dark"] .container-fluid .table {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table thead th {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table tbody td {
        border-color: var(--border-color) !important;
        background-color: transparent !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--card-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-striped tbody tr:nth-of-type(even) {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .table-hover tbody tr:hover td {
        background-color: var(--dark-bg) !important;
        color: var(--text-color) !important;
    }

    /* Бейджи */
    [data-theme="dark"] .container-fluid .badge.bg-secondary {
        background-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    /* Pre элемент для логов */
    [data-theme="dark"] .container-fluid pre {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    /* Кнопки */
    [data-theme="dark"] .container-fluid .btn-primary {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        border-color: rgba(59, 130, 246, 0.8) !important;
    }

    [data-theme="dark"] .container-fluid .btn-info:hover {
        background-color: rgba(59, 130, 246, 1) !important;
        border-color: rgba(59, 130, 246, 1) !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-sync me-2"></i>Синхронизация данных из Moodle
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Всего курсов</h5>
                                    <h2 class="mb-0">{{ $totalCourses }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Синхронизировано с Moodle</h5>
                                    <h2 class="mb-0">{{ $coursesCount }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки синхронизации -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Действия синхронизации:</h5>
                            
                            <div class="btn-group-vertical w-100" role="group">
                                <button type="button" class="btn btn-primary btn-lg w-100 mb-2" onclick="startSyncAll()" id="sync-all-btn">
                                    <i class="fas fa-sync-alt me-2"></i>Полная синхронизация (курсы + записи студентов)
                                </button>
                                
                                <button type="button" class="btn btn-info btn-lg w-100 mb-2" onclick="startSyncCourses()" id="sync-courses-btn">
                                    <i class="fas fa-book me-2"></i>Синхронизировать только курсы
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Контейнер для отображения прогресса синхронизации -->
                    <div id="sync-progress-container" style="display: none; margin-top: 20px;">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-sync fa-spin me-2"></i>Синхронизация в процессе...
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Прогресс-бар -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span id="sync-progress-text">Подготовка...</span>
                                        <span id="sync-progress-percent">0%</span>
                                    </div>
                                    <div class="progress" style="height: 25px;">
                                        <div id="sync-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <!-- Текущий этап -->
                                <div class="mb-3">
                                    <strong>Текущий этап:</strong>
                                    <div id="sync-current-step" class="mt-2 p-2 bg-light rounded">
                                        Ожидание начала синхронизации...
                                    </div>
                                </div>
                                
                                <!-- Список обработанных курсов -->
                                <div class="mb-3">
                                    <strong>Обработанные курсы:</strong>
                                    <div id="sync-processed-items" class="mt-2" style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Название курса</th>
                                                    <th>Курс</th>
                                                    <th>Записи студентов</th>
                                                    <th>Статус</th>
                                                </tr>
                                            </thead>
                                            <tbody id="sync-items-list">
                                                <!-- Список будет заполняться динамически -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Итоговая статистика (скрыта до завершения) -->
                                <div id="sync-final-stats" style="display: none;">
                                    <div class="alert alert-success">
                                        <h6><strong>Итоговая статистика:</strong></h6>
                                        <div id="sync-final-stats-content"></div>
                                    </div>
                                </div>
                                
                                <!-- Кнопка остановки -->
                                <button type="button" class="btn btn-danger btn-sm" onclick="stopSync()" id="stop-sync-btn">
                                    <i class="fas fa-stop me-2"></i>Остановить синхронизацию
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Список курсов для синхронизации записей -->
                    @php
                        $moodleCourses = \App\Models\Course::whereNotNull('moodle_course_id')->get();
                    @endphp
                    
                    @if($moodleCourses->count() > 0)
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Синхронизировать записи студентов для конкретного курса:</h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Название курса</th>
                                                <th>Moodle ID</th>
                                                <th>Студентов</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($moodleCourses as $course)
                                                @php
                                                    $studentsCount = $course->users()
                                                        ->whereHas('roles', function ($q) {
                                                            $q->where('slug', 'student');
                                                        })
                                                        ->whereNotNull('moodle_user_id')
                                                        ->count();
                                                @endphp
                                                <tr>
                                                    <td>{{ $course->id }}</td>
                                                    <td>{{ $course->name }}</td>
                                                    <td><span class="badge bg-secondary">{{ $course->moodle_course_id }}</span></td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $studentsCount }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <form action="{{ route('admin.moodle-sync.sync-enrollments', $course->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Синхронизировать записи студентов для курса «{{ $course->name }}»?')" title="Синхронизировать только записи студентов на курс">
                                                                    <i class="fas fa-users me-1"></i>Записи
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('admin.moodle-sync.sync-activities', $course->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Синхронизировать активность студентов (элементы курса и прогресс) для курса «{{ $course->name }}»? Это может занять некоторое время.')" title="Синхронизировать элементы курса и активность всех студентов">
                                                                    <i class="fas fa-tasks me-1"></i>Активность
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Последние логи -->
                    @if(!empty($recentLogs))
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Последние логи синхронизации:</h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <pre class="mb-0" style="max-height: 300px; overflow-y: auto; font-size: 0.875rem;">@foreach($recentLogs as $log){{ $log }}
@endforeach</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        setTimeout(function() {
            alert('{{ session('success') }}');
        }, 100);
    </script>
@endif

@if(session('error'))
    <script>
        setTimeout(function() {
            alert('Ошибка: {{ session('error') }}');
        }, 100);
    </script>
@endif

@push('scripts')
<script>
// Глобальные переменные для отслеживания состояния синхронизации
let syncInProgress = false;
let syncCancelled = false;
let syncItems = [];
let currentSyncStep = 0;
let totalSyncSteps = 0;
let syncStats = {
    courses: { created: 0, updated: 0, errors: 0 },
    enrollments: { created: 0, updated: 0, skipped: 0, errors: 0 },
    successful: 0,
    failed: 0
};
let syncEnrollments = false;

function startSyncAll() {
    syncEnrollments = true;
    startSync('all');
}

function startSyncCourses() {
    syncEnrollments = false;
    startSync('courses');
}

function startSync(syncType) {
    if (syncInProgress) {
        alert('Синхронизация уже выполняется. Дождитесь завершения.');
        return;
    }
    
    // Сбрасываем состояние
    syncInProgress = true;
    syncCancelled = false;
    syncItems = [];
    currentSyncStep = 0;
    totalSyncSteps = 0;
    syncStats = {
        courses: { created: 0, updated: 0, errors: 0 },
        enrollments: { created: 0, updated: 0, skipped: 0, errors: 0 },
        successful: 0,
        failed: 0
    };
    
    const btn = syncType === 'all' ? document.getElementById('sync-all-btn') : document.getElementById('sync-courses-btn');
    const originalText = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Синхронизация...';
    }
    
    // Показываем контейнер прогресса
    const progressContainer = document.getElementById('sync-progress-container');
    if (progressContainer) {
        progressContainer.style.display = 'block';
        progressContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Сбрасываем UI прогресса
    updateProgressUI(0, 0, 'Подготовка к синхронизации...', null);
    document.getElementById('sync-items-list').innerHTML = '';
    document.getElementById('sync-final-stats').style.display = 'none';
    
    // Получаем CSRF токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF токен не найден');
        resetSyncUI(btn, originalText);
        return;
    }
    
    // Определяем маршрут
    const route = syncType === 'all' 
        ? '{{ route("admin.moodle-sync.sync-all") }}'
        : '{{ route("admin.moodle-sync.sync-courses") }}';
    
    // Отправляем запрос на получение списка курсов
    fetch(route, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        
        if (!isJson) {
            const text = await response.text();
            throw new Error(`Сервер вернул неверный формат ответа (${response.status})`);
        }
        
        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Ошибка синхронизации');
        }
        
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Ошибка синхронизации');
        }
        
        // Если это пошаговая синхронизация, получаем список курсов
        if (data.sync_type && data.courses) {
            syncItems = data.courses;
            totalSyncSteps = data.total_steps;
            currentSyncStep = 0;
            
            updateProgressUI(0, totalSyncSteps, `Начинаем синхронизацию ${totalSyncSteps} курсов...`, null);
            
            // Начинаем последовательную синхронизацию
            syncNextChunk(csrfToken, btn, originalText);
        } else {
            // Старая логика (полная синхронизация)
            showSuccessMessage(data.message || 'Синхронизация завершена');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorMessage(error.message || 'Ошибка синхронизации');
        resetSyncUI(btn, originalText);
    });
}

function syncNextChunk(csrfToken, btn, originalText) {
    if (syncCancelled || currentSyncStep >= syncItems.length) {
        finishSync(btn, originalText);
        return;
    }
    
    const currentItem = syncItems[currentSyncStep];
    currentSyncStep++;
    
    updateProgressUI(currentSyncStep, totalSyncSteps, 
        `Обрабатывается курс ${currentSyncStep} из ${totalSyncSteps}: ${currentItem.name}`, 
        currentItem);
    
    // Отправляем запрос на синхронизацию одного курса
    fetch('{{ route("admin.moodle-sync.sync-chunk") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            moodle_course_id: currentItem.moodle_id,
            sync_enrollments: syncEnrollments,
            step: currentSyncStep,
            total_steps: totalSyncSteps
        })
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');
        
        if (!isJson) {
            const text = await response.text();
            throw new Error(`Сервер вернул неверный формат ответа (${response.status})`);
        }
        
        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Ошибка синхронизации');
        }
        
        return response.json();
    })
    .then(data => {
        // Обновляем статистику
        if (data.stats) {
            if (data.stats.course) {
                syncStats.courses.created += data.stats.course.created || 0;
                syncStats.courses.updated += data.stats.course.updated || 0;
                syncStats.courses.errors += data.stats.course.errors || 0;
            }
            if (data.stats.enrollments) {
                syncStats.enrollments.created += data.stats.enrollments.created || 0;
                syncStats.enrollments.updated += data.stats.enrollments.updated || 0;
                syncStats.enrollments.skipped += data.stats.enrollments.skipped || 0;
                syncStats.enrollments.errors += data.stats.enrollments.errors || 0;
            }
        }
        
        if (data.success) {
            syncStats.successful++;
        } else {
            syncStats.failed++;
        }
        
        // Добавляем элемент в список обработанных
        addProcessedItem(currentSyncStep, currentItem, data);
        
        // Продолжаем синхронизацию следующего курса
        if (data.has_more && !syncCancelled) {
            setTimeout(() => {
                syncNextChunk(csrfToken, btn, originalText);
            }, 500);
        } else {
            finishSync(btn, originalText);
        }
    })
    .catch(error => {
        console.error('Ошибка синхронизации курса:', error);
        syncStats.failed++;
        
        // Добавляем элемент с ошибкой
        addProcessedItem(currentSyncStep, currentItem, {
            success: false,
            message: error.message || 'Ошибка синхронизации',
            stats: { course: { created: 0, updated: 0, errors: 1 }, enrollments: { created: 0, updated: 0, skipped: 0, errors: 0 } }
        });
        
        // Продолжаем синхронизацию остальных курсов
        if (currentSyncStep < syncItems.length && !syncCancelled) {
            setTimeout(() => {
                syncNextChunk(csrfToken, btn, originalText);
            }, 500);
        } else {
            finishSync(btn, originalText);
        }
    });
}

function updateProgressUI(step, total, message, currentItem) {
    const percent = total > 0 ? Math.round((step / total) * 100) : 0;
    
    document.getElementById('sync-progress-percent').textContent = percent + '%';
    document.getElementById('sync-progress-bar').style.width = percent + '%';
    document.getElementById('sync-progress-text').textContent = message;
    
    if (currentItem) {
        const stepText = `Курс ${step} из ${total}: ${currentItem.name}`;
        document.getElementById('sync-current-step').textContent = stepText;
    } else {
        document.getElementById('sync-current-step').textContent = message;
    }
}

function addProcessedItem(step, item, result) {
    const tbody = document.getElementById('sync-items-list');
    const row = document.createElement('tr');
    
    const statusClass = result.success ? 'success' : 'danger';
    const statusIcon = result.success ? 'fa-check-circle' : 'fa-times-circle';
    const statusText = result.success ? 'Успешно' : 'Ошибка';
    
    const courseStatus = result.stats?.course?.created ? 'Создан' : 
                         (result.stats?.course?.updated ? 'Обновлен' : 'Без изменений');
    
    row.innerHTML = `
        <td>${step}</td>
        <td>${item.name}</td>
        <td>
            <small>${courseStatus}</small>
        </td>
        <td>
            <small>
                Создано: ${result.stats?.enrollments?.created || 0}, 
                Обновлено: ${result.stats?.enrollments?.updated || 0}
                ${(result.stats?.enrollments?.errors || 0) > 0 ? ', Ошибок: ' + result.stats.enrollments.errors : ''}
            </small>
        </td>
        <td>
            <span class="badge bg-${statusClass}">
                <i class="fas ${statusIcon} me-1"></i>${statusText}
            </span>
        </td>
    `;
    
    tbody.appendChild(row);
    
    // Прокручиваем к последнему элементу
    const container = document.getElementById('sync-processed-items');
    container.scrollTop = container.scrollHeight;
}

function finishSync(btn, originalText) {
    syncInProgress = false;
    
    updateProgressUI(totalSyncSteps, totalSyncSteps, 'Синхронизация завершена!', null);
    
    // Показываем итоговую статистику
    const finalStatsDiv = document.getElementById('sync-final-stats');
    const finalStatsContent = document.getElementById('sync-final-stats-content');
    
    finalStatsContent.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <strong>Курсы:</strong><br>
                Создано: ${syncStats.courses.created}, 
                Обновлено: ${syncStats.courses.updated}
                ${syncStats.courses.errors > 0 ? ', Ошибок: ' + syncStats.courses.errors : ''}
            </div>
            <div class="col-md-6">
                <strong>Записи студентов:</strong><br>
                Создано: ${syncStats.enrollments.created}, 
                Обновлено: ${syncStats.enrollments.updated}
                ${syncStats.enrollments.errors > 0 ? ', Ошибок: ' + syncStats.enrollments.errors : ''}
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <strong>Итого:</strong> Успешно синхронизировано: ${syncStats.successful}, 
                Ошибок: ${syncStats.failed} из ${totalSyncSteps} курсов
            </div>
        </div>
    `;
    
    finalStatsDiv.style.display = 'block';
    
    // Обновляем заголовок
    const cardHeader = document.querySelector('#sync-progress-container .card-header');
    if (cardHeader) {
        cardHeader.className = 'card-header bg-success text-white';
        cardHeader.innerHTML = '<h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Синхронизация завершена!</h5>';
    }
    
    // Скрываем кнопку остановки
    document.getElementById('stop-sync-btn').style.display = 'none';
    
    // Восстанавливаем кнопки синхронизации
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
    
    const syncAllBtn = document.getElementById('sync-all-btn');
    const syncCoursesBtn = document.getElementById('sync-courses-btn');
    if (syncAllBtn) syncAllBtn.disabled = false;
    if (syncCoursesBtn) syncCoursesBtn.disabled = false;
    
    // Обновляем страницу через 5 секунд
    setTimeout(() => {
        window.location.reload();
    }, 5000);
}

function stopSync() {
    if (syncInProgress) {
        syncCancelled = true;
        syncInProgress = false;
        
        const cardHeader = document.querySelector('#sync-progress-container .card-header');
        if (cardHeader) {
            cardHeader.className = 'card-header bg-warning text-dark';
            cardHeader.innerHTML = '<h5 class="mb-0"><i class="fas fa-stop-circle me-2"></i>Синхронизация остановлена</h5>';
        }
        
        document.getElementById('sync-current-step').textContent = 'Синхронизация остановлена пользователем';
        document.getElementById('stop-sync-btn').style.display = 'none';
        
        const syncAllBtn = document.getElementById('sync-all-btn');
        const syncCoursesBtn = document.getElementById('sync-courses-btn');
        if (syncAllBtn) {
            syncAllBtn.disabled = false;
            syncAllBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Полная синхронизация (курсы + записи студентов)';
        }
        if (syncCoursesBtn) {
            syncCoursesBtn.disabled = false;
            syncCoursesBtn.innerHTML = '<i class="fas fa-book me-2"></i>Синхронизировать только курсы';
        }
    }
}

function resetSyncUI(btn, originalText) {
    syncInProgress = false;
    syncCancelled = false;
    
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
    
    const syncAllBtn = document.getElementById('sync-all-btn');
    const syncCoursesBtn = document.getElementById('sync-courses-btn');
    if (syncAllBtn) syncAllBtn.disabled = false;
    if (syncCoursesBtn) syncCoursesBtn.disabled = false;
    
    const progressContainer = document.getElementById('sync-progress-container');
    if (progressContainer) {
        progressContainer.style.display = 'none';
    }
}

function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <strong>Успешно!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
}

function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <strong>Ошибка!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
}
</script>
@endpush
@endsection

