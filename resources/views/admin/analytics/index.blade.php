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
                                    <option value="assign" {{ (request('activity_type') == 'assign') ? 'selected' : '' }}>Задания</option>
                                    <option value="quiz" {{ (request('activity_type') == 'quiz') ? 'selected' : '' }}>Тесты</option>
                                    <option value="forum" {{ (request('activity_type') == 'forum') ? 'selected' : '' }}>Форумы</option>
                                    <option value="resource" {{ (request('activity_type') == 'resource') ? 'selected' : '' }}>Материалы</option>
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
                                            <span class="badge bg-info">
                                                @if($activity['activity_type'] == 'assign')
                                                    Задание
                                                @elseif($activity['activity_type'] == 'quiz')
                                                    Тест
                                                @elseif($activity['activity_type'] == 'forum')
                                                    Форум
                                                @elseif($activity['activity_type'] == 'resource')
                                                    Материал
                                                @else
                                                    {{ $activity['activity_type'] }}
                                                @endif
                                            </span>
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
                                        <td>{{ $activity['submitted_at'] ?? '—' }}</td>
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
                                            <div class="text-muted">
                                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                                <p><strong>Данные не найдены</strong></p>
                                                <p class="small mb-3">Возможные причины:</p>
                                                <ul class="list-unstyled small">
                                                    <li>• Данные еще не синхронизированы из Moodle</li>
                                                    <li>• Выбранные фильтры не соответствуют данным</li>
                                                    <li>• Студенты не выполнили задания</li>
                                                </ul>
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
</style>
@endsection

