@extends('layouts.admin')

@section('title', __('messages.analytics'))
@section('page-title', __('messages.analytics'))

@push('styles')
<style>
    /* Темная тема для секции фильтров */
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

    [data-theme="dark"] .container-fluid h5,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
    }

    /* Формы в фильтрах */
    [data-theme="dark"] .container-fluid .form-label {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .form-select,
    [data-theme="dark"] .container-fluid .form-control {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .form-select:focus,
    [data-theme="dark"] .container-fluid .form-control:focus {
        background-color: var(--card-bg) !important;
        border-color: #6366f1 !important;
        color: var(--text-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
    }

    [data-theme="dark"] .container-fluid .form-select option {
        background-color: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    /* Кнопки в фильтрах */
    [data-theme="dark"] .container-fluid .btn-secondary {
        background-color: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-secondary:hover {
        background-color: #475569 !important;
        border-color: #475569 !important;
        color: var(--text-color) !important;
    }

    /* Dropdown меню экспорта */
    [data-theme="dark"] .container-fluid .dropdown-menu {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .container-fluid .dropdown-item {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .dropdown-item:hover {
        background-color: var(--dark-bg) !important;
        color: var(--text-color) !important;
    }

    /* Таблица результатов аналитики */
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

    /* Текст в таблице */
    [data-theme="dark"] .container-fluid .table .text-muted {
        color: #94a3b8 !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .container-fluid .table strong {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table h6 {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .table small {
        color: #94a3b8 !important;
    }

    /* Бейджи в таблице */
    [data-theme="dark"] .container-fluid .table .badge {
        color: white !important;
    }

    /* Кнопки в таблице */
    [data-theme="dark"] .container-fluid .table .btn-outline-primary {
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }

    [data-theme="dark"] .container-fluid .table .btn-outline-primary:hover {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .table .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .table .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .table .btn-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        border-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .table .btn-success:hover {
        background-color: rgba(16, 185, 129, 1) !important;
        border-color: rgba(16, 185, 129, 1) !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Индикатор автоматической синхронизации -->
    <div id="auto-sync-indicator" class="alert alert-info alert-dismissible fade d-none" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 10000; min-width: 300px;">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <div>
                <strong>Синхронизация данных...</strong>
                <div class="small">Обновление информации о проверенных работах</div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>{{ __('messages.filter') }} {{ __('messages.analytics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.analytics.index') }}" id="analytics-filter-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="course_id" class="form-label">{{ __('messages.course') }}</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">{{ __('messages.all_courses') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ (request('course_id') == $course->id || request('course_id') == (string)$course->id) ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">{{ __('messages.students') }}</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">{{ __('messages.all_students') }}</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ (request('user_id') == $student->id || request('user_id') == (string)$student->id) ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="activity_type" class="form-label">{{ __('messages.activity_type') }}</label>
                                <select class="form-select" id="activity_type" name="activity_type">
                                    <option value="">{{ __('messages.all_types') }}</option>
                                    <option value="assign" {{ (request('activity_type') == 'assign') ? 'selected' : '' }}>📄 {{ __('messages.assignments') }}</option>
                                    <option value="quiz" {{ (request('activity_type') == 'quiz') ? 'selected' : '' }}>✅ {{ __('messages.quizzes') }}</option>
                                    <option value="forum" {{ (request('activity_type') == 'forum') ? 'selected' : '' }}>💬 {{ __('messages.forums') }}</option>
                                    <option value="resource" {{ (request('activity_type') == 'resource') ? 'selected' : '' }}>📚 {{ __('messages.resources') }}</option>
                                    <option value="exam" {{ (request('activity_type') == 'exam') ? 'selected' : '' }}>🎓 {{ __('messages.exams') }}</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ __('messages.status') }}</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ __('messages.all_statuses') }}</option>
                                    <option value="not_started" {{ (request('status') == 'not_started') ? 'selected' : '' }}>{{ __('messages.not_started') }}</option>
                                    <option value="submitted" {{ (request('status') == 'submitted') ? 'selected' : '' }}>{{ __('messages.submitted') }}</option>
                                    <option value="graded" {{ (request('status') == 'graded') ? 'selected' : '' }}>{{ __('messages.graded') }}</option>
                                    <option value="completed" {{ (request('status') == 'completed') ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">{{ __('messages.date_from') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">{{ __('messages.date_to') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="min_grade" class="form-label">{{ __('messages.min_grade') }}</label>
                                <input type="number" class="form-control" id="min_grade" name="min_grade" value="{{ request('min_grade') }}" step="0.01">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="max_grade" class="form-label">{{ __('messages.max_grade_filter') }}</label>
                                <input type="number" class="form-control" id="max_grade" name="max_grade" value="{{ request('max_grade') }}" step="0.01">
                            </div>
                            
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>{{ __('messages.apply_filters') }}
                                </button>
                                <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>{{ __('messages.reset') }}
                                </a>
                                <button type="button" class="btn btn-info ms-2" onclick="syncActivities()" id="sync-btn">
                                    <i class="fas fa-sync me-2"></i>{{ __('messages.synchronize_data') }}
                                </button>
                                <div class="btn-group ms-2 dropdown" style="z-index: 10000 !important; position: relative;">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="exportDropdownBtn">
                                        <i class="fas fa-download me-2"></i>{{ __('messages.export') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end export-dropdown-menu" style="z-index: 9999 !important; position: absolute !important;">
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
    <div class="row mb-4" style="z-index: 0 !important; position: relative;">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('messages.total_records') }}</div>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('messages.not_started') }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('messages.submitted') }}</div>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('messages.graded') }}</div>
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

    <!-- Блок помощи -->
    <div class="row mb-4" style="z-index: 0 !important; position: relative;">
        <div class="col-12">
            <div class="card border-info" style="z-index: 0 !important; position: relative;">
                <div class="card-header bg-info bg-opacity-10" style="z-index: 0 !important;">
                    <h5 class="card-title mb-0">
                        <button class="btn btn-link text-decoration-none text-dark p-0 w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#helpBlock" aria-expanded="false" aria-controls="helpBlock">
                            <i class="fas fa-question-circle me-2"></i>{{ __('messages.help_block_title') }}
                            <i class="fas fa-chevron-down float-end"></i>
                        </button>
                    </h5>
                </div>
                <div class="collapse" id="helpBlock">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary"><i class="fas fa-palette me-2"></i>Цветовая индикация даты сдачи</h6>
                                <p class="small mb-2">Цвет фона в колонке "Дата сдачи" показывает, как давно студент ждет проверки:</p>
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <span class="badge submitted-date-cell submitted-date-recent me-2">Пример</span>
                                        <strong>Светло-желтый</strong> - сдано менее 1 дня назад (требует внимания)
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge submitted-date-cell submitted-date-1-3days me-2">Пример</span>
                                        <strong>Желтый</strong> - сдано 1-3 дня назад (рекомендуется проверить)
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge submitted-date-cell submitted-date-3-7days me-2">Пример</span>
                                        <strong>Оранжевый</strong> - сдано 3-7 дней назад (требует срочной проверки)
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge submitted-date-cell submitted-date-7-14days me-2">Пример</span>
                                        <strong>Красно-оранжевый</strong> - сдано 7-14 дней назад (критично, проверьте немедленно)
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge submitted-date-cell submitted-date-old me-2">Пример</span>
                                        <strong>Темно-красный</strong> - сдано более 14 дней назад (очень критично!)
                                    </li>
                                </ul>
                                <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i>Цветовая индикация отображается только для работ со статусом "Сдано" (еще не проверено).</p>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary"><i class="fas fa-tags me-2"></i>Статусы выполнения</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <span class="badge bg-secondary me-2">Не начато</span>
                                        Студент еще не начал выполнение элемента курса
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-warning me-2">В процессе</span>
                                        Студент начал выполнение, но еще не завершил
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-info me-2">Сдано</span>
                                        Студент сдал работу, ожидает проверки преподавателем
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-success me-2">Проверено</span>
                                        Преподаватель проверил работу и поставил оценку
                                    </li>
                                    <li class="mb-2">
                                        <span class="badge bg-primary me-2">Завершено</span>
                                        Элемент курса полностью завершен
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary"><i class="fas fa-filter me-2"></i>Фильтры</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2"><strong>Курс</strong> - выберите конкретный курс для просмотра аналитики</li>
                                    <li class="mb-2"><strong>Студент</strong> - фильтрация по конкретному студенту</li>
                                    <li class="mb-2"><strong>Тип элемента</strong> - фильтр по типу (Задания, Тесты, Форумы, Материалы, Экзамены)</li>
                                    <li class="mb-2"><strong>Статус</strong> - фильтр по статусу выполнения</li>
                                    <li class="mb-2"><strong>Дата от/до</strong> - фильтр по дате сдачи в указанном диапазоне</li>
                                    <li class="mb-2"><strong>Мин./Макс. оценка</strong> - фильтр по диапазону оценок</li>
                                </ul>
                                <p class="small text-muted mb-0"><i class="fas fa-lightbulb me-1"></i>Используйте кнопку "Применить фильтры" для применения выбранных параметров.</p>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary"><i class="fas fa-cogs me-2"></i>Кнопки и действия</h6>
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <button class="btn btn-sm btn-info disabled me-2"><i class="fas fa-sync"></i></button>
                                        <strong>Синхронизировать данные</strong> - обновляет данные из Moodle. Используйте при необходимости актуализировать информацию о выполненных работах.
                                    </li>
                                    <li class="mb-2">
                                        <button class="btn btn-sm btn-success disabled me-2"><i class="fas fa-download"></i></button>
                                        <strong>Экспорт</strong> - позволяет экспортировать данные в Excel, CSV или PDF форматы для дальнейшего анализа.
                                    </li>
                                    <li class="mb-2">
                                        <button class="btn btn-sm btn-secondary disabled me-2"><i class="fas fa-times"></i></button>
                                        <strong>Сбросить</strong> - очищает все примененные фильтры и показывает все данные.
                                    </li>
                                    <li class="mb-2">
                                        <button class="btn btn-sm btn-primary disabled me-2"><i class="fas fa-filter"></i></button>
                                        <strong>Применить фильтры</strong> - применяет выбранные параметры фильтрации к таблице.
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <h6 class="text-primary"><i class="fas fa-chart-bar me-2"></i>Блок статистики</h6>
                                <p class="small mb-2">В верхней части страницы отображается статистика по выбранным фильтрам:</p>
                                <ul class="list-unstyled small">
                                    <li class="mb-1"><strong>Всего записей</strong> - общее количество элементов курса в выборке</li>
                                    <li class="mb-1"><strong>Не начато</strong> - количество элементов, которые студенты еще не начали выполнять</li>
                                    <li class="mb-1"><strong>Сдано</strong> - количество работ, ожидающих проверки (обратите внимание на цвет даты сдачи!)</li>
                                    <li class="mb-1"><strong>Проверено</strong> - количество проверенных работ</li>
                                    <li class="mb-1"><strong>Завершено</strong> - количество полностью завершенных элементов</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-lightbulb me-2"></i>Рекомендации по использованию</h6>
                                <ol class="small">
                                    <li class="mb-2">Начните с фильтрации по вашему курсу для фокусировки на нужных данных</li>
                                    <li class="mb-2">Обращайте внимание на <strong>красные и оранжевые</strong> даты сдачи - это работы, требующие срочной проверки</li>
                                    <li class="mb-2">Используйте фильтр "Статус: Сдано" для просмотра всех работ, ожидающих проверки</li>
                                    <li class="mb-2">Регулярно синхронизируйте данные, особенно перед началом проверки работ</li>
                                    <li class="mb-2">Используйте экспорт для создания отчетов и анализа прогресса студентов</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                                    try {
                                                        $submittedDate = \Carbon\Carbon::createFromFormat('d.m.Y H:i', $activity['submitted_at']);
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
                                                    } catch (\Exception $e) {
                                                        $dateClass = '';
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
                                            <div class="btn-group" role="group">
                                                @php
                                                    $gradingUrl = null;
                                                    $buttonLabel = 'Просмотреть в Moodle';
                                                    $buttonIcon = 'fa-external-link-alt';
                                                    $buttonClass = 'btn-primary';
                                                    
                                                    // Определяем текст и иконку в зависимости от типа элемента
                                                    $activityTypeLabels = [
                                                        'assign' => 'Проверить задание',
                                                        'quiz' => 'Просмотреть тест',
                                                        'forum' => 'Просмотреть форум',
                                                        'resource' => 'Просмотреть материал',
                                                        'exam' => 'Просмотреть экзамен',
                                                    ];
                                                    
                                                    $activityTypeIcons = [
                                                        'assign' => 'fa-check-circle',
                                                        'quiz' => 'fa-clipboard-check',
                                                        'forum' => 'fa-comments',
                                                        'resource' => 'fa-file-alt',
                                                        'exam' => 'fa-file-signature',
                                                    ];
                                                    
                                                    if (isset($activity['activity_type'])) {
                                                        $buttonLabel = $activityTypeLabels[$activity['activity_type']] ?? 'Просмотреть в Moodle';
                                                        $buttonIcon = $activityTypeIcons[$activity['activity_type']] ?? 'fa-external-link-alt';
                                                    }
                                                    
                                                    // Определяем цвет кнопки в зависимости от статуса
                                                    if (isset($activity['status'])) {
                                                        if ($activity['status'] == 'submitted' || $activity['status'] == 'pending') {
                                                            $buttonClass = 'btn-warning';
                                                        } elseif ($activity['status'] == 'graded') {
                                                            $buttonClass = 'btn-success';
                                                        }
                                                    }
                                                    
                                                    // Пытаемся получить URL для перехода в Moodle
                                                    if (isset($moodleApiService) && $moodleApiService) {
                                                        // Если есть cmid и moodle_user_id - используем прямой метод
                                                        if (!empty($activity['cmid']) && !empty($activity['moodle_user_id'])) {
                                                            $gradingUrl = $moodleApiService->getGradingUrl(
                                                                $activity['activity_type'] ?? 'assign',
                                                                $activity['cmid'],
                                                                $activity['moodle_user_id'],
                                                                $activity['moodle_course_id'] ?? null
                                                            );
                                                        }
                                                        // Если нет cmid, но есть moodle_activity_id и moodle_course_id - пытаемся получить cmid
                                                        elseif (!empty($activity['moodle_activity_id']) && !empty($activity['moodle_course_id']) && !empty($activity['moodle_user_id'])) {
                                                            try {
                                                                $moduleName = $activity['activity_type'] ?? 'assign';
                                                                $moduleMap = [
                                                                    'assign' => 'assign',
                                                                    'quiz' => 'quiz',
                                                                    'forum' => 'forum',
                                                                ];
                                                                
                                                                if (isset($moduleMap[$moduleName])) {
                                                                    $cmResult = $moodleApiService->call('core_course_get_course_module_by_instance', [
                                                                        'module' => $moduleMap[$moduleName],
                                                                        'instance' => $activity['moodle_activity_id']
                                                                    ]);
                                                                    
                                                                    if ($cmResult !== false && !isset($cmResult['exception']) && isset($cmResult['cm']['id'])) {
                                                                        $cmid = $cmResult['cm']['id'];
                                                                        $gradingUrl = $moodleApiService->getGradingUrl(
                                                                            $moduleName,
                                                                            $cmid,
                                                                            $activity['moodle_user_id'],
                                                                            $activity['moodle_course_id']
                                                                        );
                                                                    }
                                                                }
                                                            } catch (\Exception $e) {
                                                                // Игнорируем ошибки
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($gradingUrl)
                                                    <a href="{{ $gradingUrl }}" target="_blank" class="btn btn-sm {{ $buttonClass }}" title="{{ $buttonLabel }} в Moodle">
                                                        <i class="fas {{ $buttonIcon }}"></i>
                                                    </a>
                                                @elseif(isset($moodleApiService) && $moodleApiService && !empty($activity['moodle_course_id']) && !empty($activity['moodle_user_id']))
                                                    {{-- Показываем кнопку даже если нет cmid, но есть базовые данные для генерации URL --}}
                                                    @php
                                                        // Получаем URL Moodle из конфигурации
                                                        $moodleUrl = config('services.moodle.url', '');
                                                        $moodleCourseUrl = $moodleUrl ? rtrim($moodleUrl, '/') . '/course/view.php?id=' . $activity['moodle_course_id'] : null;
                                                    @endphp
                                                    @if($moodleCourseUrl)
                                                        <a href="{{ $moodleCourseUrl }}" target="_blank" class="btn btn-sm btn-secondary" title="Перейти в курс Moodle">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                                
                                                <a href="{{ route('admin.users.show', $activity['user_id'] ?? '#') }}" class="btn btn-sm btn-info" title="Просмотр студента">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            </div>
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

/* Исправление z-index для выпадающего меню экспорта */
.export-dropdown-menu {
    z-index: 9999 !important;
    position: absolute !important;
}

.btn-group.dropdown {
    position: relative;
    z-index: 10000 !important;
}

/* Убеждаемся, что родительские элементы не обрезают меню */
.card-body {
    overflow: visible !important;
    position: relative;
}

.card {
    overflow: visible !important;
    position: relative;
}

/* Все карточки должны быть ниже выпадающего меню, кроме выпадающего меню */
.card:not(.export-dropdown-menu):not(.dropdown-menu) {
    z-index: 1 !important;
}

/* Карточки статистики должны быть ниже выпадающего меню */
.card.border-left-primary,
.card.border-left-success,
.card.border-left-info,
.card.border-left-warning {
    position: relative !important;
    z-index: 0 !important;
}

/* Специфично для карточки "Не начато" */
.card.border-left-warning {
    z-index: 0 !important;
    position: relative !important;
}

.card.border-left-warning .card-body {
    z-index: 0 !important;
    position: relative !important;
}

/* Блок помощи также должен быть ниже выпадающего меню */
.card.border-info {
    z-index: 0 !important;
    position: relative !important;
}

.card.border-info .card-body {
    z-index: 0 !important;
    position: relative !important;
}

.card.border-info .card-header {
    z-index: 0 !important;
    position: relative !important;
}

/* Убеждаемся, что выпадающее меню отображается поверх всего */
.dropdown-menu.show,
.dropdown-menu,
#exportDropdownBtn + .dropdown-menu {
    z-index: 9999 !important;
    position: absolute !important;
}

/* Контейнер формы фильтров */
form#analytics-filter-form {
    position: relative;
    z-index: 10000 !important;
}

/* Родительские контейнеры */
.row {
    position: relative;
}
</style>

<script>
// Показываем индикатор автоматической синхронизации при загрузке страницы
@if(isset($hasAutoSynced) && $hasAutoSynced)
document.addEventListener('DOMContentLoaded', function() {
    const syncIndicator = document.getElementById('auto-sync-indicator');
    if (syncIndicator) {
        syncIndicator.classList.remove('d-none');
        syncIndicator.classList.add('show');
        
        // Скрываем индикатор через 3 секунды
        setTimeout(function() {
            syncIndicator.classList.remove('show');
            setTimeout(function() {
                syncIndicator.classList.add('d-none');
            }, 300);
        }, 3000);
    }
});
@endif

// Анимация иконки chevron в блоке помощи
document.addEventListener('DOMContentLoaded', function() {
    const helpBlock = document.getElementById('helpBlock');
    const chevronIcon = helpBlock?.previousElementSibling?.querySelector('.fa-chevron-down');
    
    if (helpBlock && chevronIcon) {
        helpBlock.addEventListener('show.bs.collapse', function() {
            chevronIcon.classList.remove('fa-chevron-down');
            chevronIcon.classList.add('fa-chevron-up');
        });
        
        helpBlock.addEventListener('hide.bs.collapse', function() {
            chevronIcon.classList.remove('fa-chevron-up');
            chevronIcon.classList.add('fa-chevron-down');
        });
    }
    
    // Исправление z-index для выпадающего меню экспорта
    const exportDropdownBtn = document.getElementById('exportDropdownBtn');
    const exportDropdownMenu = document.querySelector('.export-dropdown-menu');
    const exportDropdownContainer = exportDropdownBtn?.closest('.dropdown');
    
    if (exportDropdownBtn && exportDropdownMenu && exportDropdownContainer) {
        // Устанавливаем максимальный z-index при открытии меню
        exportDropdownContainer.addEventListener('show.bs.dropdown', function() {
            setTimeout(function() {
                exportDropdownMenu.style.zIndex = '99999';
                exportDropdownMenu.style.position = 'absolute';
            }, 10);
        });
        
        exportDropdownContainer.addEventListener('shown.bs.dropdown', function() {
            exportDropdownMenu.style.zIndex = '99999';
            exportDropdownMenu.style.position = 'absolute';
        });
        
        // Используем MutationObserver для отслеживания изменений класса show
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (exportDropdownMenu.classList.contains('show')) {
                        exportDropdownMenu.style.zIndex = '99999';
                        exportDropdownMenu.style.position = 'absolute';
                    }
                }
            });
        });
        
        observer.observe(exportDropdownMenu, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Также устанавливаем при клике на кнопку
        exportDropdownBtn.addEventListener('click', function(e) {
            setTimeout(function() {
                exportDropdownMenu.style.zIndex = '99999';
                exportDropdownMenu.style.position = 'absolute';
            }, 50);
        });
    }
});

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

