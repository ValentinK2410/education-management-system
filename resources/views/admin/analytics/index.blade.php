@extends('layouts.admin')

@section('title', 'Аналитика курсов')
@section('page-title', 'Аналитика курсов')

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Фильтры аналитики
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.analytics.index') }}" id="analytics-filter-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="course_id" class="form-label">Курс</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">Все курсы</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ (request('course_id') == $course->id || request('course_id') == (string)$course->id) ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">Студент</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Все студенты</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ (request('user_id') == $student->id || request('user_id') == (string)$student->id) ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="activity_type" class="form-label">Тип элемента</label>
                                <select class="form-select" id="activity_type" name="activity_type">
                                    <option value="">Все типы</option>
                                    <option value="assign" {{ (request('activity_type') == 'assign') ? 'selected' : '' }}>📄 Задания</option>
                                    <option value="quiz" {{ (request('activity_type') == 'quiz') ? 'selected' : '' }}>✅ Тесты</option>
                                    <option value="forum" {{ (request('activity_type') == 'forum') ? 'selected' : '' }}>💬 Форумы</option>
                                    <option value="resource" {{ (request('activity_type') == 'resource') ? 'selected' : '' }}>📚 Материалы</option>
                                    <option value="exam" {{ (request('activity_type') == 'exam') ? 'selected' : '' }}>🎓 Экзамены</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="status" class="form-label">Статус</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Все статусы</option>
                                    <option value="not_started" {{ (request('status') == 'not_started') ? 'selected' : '' }}>Не начато</option>
                                    <option value="submitted" {{ (request('status') == 'submitted') ? 'selected' : '' }}>Сдано</option>
                                    <option value="graded" {{ (request('status') == 'graded') ? 'selected' : '' }}>Проверено</option>
                                    <option value="completed" {{ (request('status') == 'completed') ? 'selected' : '' }}>Завершено</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Дата от</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Дата до</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="min_grade" class="form-label">Мин. оценка</label>
                                <input type="number" class="form-control" id="min_grade" name="min_grade" value="{{ request('min_grade') }}" step="0.01">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="max_grade" class="form-label">Макс. оценка</label>
                                <input type="number" class="form-control" id="max_grade" name="max_grade" value="{{ request('max_grade') }}" step="0.01">
                            </div>
                            
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Применить фильтры
                                </button>
                                <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Сбросить
                                </a>
                                <button type="button" class="btn btn-info ms-2" onclick="syncActivities()" id="sync-btn">
                                    <i class="fas fa-sync me-2"></i>Синхронизировать данные
                                </button>
                                <div class="btn-group ms-2">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-download me-2"></i>Экспорт
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.analytics.export.csv', request()->all()) }}">
                                            <i class="fas fa-file-csv me-2"></i>CSV
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.analytics.export.excel', request()->all()) }}">
                                            <i class="fas fa-file-excel me-2"></i>Excel
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.analytics.export.pdf', request()->all()) }}">
                                            <i class="fas fa-file-pdf me-2"></i>PDF
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    @if(isset($stats))
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Всего записей</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Не начато</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['not_started'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Сдано</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['submitted'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Проверено</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['graded'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Таблица данных -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>Результаты аналитики
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Студент</th>
                                    <th>Курс</th>
                                    <th>Элемент курса</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th>Оценка</th>
                                    <th>Дата сдачи</th>
                                    <th>Дата проверки</th>
                                    <th>Проверил</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $activity['student_name'] }}</strong>
                                                <br><small class="text-muted">{{ $activity['student_email'] }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $activity['course_name'] }}</td>
                                        <td>{{ $activity['activity_name'] }}</td>
                                        <td>
                                            @if($activity['activity_type'] == 'assign')
                                                <span class="badge activity-type-badge activity-type-assign">
                                                    <i class="fas fa-file-alt me-1"></i>Задание
                                                </span>
                                            @elseif($activity['activity_type'] == 'quiz')
                                                <span class="badge activity-type-badge activity-type-quiz">
                                                    <i class="fas fa-clipboard-check me-1"></i>Тест
                                                </span>
                                            @elseif($activity['activity_type'] == 'forum')
                                                <span class="badge activity-type-badge activity-type-forum">
                                                    <i class="fas fa-comments me-1"></i>Форум
                                                </span>
                                            @elseif($activity['activity_type'] == 'resource')
                                                <span class="badge activity-type-badge activity-type-resource">
                                                    <i class="fas fa-book me-1"></i>Материал
                                                </span>
                                            @elseif($activity['activity_type'] == 'exam')
                                                <span class="badge activity-type-badge activity-type-exam">
                                                    <i class="fas fa-graduation-cap me-1"></i>Экзамен
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ $activity['activity_type'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'not_started' => 'bg-secondary',
                                                    'in_progress' => 'bg-warning',
                                                    'submitted' => 'bg-info',
                                                    'graded' => 'bg-success',
                                                    'completed' => 'bg-primary',
                                                ];
                                                $class = $statusClass[$activity['status']] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $class }}">{{ $activity['status_text'] }}</span>
                                        </td>
                                        <td>
                                            @if($activity['grade'] !== null)
                                                <strong>{{ $activity['grade'] }}</strong>
                                                @if($activity['max_grade'])
                                                    / {{ $activity['max_grade'] }}
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity['submitted_at'] && $activity['status'] == 'submitted')
                                                @php
                                                    $submittedDate = \Carbon\Carbon::parse($activity['submitted_at']);
                                                    $daysAgo = now()->diffInDays($submittedDate);
                                                    $dateClass = 'submitted-date-cell ';
                                                    if ($daysAgo < 1) {
                                                        $dateClass .= 'submitted-date-recent';
                                                    } elseif ($daysAgo < 3) {
                                                        $dateClass .= 'submitted-date-1-3days';
                                                    } elseif ($daysAgo < 7) {
                                                        $dateClass .= 'submitted-date-3-7days';
                                                    } elseif ($daysAgo < 14) {
                                                        $dateClass .= 'submitted-date-7-14days';
                                                    } else {
                                                        $dateClass .= 'submitted-date-old';
                                                    }
                                                @endphp
                                                <span class="{{ $dateClass }}">{{ $activity['submitted_at'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $activity['graded_at'] ?? '—' }}</td>
                                        <td>{{ $activity['graded_by'] ?: '—' }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $activity['user_id'] ?? '#') }}" class="btn btn-sm btn-info" title="Просмотр студента">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="alert {{ isset($hasNoData) && $hasNoData ? 'alert-info' : 'text-muted' }}">
                                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                                <p><strong>{{ isset($noDataMessage) && $noDataMessage ? $noDataMessage : 'Данные не найдены' }}</strong></p>
                                                @if(!isset($hasNoData) || !$hasNoData)
                                                <p class="small mb-3">Возможные причины:</p>
                                                <ul class="list-unstyled small">
                                                    <li>• Данные еще не синхронизированы из Moodle</li>
                                                    <li>• Выбранные фильтры не соответствуют данным</li>
                                                    <li>• Студенты не выполнили задания</li>
                                                </ul>
                                                @endif
                                                <button type="button" class="btn btn-primary mt-3" onclick="syncActivities()">
                                                    <i class="fas fa-sync me-2"></i>Запустить синхронизацию из Moodle
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($pagination))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pagination->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #4e73df;
}

.border-left-success {
    border-left: 4px solid #1cc88a;
}

.border-left-info {
    border-left: 4px solid #36b9cc;
}

.border-left-warning {
    border-left: 4px solid #f6c23e;
}

/* Стили для бейджей типов элементов курса - нейтральные цвета */
.activity-type-badge {
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.4rem 0.65rem;
    border: 1px solid #dee2e6;
    background-color: #6c757d !important;
    color: #ffffff !important;
}

.activity-type-badge i {
    color: #ffffff;
    opacity: 0.9;
}

/* Подсветка даты сдачи в зависимости от давности */
.submitted-date-cell {
    padding: 0.5rem 0.75rem;
    border-radius: 0.25rem;
    font-weight: 500;
}

.submitted-date-recent {
    background-color: #fff9c4; /* Светло-желтый - менее 1 дня */
}

.submitted-date-1-3days {
    background-color: #ffe082; /* Желтый - 1-3 дня */
}

.submitted-date-3-7days {
    background-color: #ffb74d; /* Оранжевый - 3-7 дней */
}

.submitted-date-7-14days {
    background-color: #ff8a65; /* Красно-оранжевый - 7-14 дней */
}

.submitted-date-old {
    background-color: #d32f2f; /* Темно-красный - более 14 дней */
    color: #ffffff;
}
</style>

<script>
function syncActivities() {
    const btn = document.getElementById('sync-btn') || document.querySelector('button[onclick="syncActivities()"]');
    const originalText = btn ? btn.innerHTML : '';
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Синхронизация...';
    }
    
    // Показываем уведомление
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show';
    alertDiv.innerHTML = `
        <strong>Синхронизация запущена!</strong> Это может занять некоторое время. Страница обновится автоматически после завершения.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
    
    // Получаем значения фильтров
    const courseIdEl = document.getElementById('course_id');
    const userIdEl = document.getElementById('user_id');
    const courseId = courseIdEl && courseIdEl.value ? courseIdEl.value : null;
    const userId = userIdEl && userIdEl.value ? userIdEl.value : null;
    
    // Получаем CSRF токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Ошибка: CSRF токен не найден');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
        return;
    }
    
    // Подготавливаем данные для отправки
    const requestData = {};
    if (courseId) requestData.course_id = courseId;
    if (userId) requestData.user_id = userId;
    
    console.log('Отправка запроса синхронизации:', requestData);
    
    // Отправляем запрос на синхронизацию
    fetch('{{ route("admin.analytics.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Ошибка сервера');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <strong>Синхронизация завершена!</strong> ${data.message || 'Данные успешно синхронизированы.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            // Обновляем страницу через 2 секунды
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                <strong>Ошибка синхронизации!</strong> ${data.message || 'Произошла ошибка при синхронизации.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            <strong>Ошибка!</strong> ${error.message || 'Не удалось выполнить синхронизацию. Проверьте консоль браузера для деталей.'}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}
</script>
@endsection

