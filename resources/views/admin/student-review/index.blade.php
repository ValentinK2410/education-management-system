@extends('layouts.admin')

@section('title', 'Проверка студентов')
@section('page-title', 'Проверка студентов')

@push('styles')
<style>
    /* Tabs */
    .tabs-container {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        overflow-x: auto;
    }

    .tab-button {
        flex: 1;
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
        min-width: 150px;
    }

    .tab-button:hover {
        background: #f1f5f9;
        color: #667eea;
    }

    .tab-button.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: white;
    }

    .tab-button i {
        font-size: 1.125rem;
    }

    .tab-content {
        display: none;
        padding: 2rem;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .message-preview {
        max-width: 400px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .tabs-nav {
            flex-direction: column;
        }

        .tab-button {
            border-bottom: 1px solid #e2e8f0;
            border-right: none;
        }

        .tab-button.active {
            border-bottom-color: #e2e8f0;
            border-left: 3px solid #667eea;
        }
    }

    /* Панель поиска */
    .search-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    /* Сортировка */
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
    }

    .sortable:hover {
        background-color: #f1f5f9;
    }

    .sortable i {
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .sortable:hover i {
        opacity: 1;
    }

    .sortable[data-sort="asc"] i::before {
        content: "\f0de";
        opacity: 1;
        color: #667eea;
    }

    .sortable[data-sort="desc"] i::before {
        content: "\f0dd";
        opacity: 1;
        color: #667eea;
    }

    /* Оранжевый крестик для не сданных тестов */
    .quiz-not-submitted {
        color: #ff8c00;
        font-size: 1.2rem;
        margin-left: 0.5rem;
    }

    .quiz-not-submitted:hover {
        color: #ff6b00;
    }

    /* Темная тема */
    [data-theme="dark"] .search-panel {
        background: var(--card-bg);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .search-panel .form-control {
        background-color: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .sortable:hover {
        background-color: rgba(99, 102, 241, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Индикатор синхронизации -->
    <div id="sync-progress" class="alert alert-info d-none mb-3" role="alert">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <div class="flex-grow-1">
                <strong>Синхронизация данных...</strong>
                <div id="sync-progress-text" class="small mt-1">Подготовка к синхронизации</div>
            </div>
            <div id="sync-progress-bar" class="progress flex-grow-1 ms-3" style="max-width: 300px; height: 20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Проверка студентов
                            </h4>
                            <p class="text-muted mb-0 mt-2">
                                Просмотр и проверка заданий, тестов и форумов студентов со всех ваших курсов
                            </p>
                        </div>
                        <div>
                            <button id="check-moodle-btn" class="btn btn-sm btn-info" onclick="checkMoodleAssignments()">
                                <i class="fas fa-search me-1"></i>
                                Проверить данные в Moodle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Модальное окно для отображения результатов проверки Moodle -->
            <div class="modal fade" id="moodleCheckModal" tabindex="-1" aria-labelledby="moodleCheckModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="moodleCheckModalLabel">
                                <i class="fas fa-database me-2"></i>
                                Результаты проверки данных в Moodle
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="moodle-check-loading" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Загрузка...</span>
                                </div>
                                <p class="mt-3">Проверка данных в Moodle...</p>
                            </div>
                            <div id="moodle-check-results" style="display: none;">
                                <!-- Результаты будут вставлены сюда -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                            <button type="button" class="btn btn-primary" onclick="copyMoodleCheckResults()">
                                <i class="fas fa-copy me-1"></i>
                                Копировать результаты
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-button {{ request('tab', 'assignments') === 'assignments' ? 'active' : '' }}" onclick="switchTab(event, 'assignments')" data-tab="assignments">
                        <i class="fas fa-file-alt"></i>
                        <span>Задания <span class="badge bg-warning ms-2">{{ $assignments->count() }}</span></span>
                    </button>
                    <button class="tab-button {{ request('tab') === 'quizzes' ? 'active' : '' }}" onclick="switchTab(event, 'quizzes')" data-tab="quizzes">
                        <i class="fas fa-question-circle"></i>
                        <span>Тесты <span class="badge bg-info ms-2">{{ $quizzes->count() }}</span></span>
                    </button>
                    <button class="tab-button {{ request('tab') === 'forums' ? 'active' : '' }}" onclick="switchTab(event, 'forums')" data-tab="forums">
                        <i class="fas fa-comments"></i>
                        <span>Форумы <span class="badge bg-danger ms-2">{{ $forums->count() }}</span></span>
                    </button>
                </div>

                <!-- Tab: Задания -->
                <div id="tab-assignments" class="tab-content {{ request('tab', 'assignments') === 'assignments' ? 'active' : '' }}">
                    @if($assignments->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет заданий, ожидающих проверки</p>
                        </div>
                    @else
                        <!-- Панель поиска для заданий -->
                        <div class="search-panel mb-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по названию</label>
                                    <input type="text" class="form-control" id="search-assignment-name" placeholder="Название задания..." onkeypress="if(event.key==='Enter') applyFilters('assignments')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по студенту</label>
                                    <input type="text" class="form-control" id="search-assignment-student" placeholder="Имя или email студента..." onkeypress="if(event.key==='Enter') applyFilters('assignments')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по курсу</label>
                                    <input type="text" class="form-control" id="search-assignment-course" placeholder="Название курса..." onkeypress="if(event.key==='Enter') applyFilters('assignments')">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" data-search-tab="assignments" onclick="applyFilters('assignments')">
                                        <i class="fas fa-search me-1"></i>
                                        Найти
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters('assignments')">
                                        <i class="fas fa-refresh me-1"></i>
                                        Сбросить фильтры
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="assignments-table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="student" data-sort="none">
                                            Студент <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="role" data-sort="none">
                                            Роль <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="course" data-sort="none">
                                            Курс <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="activity" data-sort="none">
                                            Название задания <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="status" data-sort="none">
                                            Статус <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="date" data-sort="desc">
                                            Дата <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="assignments-tbody">
                                    @foreach($assignments as $assignment)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($assignment->activity) {
                                                    $moodleUrl = $assignment->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                        @endphp
                                        <tr data-student="{{ strtolower($assignment->user->name . ' ' . $assignment->user->email) }}"
                                            data-course="{{ strtolower($assignment->course->name) }}"
                                            data-activity="{{ strtolower($assignment->activity->name ?? '') }}"
                                            data-status="{{ $assignment->status }}"
                                            data-role="{{ strtolower($assignment->user->roles->pluck('name')->join(', ')) }}"
                                            data-date="{{ $assignment->display_date ? \Carbon\Carbon::parse($assignment->display_date)->timestamp : 0 }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $assignment->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $assignment->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($assignment->user->roles->count() > 0)
                                                    @foreach($assignment->user->roles as $role)
                                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $assignment->course->name }}</strong>
                                                @if($assignment->course->code)
                                                    <br><small class="text-muted">({{ $assignment->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $assignment->activity->name ?? 'Неизвестно' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $assignment->status_class }} status-badge">
                                                    {{ $assignment->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($assignment->display_date)
                                                    {{ \Carbon\Carbon::parse($assignment->display_date)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab: Тесты -->
                <div id="tab-quizzes" class="tab-content {{ request('tab') === 'quizzes' ? 'active' : '' }}">
                    @if($quizzes->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет тестов для отображения</p>
                        </div>
                    @else
                        <!-- Панель поиска для тестов -->
                        <div class="search-panel mb-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по названию</label>
                                    <input type="text" class="form-control" id="search-quiz-name" placeholder="Название теста..." onkeypress="if(event.key==='Enter') applyFilters('quizzes')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по студенту</label>
                                    <input type="text" class="form-control" id="search-quiz-student" placeholder="Имя или email студента..." onkeypress="if(event.key==='Enter') applyFilters('quizzes')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по курсу</label>
                                    <input type="text" class="form-control" id="search-quiz-course" placeholder="Название курса..." onkeypress="if(event.key==='Enter') applyFilters('quizzes')">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" data-search-tab="quizzes" onclick="applyFilters('quizzes')">
                                        <i class="fas fa-search me-1"></i>
                                        Найти
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters('quizzes')">
                                        <i class="fas fa-refresh me-1"></i>
                                        Сбросить фильтры
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="quizzes-table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="student" data-sort="none">
                                            Студент <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="role" data-sort="none">
                                            Роль <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="course" data-sort="none">
                                            Курс <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="activity" data-sort="none">
                                            Название теста <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="status" data-sort="none">
                                            Статус <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="attempts" data-sort="none">
                                            Попытки <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="grade" data-sort="none">
                                            Оценка <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="date" data-sort="none">
                                            Дата последней попытки <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="quizzes-tbody">
                                    @foreach($quizzes as $quiz)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($quiz->activity) {
                                                    $cmid = $quiz->activity->cmid;
                                                    $moodleCourseId = $quiz->course->moodle_course_id ?? null;
                                                    $moodleUserId = $quiz->user->moodle_user_id ?? null;
                                                    
                                                    if ($cmid) {
                                                        $moodleBaseUrl = rtrim(config('services.moodle.url', ''), '/');
                                                        if ($moodleBaseUrl) {
                                                            // Формируем ссылку на отчет о попытках студента
                                                            if ($moodleUserId && $moodleCourseId) {
                                                                // Ссылка на отчет с фильтром по студенту
                                                                $moodleUrl = $moodleBaseUrl . "/mod/quiz/report.php?id={$cmid}&mode=overview&course={$moodleCourseId}";
                                                            } else {
                                                                // Общая ссылка на тест
                                                                $moodleUrl = $moodleBaseUrl . "/mod/quiz/view.php?id={$cmid}";
                                                            }
                                                        }
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                            
                                            // Определяем, не сдан ли тест
                                            $isNotSubmitted = in_array($quiz->status, ['not_answered', 'not_started']) || 
                                                             ($quiz->attempts_count === 0 && !$quiz->submitted_at);
                                        @endphp
                                        <tr data-student="{{ strtolower($quiz->user->name . ' ' . $quiz->user->email) }}"
                                            data-course="{{ strtolower($quiz->course->name) }}"
                                            data-activity="{{ strtolower($quiz->activity->name ?? '') }}"
                                            data-status="{{ $quiz->status }}"
                                            data-role="{{ strtolower($quiz->user->roles->pluck('name')->join(', ')) }}"
                                            data-attempts="{{ $quiz->attempts_count ?? 0 }}"
                                            data-grade="{{ $quiz->grade ?? 0 }}"
                                            data-date="{{ ($quiz->last_attempt_at ? \Carbon\Carbon::parse($quiz->last_attempt_at)->timestamp : ($quiz->submitted_at ? \Carbon\Carbon::parse($quiz->submitted_at)->timestamp : 0)) }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $quiz->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $quiz->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($quiz->user->roles->count() > 0)
                                                    @foreach($quiz->user->roles as $role)
                                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $quiz->course->name }}</strong>
                                                @if($quiz->course->code)
                                                    <br><small class="text-muted">({{ $quiz->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $quiz->activity->name ?? 'Неизвестно' }}</strong>
                                                @if($isNotSubmitted)
                                                    <span class="ms-2" style="color: #ff8c00;" title="Тест не сдан">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz->status === 'graded')
                                                    <span class="badge bg-success status-badge">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @elseif($quiz->status === 'submitted')
                                                    <span class="badge bg-warning status-badge">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @elseif($quiz->status === 'in_progress')
                                                    <span class="badge bg-info status-badge">
                                                        <i class="fas fa-spinner me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @elseif($quiz->status === 'answered')
                                                    <span class="badge bg-success status-badge">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger status-badge">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $quiz->attempts_count ?? 0 }}
                                            </td>
                                            <td>
                                                @if($quiz->grade !== null && $quiz->max_grade)
                                                    <strong>{{ number_format($quiz->grade, 1) }}</strong> / {{ number_format($quiz->max_grade, 1) }}
                                                    @if($quiz->max_grade > 0)
                                                        <br><small class="text-muted">({{ number_format(($quiz->grade / $quiz->max_grade) * 100, 1) }}%)</small>
                                                    @endif
                                                @elseif($quiz->max_grade)
                                                    <span class="text-muted">— / {{ number_format($quiz->max_grade, 1) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($quiz->last_attempt_at)
                                                    {{ \Carbon\Carbon::parse($quiz->last_attempt_at)->format('d.m.Y H:i') }}
                                                @elseif($quiz->submitted_at)
                                                    {{ \Carbon\Carbon::parse($quiz->submitted_at)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab: Форумы -->
                <div id="tab-forums" class="tab-content {{ request('tab') === 'forums' ? 'active' : '' }}">
                    @if($forums->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Нет форумов, ожидающих ответа</p>
                        </div>
                    @else
                        <!-- Панель поиска для форумов -->
                        <div class="search-panel mb-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по названию</label>
                                    <input type="text" class="form-control" id="search-forum-name" placeholder="Название форума..." onkeypress="if(event.key==='Enter') applyFilters('forums')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по студенту</label>
                                    <input type="text" class="form-control" id="search-forum-student" placeholder="Имя или email студента..." onkeypress="if(event.key==='Enter') applyFilters('forums')">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted">Поиск по курсу</label>
                                    <input type="text" class="form-control" id="search-forum-course" placeholder="Название курса..." onkeypress="if(event.key==='Enter') applyFilters('forums')">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" data-search-tab="forums" onclick="applyFilters('forums')">
                                        <i class="fas fa-search me-1"></i>
                                        Найти
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters('forums')">
                                        <i class="fas fa-refresh me-1"></i>
                                        Сбросить фильтры
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="forums-table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="student" data-sort="none">
                                            Студент <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="role" data-sort="none">
                                            Роль <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="course" data-sort="none">
                                            Курс <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th class="sortable" data-column="activity" data-sort="none">
                                            Название форума <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th>Последнее сообщение</th>
                                        <th class="sortable" data-column="date" data-sort="desc">
                                            Дата сообщения <i class="fas fa-sort ms-1"></i>
                                        </th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody id="forums-tbody">
                                    @foreach($forums as $forum)
                                        @php
                                            $moodleUrl = null;
                                            try {
                                                if ($forum->activity) {
                                                    $moodleUrl = $forum->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
                                        @endphp
                                        <tr data-student="{{ strtolower($forum->user->name . ' ' . $forum->user->email) }}"
                                            data-course="{{ strtolower($forum->course->name) }}"
                                            data-activity="{{ strtolower($forum->activity->name ?? '') }}"
                                            data-role="{{ strtolower($forum->user->roles->pluck('name')->join(', ')) }}"
                                            data-date="{{ $forum->submitted_at ? \Carbon\Carbon::parse($forum->submitted_at)->timestamp : 0 }}">
                                            <td>
                                                <div>
                                                    <strong>{{ $forum->user->name }}</strong><br>
                                                    <small class="text-muted">{{ $forum->user->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($forum->user->roles->count() > 0)
                                                    @foreach($forum->user->roles as $role)
                                                        <span class="badge bg-info me-1">{{ $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $forum->course->name }}</strong>
                                                @if($forum->course->code)
                                                    <br><small class="text-muted">({{ $forum->course->code }})</small>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $forum->activity->name ?? 'Неизвестно' }}</strong>
                                            </td>
                                            <td>
                                                <div class="message-preview" title="{{ $forum->message_text ?? 'Текст сообщения недоступен' }}">
                                                    {{ \Illuminate\Support\Str::limit($forum->message_text ?? 'Текст сообщения недоступен', 200) }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($forum->submitted_at)
                                                    {{ \Carbon\Carbon::parse($forum->submitted_at)->format('d.m.Y H:i') }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-danger status-badge">
                                                    <i class="fas fa-comments me-1"></i>
                                                    Ожидает ответа
                                                </span>
                                            </td>
                                            <td>
                                                @if($moodleUrl)
                                                    <a href="{{ $moodleUrl }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Перейти в Moodle
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Ссылка недоступна
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Состояние фильтров для каждой вкладки
const filterState = {
    assignments: { name: '', student: '', course: '' },
    quizzes: { name: '', student: '', course: '' },
    forums: { name: '', student: '', course: '' }
};

// Состояние сортировки
const sortState = {
    assignments: { column: 'date', direction: 'desc' },
    quizzes: { column: 'date', direction: 'none' },
    forums: { column: 'date', direction: 'desc' }
};

window.switchTab = function(evt, tabName) {
    if (evt) {
        evt.preventDefault();
    }

    // Hide all tab contents
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }

    // Remove active class from all buttons
    const tabButtons = document.getElementsByClassName('tab-button');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }

    // Show selected tab content
    document.getElementById('tab-' + tabName).classList.add('active');

    // Add active class to clicked button
    if (evt && evt.currentTarget) {
        evt.currentTarget.classList.add('active');
    } else {
        // Если вызывается программно, находим кнопку по data-tab
        const targetButton = document.querySelector(`button[data-tab="${tabName}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }
    }

    // Обновляем URL без перезагрузки страницы
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
};

// Функция фильтрации строк таблицы
function filterTableRows(tabName) {
    console.log('=== filterTableRows called for:', tabName);

    const tbody = document.getElementById(tabName + '-tbody');
    if (!tbody) {
        console.error('Table body not found for:', tabName, 'Looking for:', tabName + '-tbody');
        return;
    }

    // Инициализируем состояние фильтров, если его нет
    if (!filterState[tabName]) {
        filterState[tabName] = { name: '', student: '', course: '' };
    }

    const filters = filterState[tabName];
    const rows = Array.from(tbody.getElementsByTagName('tr'));

    console.log('Total rows:', rows.length, 'Filters:', filters);
    console.log('Filter values:', {
        name: filters.name,
        student: filters.student,
        course: filters.course
    });

    let visibleCount = 0;
    let hiddenCount = 0;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const student = (row.getAttribute('data-student') || '').toLowerCase();
        const course = (row.getAttribute('data-course') || '').toLowerCase();
        const activity = (row.getAttribute('data-activity') || '').toLowerCase();

        const matchName = !filters.name || activity.includes(filters.name.toLowerCase());
        const matchStudent = !filters.student || student.includes(filters.student.toLowerCase());
        const matchCourse = !filters.course || course.includes(filters.course.toLowerCase());

        if (matchName && matchStudent && matchCourse) {
            row.style.display = '';
            row.style.visibility = 'visible';
            visibleCount++;
        } else {
            row.style.display = 'none';
            row.style.visibility = 'hidden';
            hiddenCount++;
        }
    }

    console.log('Filtering results - Visible:', visibleCount, 'Hidden:', hiddenCount);
    console.log('=== filterTableRows completed');

    // НЕ применяем сортировку автоматически после фильтрации
    // Сортировка должна применяться отдельно при клике на заголовок
}

// Функция сортировки таблицы
function sortTable(tabName, column, direction) {
    if (direction === 'none') return;

    const tbody = document.getElementById(tabName + '-tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.getElementsByTagName('tr'));

    // Сохраняем display стили перед сортировкой
    const displayStates = rows.map(row => ({
        element: row,
        display: row.style.display || '',
        visibility: row.style.visibility || ''
    }));

    const visibleRows = displayStates.filter(state => state.display !== 'none').map(state => state.element);
    const hiddenRows = displayStates.filter(state => state.display === 'none').map(state => state.element);

    visibleRows.sort((a, b) => {
        let aVal, bVal;

        switch(column) {
            case 'student':
                aVal = (a.getAttribute('data-student') || '').toLowerCase();
                bVal = (b.getAttribute('data-student') || '').toLowerCase();
                break;
            case 'role':
                aVal = (a.getAttribute('data-role') || '').toLowerCase();
                bVal = (b.getAttribute('data-role') || '').toLowerCase();
                break;
            case 'course':
                aVal = (a.getAttribute('data-course') || '').toLowerCase();
                bVal = (b.getAttribute('data-course') || '').toLowerCase();
                break;
            case 'activity':
                aVal = (a.getAttribute('data-activity') || '').toLowerCase();
                bVal = (b.getAttribute('data-activity') || '').toLowerCase();
                break;
            case 'status':
                aVal = a.getAttribute('data-status') || '';
                bVal = b.getAttribute('data-status') || '';
                break;
            case 'attempts':
                aVal = parseInt(a.getAttribute('data-attempts') || 0);
                bVal = parseInt(b.getAttribute('data-attempts') || 0);
                break;
            case 'grade':
                aVal = parseFloat(a.getAttribute('data-grade') || 0);
                bVal = parseFloat(b.getAttribute('data-grade') || 0);
                break;
            case 'date':
                aVal = parseInt(a.getAttribute('data-date') || 0);
                bVal = parseInt(b.getAttribute('data-date') || 0);
                break;
            default:
                return 0;
        }

        let comparison = 0;
        if (typeof aVal === 'string') {
            comparison = aVal.localeCompare(bVal);
        } else {
            comparison = aVal - bVal;
        }

        return direction === 'asc' ? comparison : -comparison;
    });

    // Создаем фрагмент документа для эффективного перемещения
    const fragment = document.createDocumentFragment();

    // Добавляем отсортированные видимые строки
    visibleRows.forEach(row => {
        row.style.display = '';
        row.style.visibility = 'visible';
        fragment.appendChild(row);
    });

    // Добавляем скрытые строки
    hiddenRows.forEach(row => {
        row.style.display = 'none';
        row.style.visibility = 'hidden';
        fragment.appendChild(row);
    });

    // Очищаем tbody и добавляем все строки
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    tbody.appendChild(fragment);
}

// Обработчик клика на заголовок для сортировки
function handleSortClick(tabName, column) {
    const currentSort = sortState[tabName];

    // Определяем новое направление сортировки
    let newDirection = 'asc';
    if (currentSort.column === column) {
        if (currentSort.direction === 'asc') {
            newDirection = 'desc';
        } else if (currentSort.direction === 'desc') {
            newDirection = 'none';
        }
    }

    // Обновляем состояние
    sortState[tabName] = { column, direction: newDirection };

    // Обновляем индикаторы сортировки
    const table = document.getElementById(tabName + '-table');
    if (table) {
        const headers = table.querySelectorAll('.sortable');
        headers.forEach(header => {
            const headerColumn = header.getAttribute('data-column');
            if (headerColumn === column) {
                header.setAttribute('data-sort', newDirection);
            } else {
                header.setAttribute('data-sort', 'none');
            }
        });
    }

    // Применяем сортировку
    sortTable(tabName, column, newDirection);
}

// Функция для получения правильного префикса из имени вкладки
window.getSearchPrefix = function(tabName) {
    const prefixMap = {
        'assignments': 'assignment',
        'quizzes': 'quiz',
        'forums': 'forum'
    };
    const prefix = prefixMap[tabName] || tabName.slice(0, -1);
    console.log('getSearchPrefix:', tabName, '->', prefix);
    return prefix;
};

// Применение фильтров (вызывается по кнопке "Найти" или Enter)
window.applyFilters = function(tabName) {
    console.log('=== applyFilters called for:', tabName);

    if (!tabName) {
        console.error('tabName is required');
        return;
    }

    const prefix = getSearchPrefix(tabName);
    console.log('Prefix:', prefix, 'for tabName:', tabName);

    // Получаем значения из полей ввода
    const nameInput = document.getElementById(`search-${prefix}-name`);
    const studentInput = document.getElementById(`search-${prefix}-student`);
    const courseInput = document.getElementById(`search-${prefix}-course`);

    console.log('Inputs found:', {
        nameId: `search-${prefix}-name`,
        name: nameInput ? nameInput.value : 'NOT FOUND',
        studentId: `search-${prefix}-student`,
        student: studentInput ? studentInput.value : 'NOT FOUND',
        courseId: `search-${prefix}-course`,
        course: courseInput ? courseInput.value : 'NOT FOUND'
    });

    // Обновляем состояние фильтров
    filterState[tabName] = {
        name: nameInput ? nameInput.value.trim() : '',
        student: studentInput ? studentInput.value.trim() : '',
        course: courseInput ? courseInput.value.trim() : ''
    };

    console.log('Filter state updated:', filterState[tabName]);

    // Применяем фильтрацию
    filterTableRows(tabName);
    
    console.log('=== applyFilters completed');
};

// Сброс фильтров
window.resetFilters = function(tabName) {
    console.log('resetFilters called for:', tabName);

    filterState[tabName] = { name: '', student: '', course: '' };

    // Очищаем поля ввода
    const prefix = getSearchPrefix(tabName);
    const nameInput = document.getElementById(`search-${prefix}-name`);
    const studentInput = document.getElementById(`search-${prefix}-student`);
    const courseInput = document.getElementById(`search-${prefix}-course`);

    if (nameInput) nameInput.value = '';
    if (studentInput) studentInput.value = '';
    if (courseInput) courseInput.value = '';

    console.log('Filters reset, applying filterTableRows...');

    // Применяем фильтрацию (которая покажет все строки, так как фильтры пустые)
    filterTableRows(tabName);
};

// При загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');

    // Инициализация обработчиков сортировки и кнопок поиска для каждой вкладки
    ['assignments', 'quizzes', 'forums'].forEach(tabName => {
        // Обработчики сортировки
        const table = document.getElementById(tabName + '-table');
        if (table) {
            const sortableHeaders = table.querySelectorAll('.sortable');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.getAttribute('data-column');
                    handleSortClick(tabName, column);
                });
            });
        }

        // Добавляем обработчики для кнопок "Найти" через addEventListener
        const searchButton = document.querySelector(`button[data-search-tab="${tabName}"]`);
        if (searchButton) {
            // Удаляем старый onclick и добавляем новый обработчик
            searchButton.removeAttribute('onclick');
            searchButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Search button clicked for:', tabName);
                applyFilters(tabName);
                return false;
            });
            console.log('Search button handler added for:', tabName);
        } else {
            console.warn('Search button not found for:', tabName);
        }
    });

    // Проверяем параметр tab в URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || 'assignments';

    const tabElement = document.getElementById('tab-' + tabParam);
    if (tabElement) {
        switchTab(null, tabParam);
    }

    console.log('Initialization complete');
    
    // Асинхронная синхронизация данных из Moodle
    const courses = @json($courses ?? []);
    const currentTab = urlParams.get('tab') || 'assignments';
    
    // Функция для синхронизации данных курса
    async function syncCourseData(courseId, courseName, tab) {
        const progressDiv = document.getElementById('sync-progress');
        const progressText = document.getElementById('sync-progress-text');
        const progressBar = document.getElementById('sync-progress-bar').querySelector('.progress-bar');
        
        try {
            progressText.textContent = `Синхронизация курса: ${courseName}...`;
            
            const response = await fetch(`/admin/student-review/sync-course/${courseId}?tab=${tab}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                progressText.textContent = `✓ ${courseName}: синхронизировано ${data.updated_count} записей`;
                if (data.errors && data.errors.length > 0) {
                    console.warn('Ошибки синхронизации:', data.errors);
                }
                return true;
            } else {
                progressText.textContent = `✗ ${courseName}: ${data.error || 'Ошибка синхронизации'}`;
                console.error('Ошибка синхронизации:', data.error);
                return false;
            }
        } catch (error) {
            progressText.textContent = `✗ ${courseName}: Ошибка подключения`;
            console.error('Ошибка синхронизации курса:', error);
            return false;
        }
    }
    
    // Функция для синхронизации всех курсов
    async function syncAllCourses(tab) {
        if (courses.length === 0) {
            return;
        }
        
        const progressDiv = document.getElementById('sync-progress');
        const progressText = document.getElementById('sync-progress-text');
        const progressBar = document.getElementById('sync-progress-bar').querySelector('.progress-bar');
        
        progressDiv.classList.remove('d-none');
        progressText.textContent = `Найдено курсов: ${courses.length}`;
        progressBar.style.width = '0%';
        
        let syncedCount = 0;
        
        for (let i = 0; i < courses.length; i++) {
            const course = courses[i];
            const success = await syncCourseData(course.id, course.name, tab);
            
            if (success) {
                syncedCount++;
            }
            
            // Обновляем прогресс-бар
            const progress = ((i + 1) / courses.length) * 100;
            progressBar.style.width = progress + '%';
            
            // Небольшая задержка между запросами, чтобы не перегружать сервер
            if (i < courses.length - 1) {
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        }
        
        // Завершение синхронизации
        progressText.textContent = `Синхронизация завершена: ${syncedCount} из ${courses.length} курсов`;
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-success');
        
        // Скрываем индикатор через 3 секунды и перезагружаем страницу
        setTimeout(() => {
            progressDiv.classList.add('d-none');
            // Перезагружаем страницу для отображения обновленных данных
            window.location.reload();
        }, 3000);
    }
    
    // Автоматическая синхронизация при загрузке страницы (только для тестов и форумов)
    // Можно отключить, если нужна ручная синхронизация
    const autoSync = false; // Установите в true для автоматической синхронизации
    
    if (autoSync && (currentTab === 'quizzes' || currentTab === 'forums')) {
        // Запускаем синхронизацию через 1 секунду после загрузки страницы
        setTimeout(() => {
            syncAllCourses(currentTab);
        }, 1000);
    }
    
    // Добавляем кнопку для ручной синхронизации
    const syncButton = document.createElement('button');
    syncButton.className = 'btn btn-sm btn-primary ms-2';
    syncButton.innerHTML = '<i class="fas fa-sync me-1"></i>Обновить данные из Moodle';
    syncButton.onclick = function() {
        const activeTab = document.querySelector('.tab-button.active')?.getAttribute('data-tab') || currentTab;
        if (activeTab === 'quizzes' || activeTab === 'forums') {
            syncAllCourses(activeTab);
        } else {
            alert('Синхронизация доступна только для вкладок "Тесты" и "Форумы"');
        }
    };
    
    // Добавляем кнопку рядом с заголовком страницы
    const pageTitle = document.querySelector('.page-title');
    if (pageTitle && pageTitle.parentElement) {
        pageTitle.parentElement.appendChild(syncButton);
    }
});

// Функция для проверки данных в Moodle
async function checkMoodleAssignments() {
    const modal = new bootstrap.Modal(document.getElementById('moodleCheckModal'));
    const loadingDiv = document.getElementById('moodle-check-loading');
    const resultsDiv = document.getElementById('moodle-check-results');
    
    modal.show();
    loadingDiv.style.display = 'block';
    resultsDiv.style.display = 'none';
    
    try {
        const response = await fetch('/admin/student-review/check-moodle-assignments', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        loadingDiv.style.display = 'none';
        resultsDiv.style.display = 'block';
        
        if (data.success) {
            let html = '<div class="alert alert-success"><strong>Проверка завершена успешно</strong></div>';
            
            html += '<div class="mb-3"><h6>Сводка:</h6>';
            html += `<ul><li>Всего курсов: ${data.total_courses}</li>`;
            html += `<li>Всего заданий: ${data.summary.total_assignments}</li>`;
            html += `<li>Всего студентов: ${data.summary.total_students}</li>`;
            html += `<li>Курсов с заданиями: ${data.summary.courses_with_assignments}</li></ul></div>`;
            
            html += '<div class="accordion" id="moodleCheckAccordion">';
            
            data.courses.forEach((course, index) => {
                const accordionId = `course-${index}`;
                html += `<div class="accordion-item">`;
                html += `<h2 class="accordion-header" id="heading-${index}">`;
                html += `<button class="accordion-button ${index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#${accordionId}">`;
                html += `<strong>${course.course_name}</strong>`;
                if (course.moodle_course_id) {
                    html += ` <span class="badge bg-info ms-2">Moodle ID: ${course.moodle_course_id}</span>`;
                }
                if (course.assignments_count !== undefined) {
                    html += ` <span class="badge bg-primary ms-2">Заданий: ${course.assignments_count}</span>`;
                }
                if (course.students_count !== undefined) {
                    html += ` <span class="badge bg-success ms-2">Студентов: ${course.students_count}</span>`;
                }
                if (course.error) {
                    html += ` <span class="badge bg-danger ms-2">Ошибка</span>`;
                }
                html += `</button></h2>`;
                html += `<div id="${accordionId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#moodleCheckAccordion">`;
                html += `<div class="accordion-body">`;
                
                if (course.error) {
                    html += `<div class="alert alert-danger">${course.error}</div>`;
                }
                
                if (course.api_request) {
                    html += `<h6>Запрос к Moodle API:</h6>`;
                    html += `<pre class="bg-light p-3 rounded"><code>${JSON.stringify(course.api_request, null, 2)}</code></pre>`;
                }
                
                if (course.api_response) {
                    html += `<h6>Ответ Moodle API:</h6>`;
                    html += `<pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(course.api_response, null, 2)}</code></pre>`;
                }
                
                if (course.assignments && course.assignments.length > 0) {
                    html += `<h6>Задания (${course.assignments.length}):</h6>`;
                    html += `<ul>`;
                    course.assignments.forEach(assignment => {
                        html += `<li><strong>${assignment.name}</strong> (ID: ${assignment.id})</li>`;
                    });
                    html += `</ul>`;
                } else if (course.assignments_count === 0) {
                    html += `<div class="alert alert-warning">Заданий не найдено в Moodle</div>`;
                }
                
                if (course.students && course.students.length > 0) {
                    html += `<h6>Студенты и их сдачи:</h6>`;
                    course.students.forEach(student => {
                        html += `<div class="card mb-2">`;
                        html += `<div class="card-header"><strong>${student.student_name}</strong> (${student.student_email})`;
                        html += ` <span class="badge bg-info">Moodle ID: ${student.moodle_user_id}</span>`;
                        html += `</div>`;
                        html += `<div class="card-body">`;
                        if (student.submissions_count !== undefined) {
                            html += `<p>Сдач: ${student.submissions_count}</p>`;
                            if (student.submissions && Object.keys(student.submissions).length > 0) {
                                html += `<pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;"><code>${JSON.stringify(student.submissions, null, 2)}</code></pre>`;
                            }
                        }
                        if (student.grades_count !== undefined) {
                            html += `<p>Оценок: ${student.grades_count}</p>`;
                            if (student.grades && Object.keys(student.grades).length > 0) {
                                html += `<pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow-y: auto;"><code>${JSON.stringify(student.grades, null, 2)}</code></pre>`;
                            }
                        }
                        html += `</div></div>`;
                    });
                }
                
                html += `</div></div></div>`;
            });
            
            html += '</div>';
            
            resultsDiv.innerHTML = html;
            
            // Сохраняем данные для копирования
            window.moodleCheckData = data;
        } else {
            resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Ошибка:</strong> ${data.error || 'Неизвестная ошибка'}</div>`;
        }
    } catch (error) {
        loadingDiv.style.display = 'none';
        resultsDiv.style.display = 'block';
        resultsDiv.innerHTML = `<div class="alert alert-danger"><strong>Ошибка подключения:</strong> ${error.message}</div>`;
    }
}

// Функция для копирования результатов
function copyMoodleCheckResults() {
    if (window.moodleCheckData) {
        const text = JSON.stringify(window.moodleCheckData, null, 2);
        navigator.clipboard.writeText(text).then(() => {
            alert('Результаты скопированы в буфер обмена');
        }).catch(err => {
            console.error('Ошибка копирования:', err);
            // Fallback: создаем текстовую область
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Результаты скопированы в буфер обмена');
        });
    }
}
</script>
@endsection
