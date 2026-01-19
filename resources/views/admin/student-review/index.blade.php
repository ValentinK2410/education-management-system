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
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Проверка студентов
                    </h4>
                    <p class="text-muted mb-0 mt-2">
                        Просмотр и проверка заданий, тестов и форумов студентов со всех ваших курсов
                    </p>
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
                                    <button class="btn btn-primary w-100" onclick="applyFilters('assignments')">
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
                                    <button class="btn btn-primary w-100" onclick="applyFilters('quizzes')">
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
                                                    $moodleUrl = $quiz->activity->moodle_url;
                                                }
                                            } catch (\Exception $e) {
                                                $moodleUrl = null;
                                            }
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
                                            </td>
                                            <td>
                                                @if($quiz->status === 'answered')
                                                    <span class="badge bg-success status-badge">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        {{ $quiz->status_text }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning status-badge">
                                                        <i class="fas fa-exclamation-circle me-1"></i>
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
                                    <button class="btn btn-primary w-100" onclick="applyFilters('forums')">
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

function switchTab(evt, tabName) {
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
}

// Функция фильтрации строк таблицы
function filterTableRows(tabName) {
    const tbody = document.getElementById(tabName + '-tbody');
    if (!tbody) return;

    const filters = filterState[tabName];
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        const student = (row.getAttribute('data-student') || '').toLowerCase();
        const course = (row.getAttribute('data-course') || '').toLowerCase();
        const activity = (row.getAttribute('data-activity') || '').toLowerCase();

        const matchName = !filters.name || activity.includes(filters.name.toLowerCase());
        const matchStudent = !filters.student || student.includes(filters.student.toLowerCase());
        const matchCourse = !filters.course || course.includes(filters.course.toLowerCase());

        if (matchName && matchStudent && matchCourse) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }

    // Применяем сортировку после фильтрации
    sortTable(tabName, sortState[tabName].column, sortState[tabName].direction);
}

// Функция сортировки таблицы
function sortTable(tabName, column, direction) {
    if (direction === 'none') return;

    const tbody = document.getElementById(tabName + '-tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.getElementsByTagName('tr'));
    const visibleRows = rows.filter(row => row.style.display !== 'none');

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

    // Перемещаем отсортированные видимые строки
    visibleRows.forEach(row => tbody.appendChild(row));
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

// Применение фильтров (вызывается по кнопке "Найти" или Enter)
function applyFilters(tabName) {
    const prefix = tabName.slice(0, -1); // assignments -> assignment

    // Получаем значения из полей ввода
    const nameInput = document.getElementById(`search-${prefix}-name`);
    const studentInput = document.getElementById(`search-${prefix}-student`);
    const courseInput = document.getElementById(`search-${prefix}-course`);

    // Обновляем состояние фильтров
    filterState[tabName] = {
        name: nameInput ? nameInput.value : '',
        student: studentInput ? studentInput.value : '',
        course: courseInput ? courseInput.value : ''
    };

    // Применяем фильтрацию
    filterTableRows(tabName);
}

// Сброс фильтров
function resetFilters(tabName) {
    filterState[tabName] = { name: '', student: '', course: '' };

    // Очищаем поля ввода
    const prefix = tabName.slice(0, -1);
    const nameInput = document.getElementById(`search-${prefix}-name`);
    const studentInput = document.getElementById(`search-${prefix}-student`);
    const courseInput = document.getElementById(`search-${prefix}-course`);

    if (nameInput) nameInput.value = '';
    if (studentInput) studentInput.value = '';
    if (courseInput) courseInput.value = '';

    // Применяем фильтрацию
    filterTableRows(tabName);
}

// При загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация обработчиков сортировки для каждой вкладки
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
    });

    // Проверяем параметр tab в URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab') || 'assignments';

    const tabElement = document.getElementById('tab-' + tabParam);
    if (tabElement) {
        switchTab(null, tabParam);
    }
});
</script>
@endsection
