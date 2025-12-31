@extends('layouts.admin')

@section('title', 'Просмотр пользователя')
@section('page-title', 'Просмотр пользователя')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .user-header-card {
        background: var(--primary-gradient);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .user-avatar-section {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .user-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .user-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .user-header-info h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .user-header-info p {
        font-size: 1.125rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }

    .user-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

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

    .info-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }

    .info-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .info-card-title i {
        color: #667eea;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 200px;
    }

    .info-label i {
        color: #667eea;
        width: 20px;
    }

    .info-value {
        color: #1e293b;
        font-weight: 500;
        text-align: right;
        flex: 1;
    }

    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .badge-success {
        background: var(--success-gradient);
        color: white;
    }

    .badge-danger {
        background: var(--danger-gradient);
        color: white;
    }

    .badge-primary {
        background: var(--primary-gradient);
        color: white;
    }

    .badge-secondary {
        background: #64748b;
        color: white;
    }

    .badge-warning {
        background: var(--warning-gradient);
        color: white;
    }

    .badge-info {
        background: var(--info-gradient);
        color: white;
    }

    .item-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .item-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
    }

    .item-title a {
        color: #1e293b;
        text-decoration: none;
        transition: color 0.2s;
    }

    .item-title a:hover {
        color: #667eea;
    }

    .item-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.75rem;
    }

    .item-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .item-meta-item i {
        color: #667eea;
    }

    .item-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-edit {
        background: var(--warning-gradient);
        color: white;
    }

    .btn-edit:hover {
        color: white;
    }

    .btn-back {
        background: #64748b;
        color: white;
    }

    .btn-back:hover {
        background: #475569;
        color: white;
    }

    .bio-section {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border-left: 4px solid #667eea;
        margin-top: 1rem;
    }

    .bio-text {
        color: #475569;
        line-height: 1.7;
        margin: 0;
    }

    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-bar-fill {
        height: 100%;
        background: var(--success-gradient);
        transition: width 0.3s ease;
    }

    @media (max-width: 768px) {
        .user-avatar-section {
            flex-direction: column;
            text-align: center;
        }

        .user-actions {
            flex-direction: column;
        }

        .info-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .info-value {
            text-align: left;
        }

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
</style>
@endpush

@section('content')
<!-- User Header Card -->
<div class="user-header-card">
    <div class="user-avatar-section">
        @if($user->avatar)
            <img src="{{ Storage::url($user->avatar) }}"
                 class="user-avatar-large"
                 alt="Avatar"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="user-avatar-placeholder" style="display: none;">
                <i class="fas fa-user"></i>
            </div>
        @else
            <div class="user-avatar-placeholder">
                <i class="fas fa-user"></i>
            </div>
        @endif
        <div class="user-header-info">
            <h2>{{ $user->name }}</h2>
            <p><i class="fas fa-envelope me-2"></i>{{ $user->email }}</p>
            @if($user->phone)
                <p><i class="fas fa-phone me-2"></i>{{ $user->phone }}</p>
            @endif
        </div>
    </div>
    <div class="user-actions">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn-action btn-edit">
            <i class="fas fa-edit"></i>
            Редактировать
                        </a>
        <a href="{{ route('admin.users.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i>
            Назад к списку
                        </a>
                    </div>
                </div>

<!-- Tabs Navigation -->
<div class="tabs-container">
    <div class="tabs-nav">
        <button class="tab-button active" onclick="switchTab(event, 'info')">
            <i class="fas fa-info-circle"></i>
            <span>Основная информация</span>
        </button>
        <button class="tab-button" onclick="switchTab(event, 'programs')">
            <i class="fas fa-book"></i>
            <span>Программы ({{ $user->programs->count() }})</span>
        </button>
        <button class="tab-button" onclick="switchTab(event, 'courses')">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Курсы ({{ $user->courses->count() }})</span>
        </button>
        <button class="tab-button" onclick="switchTab(event, 'institutions')">
            <i class="fas fa-university"></i>
            <span>Учебные заведения ({{ $user->institutions->count() }})</span>
        </button>
        <button class="tab-button" onclick="switchTab(event, 'certificates')">
            <i class="fas fa-certificate"></i>
            <span>Сертификаты и дипломы</span>
        </button>
        <button class="tab-button" onclick="switchTab(event, 'analytics')">
            <i class="fas fa-chart-line"></i>
            <span>Детальная аналитика</span>
        </button>
    </div>

    <!-- Tab: Основная информация -->
    <div id="tab-info" class="tab-content {{ request('tab', 'info') === 'info' ? 'active' : '' }}">
                    <div class="row">
            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-info-circle"></i>
                        Основная информация
                    </h5>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-hashtag"></i>
                            <span>ID</span>
                        </div>
                        <div class="info-value">#{{ $user->id }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i>
                            <span>Имя</span>
                        </div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    @if($user->phone)
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-phone"></i>
                                <span>Телефон</span>
                            </div>
                            <div class="info-value">{{ $user->phone }}</div>
                        </div>
                                        @endif
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-circle"></i>
                            <span>Статус</span>
                        </div>
                        <div class="info-value">
                            @if($user->is_active)
                                <span class="badge-custom badge-success">Активен</span>
                            @else
                                <span class="badge-custom badge-danger">Неактивен</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-calendar"></i>
                            <span>Дата регистрации</span>
                        </div>
                        <div class="info-value">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                        </div>
                    </div>

            <div class="col-lg-6">
                <div class="info-card">
                    <h5 class="info-card-title">
                        <i class="fas fa-shield-alt"></i>
                        Роли и права
                    </h5>
                    @if($user->roles->count() > 0)
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($user->roles as $role)
                                <span class="badge-custom badge-primary">{{ $role->name }}</span>
                            @endforeach
                        </div>
                                                @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Роли не назначены
                        </p>
                                                @endif

                    @if($user->bio)
                        <div class="bio-section">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-book-open me-2"></i>
                                Биография
                            </h6>
                            <p class="bio-text">{{ $user->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($user->taughtCourses->count() > 0)
            <div class="info-card">
                <h5 class="info-card-title">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Преподаваемые курсы ({{ $user->taughtCourses->count() }})
                </h5>
                <div class="row">
                    @foreach($user->taughtCourses as $course)
                        <div class="col-md-6 col-lg-4">
                            <div class="item-card">
                                <div class="item-title">
                                    <a href="{{ route('admin.courses.show', $course) }}">{{ $course->name }}</a>
                                </div>
                                <div class="item-meta">
                                    <div class="item-meta-item">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span>{{ $course->program->name ?? 'Не указано' }}</span>
                                    </div>
                                    <div class="item-meta-item">
                                        <i class="fas fa-university"></i>
                                        <span>{{ $course->program->institution->name ?? 'Не указано' }}</span>
                                    </div>
                                </div>
                                <div class="item-status">
                                    @if($course->is_active)
                                        <span class="badge-custom badge-success">Активен</span>
                                    @else
                                        <span class="badge-custom badge-secondary">Неактивен</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Tab: Программы -->
    <div id="tab-programs" class="tab-content">
        @if($user->programs->count() > 0)
            <div class="row">
                @foreach($user->programs as $program)
                    <div class="col-md-6 col-lg-4">
                        <div class="item-card">
                            <div class="item-title">
                                <a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a>
                            </div>
                            <div class="item-meta">
                                <div class="item-meta-item">
                                    <i class="fas fa-university"></i>
                                    <span>{{ $program->institution->name ?? 'Не указано' }}</span>
                                </div>
                                @if($program->pivot->enrolled_at)
                                    <div class="item-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Записан: {{ \Carbon\Carbon::parse($program->pivot->enrolled_at)->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                                @if($program->pivot->completed_at)
                                    <div class="item-meta-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Завершен: {{ \Carbon\Carbon::parse($program->pivot->completed_at)->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="item-status">
                                @php
                                    $status = $program->pivot->status ?? 'enrolled';
                                    $statusLabels = [
                                        'enrolled' => ['label' => 'Записан', 'class' => 'badge-info'],
                                        'active' => ['label' => 'Активен', 'class' => 'badge-success'],
                                        'completed' => ['label' => 'Завершен', 'class' => 'badge-primary'],
                                        'cancelled' => ['label' => 'Отменен', 'class' => 'badge-danger'],
                                    ];
                                    $statusInfo = $statusLabels[$status] ?? $statusLabels['enrolled'];
                                @endphp
                                <span class="badge-custom {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h5>Программы не найдены</h5>
                <p>Пользователь не записан ни на одну программу</p>
            </div>
        @endif
    </div>

    <!-- Tab: Курсы -->
    <div id="tab-courses" class="tab-content {{ request('tab') === 'courses' ? 'active' : '' }}">
        @if($user->courses->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 25%;">Название курса</th>
                            <th style="width: 15%;">Программа</th>
                            <th style="width: 15%;">Преподаватель</th>
                            <th style="width: 20%;">Статус заданий (ПОСЛЕ СЕССИИ)</th>
                            <th style="width: 10%;">Прогресс</th>
                            <th style="width: 10%;">Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->courses as $course)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $course->id }}</span></td>
                                <td>
                                    <strong>
                                        <a href="{{ route('admin.courses.show', $course) }}">{{ $course->name }}</a>
                                    </strong>
                                    @if($course->code)
                                        <br><small class="text-muted">{{ $course->code }}</small>
                                    @endif
                                    @if($course->pivot->enrolled_at)
                                        <br><small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Записан: {{ \Carbon\Carbon::parse($course->pivot->enrolled_at)->format('d.m.Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($course->program)
                                        <span class="badge bg-info">{{ $course->program->name }}</span>
                                        @if($course->program->institution)
                                            <br><small class="text-muted">{{ $course->program->institution->name }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Без программы</span>
                                    @endif
                                </td>
                                <td>
                                    @if($course->instructor)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-success text-white rounded-circle">
                                                    {{ substr($course->instructor->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <small class="d-block">{{ $course->instructor->name }}</small>
                                                <small class="text-muted">{{ $course->instructor->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Не назначен</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                        <div class="d-flex flex-column gap-1">
                                            @foreach($coursesWithAssignments[$course->id] as $assignment)
                                                @php
                                                    $moodleApiService = new \App\Services\MoodleApiService();
                                                    $assignmentUrl = $moodleApiService->getAssignmentUrl(
                                                        $assignment['cmid'] ?? null,
                                                        $assignment['id'] ?? null,
                                                        $course->moodle_course_id ?? null
                                                    );
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    @if($assignmentUrl && ($assignment['status'] === 'not_submitted' || $assignment['status'] === 'pending'))
                                                        <a href="{{ $assignmentUrl }}" target="_blank" class="text-decoration-none">
                                                            <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                                  title="{{ $assignment['name'] }} - Нажмите для сдачи">
                                                                @if($assignment['status'] === 'not_submitted')
                                                                    <i class="fas fa-times-circle me-1"></i>Не сдано
                                                                @elseif($assignment['status'] === 'pending')
                                                                    <i class="fas fa-clock me-1"></i>Не проверено
                                                                @else
                                                                    <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                                @endif
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                              title="{{ $assignment['name'] }}">
                                                            @if($assignment['status'] === 'not_submitted')
                                                                <i class="fas fa-times-circle me-1"></i>Не сдано
                                                            @elseif($assignment['status'] === 'pending')
                                                                <i class="fas fa-clock me-1"></i>Не проверено
                                                            @else
                                                                <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                    <small class="text-muted ms-2" title="{{ $assignment['name'] }}">
                                                        {{ \Illuminate\Support\Str::limit($assignment['name'], 30) }}
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($course->moodle_course_id && $user->moodle_user_id)
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>Задания не найдены
                                        </small>
                                    @elseif(!$user->moodle_user_id)
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Не настроена синхронизация
                                        </small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td>
                                    @if($course->pivot->progress !== null)
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $course->pivot->progress }}%"
                                                     aria-valuenow="{{ $course->pivot->progress }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    {{ $course->pivot->progress }}%
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $status = $course->pivot->status ?? 'enrolled';
                                        $statusLabels = [
                                            'enrolled' => ['label' => 'Записан', 'class' => 'bg-info'],
                                            'active' => ['label' => 'Активен', 'class' => 'bg-success'],
                                            'completed' => ['label' => 'Завершен', 'class' => 'bg-primary'],
                                            'cancelled' => ['label' => 'Отменен', 'class' => 'bg-danger'],
                                        ];
                                        $statusInfo = $statusLabels[$status] ?? $statusLabels['enrolled'];
                                    @endphp
                                    <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-chalkboard-teacher"></i>
                <h5>Курсы не найдены</h5>
                <p>Пользователь не записан ни на один курс</p>
            </div>
        @endif
    </div>
    
    <style>
    .avatar-sm {
        width: 30px;
        height: 30px;
    }

    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* Мини-бейджи для статусов заданий */
    .assignment-mini-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.7rem;
        font-weight: 700;
        white-space: nowrap;
        border-radius: 0.375rem;
        border: 2px solid transparent;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.1);
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    /* Красный - не сдано */
    .assignment-mini-badge.assignment-status-not-submitted,
    .assignment-mini-badge.assignment-status-not_submitted {
        background-color: #b91c1c;
        color: #ffffff;
        border-color: #991b1b;
        box-shadow: 0 2px 6px rgba(185, 28, 28, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
    }

    /* Желтый - не проверено */
    .assignment-mini-badge.assignment-status-pending {
        background-color: #d97706;
        color: #ffffff;
        border-color: #b45309;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
    }

    /* Зеленый - оценка */
    .assignment-mini-badge.assignment-status-graded {
        background-color: #059669;
        color: #ffffff;
        border-color: #047857;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
    }

    /* Стили для истории действий */
    .timeline-item {
        border-left: 2px solid #e3e6f0;
        padding-left: 1rem;
    }

    .timeline-marker {
        margin-left: -1.5rem;
    }
    </style>

    <!-- Tab: Учебные заведения -->
    <div id="tab-institutions" class="tab-content {{ request('tab') === 'institutions' ? 'active' : '' }}">
        @if($user->institutions->count() > 0)
            <div class="row">
                @foreach($user->institutions as $institution)
                    <div class="col-md-6 col-lg-4">
                        <div class="item-card">
                            <div class="item-title">
                                <a href="{{ route('admin.institutions.show', $institution) }}">{{ $institution->name }}</a>
                            </div>
                            <div class="item-meta">
                                @if($institution->pivot->enrolled_at)
                                    <div class="item-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Поступил: {{ \Carbon\Carbon::parse($institution->pivot->enrolled_at)->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                                @if($institution->pivot->graduated_at)
                                    <div class="item-meta-item">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span>Выпуск: {{ \Carbon\Carbon::parse($institution->pivot->graduated_at)->format('d.m.Y') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="item-status">
                                @php
                                    $status = $institution->pivot->status ?? 'student';
                                    $statusLabels = [
                                        'student' => ['label' => 'Студент', 'class' => 'badge-info'],
                                        'graduate' => ['label' => 'Выпускник', 'class' => 'badge-primary'],
                                        'staff' => ['label' => 'Сотрудник', 'class' => 'badge-warning'],
                                        'visitor' => ['label' => 'Посетитель', 'class' => 'badge-secondary'],
                                    ];
                                    $statusInfo = $statusLabels[$status] ?? $statusLabels['student'];
                                @endphp
                                <span class="badge-custom {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-university"></i>
                <h5>Учебные заведения не найдены</h5>
                <p>Пользователь не связан ни с одним учебным заведением</p>
            </div>
        @endif
    </div>

    <!-- Tab: Детальная аналитика -->
    <div id="tab-analytics" class="tab-content {{ request('tab') === 'analytics' ? 'active' : '' }}">
        @if(isset($detailedAnalytics) && count($detailedAnalytics) > 0)
            @foreach($user->courses as $course)
                @php
                    $courseAnalytics = collect($detailedAnalytics)->filter(function($item) use ($course) {
                        return $item['course']->id === $course->id;
                    });
                @endphp
                
                @if($courseAnalytics->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book me-2"></i>{{ $course->name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Элемент курса</th>
                                            <th>Тип</th>
                                            <th>Раздел</th>
                                            <th>Статус</th>
                                            <th>Оценка</th>
                                            <th>Дата сдачи</th>
                                            <th>Дата проверки</th>
                                            <th>История</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($courseAnalytics as $item)
                                            @php
                                                $activity = $item['activity'];
                                                $progress = $item['progress'];
                                                $history = $item['history'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $activity->name }}</strong>
                                                    @if($activity->description)
                                                        <br><small class="text-muted">{{ Str::limit($activity->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        @if($activity->activity_type == 'assign')
                                                            Задание
                                                        @elseif($activity->activity_type == 'quiz')
                                                            Тест
                                                        @elseif($activity->activity_type == 'forum')
                                                            Форум
                                                        @elseif($activity->activity_type == 'resource')
                                                            Материал
                                                        @else
                                                            {{ $activity->activity_type }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td>{{ $activity->section_name ?? '—' }}</td>
                                                <td>
                                                    @if($progress)
                                                        @php
                                                            $statusClass = [
                                                                'not_started' => 'bg-secondary',
                                                                'in_progress' => 'bg-warning',
                                                                'submitted' => 'bg-info',
                                                                'graded' => 'bg-success',
                                                                'completed' => 'bg-primary',
                                                            ];
                                                            $class = $statusClass[$progress->status] ?? 'bg-secondary';
                                                            $statusText = [
                                                                'not_started' => 'Не начато',
                                                                'in_progress' => 'В процессе',
                                                                'submitted' => 'Сдано',
                                                                'graded' => 'Проверено',
                                                                'completed' => 'Завершено',
                                                            ];
                                                        @endphp
                                                        <span class="badge {{ $class }}">
                                                            {{ $statusText[$progress->status] ?? $progress->status }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Не начато</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($progress && $progress->grade !== null)
                                                        <strong>{{ $progress->grade }}</strong>
                                                        @if($progress->max_grade)
                                                            / {{ $progress->max_grade }}
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $progress->submitted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                                <td>{{ $progress->graded_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                                <td>
                                                    @if($history->count() > 0)
                                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#historyModal{{ $activity->id }}">
                                                            <i class="fas fa-history me-1"></i>{{ $history->count() }}
                                                        </button>
                                                        
                                                        <!-- Модальное окно истории -->
                                                        <div class="modal fade" id="historyModal{{ $activity->id }}" tabindex="-1">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">История действий: {{ $activity->name }}</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="timeline">
                                                                            @foreach($history as $historyItem)
                                                                                <div class="timeline-item mb-3">
                                                                                    <div class="d-flex">
                                                                                        <div class="timeline-marker me-3">
                                                                                            <i class="fas fa-circle text-primary"></i>
                                                                                        </div>
                                                                                        <div class="flex-grow-1">
                                                                                            <div class="d-flex justify-content-between">
                                                                                                <strong>
                                                                                                    @if($historyItem->action_type == 'submitted')
                                                                                                        Сдано
                                                                                                    @elseif($historyItem->action_type == 'graded')
                                                                                                        Проверено
                                                                                                    @elseif($historyItem->action_type == 'started')
                                                                                                        Начато
                                                                                                    @elseif($historyItem->action_type == 'completed')
                                                                                                        Завершено
                                                                                                    @else
                                                                                                        {{ $historyItem->action_type }}
                                                                                                    @endif
                                                                                                </strong>
                                                                                                <small class="text-muted">{{ $historyItem->created_at->format('d.m.Y H:i') }}</small>
                                                                                            </div>
                                                                                            @if($historyItem->description)
                                                                                                <div class="mt-1">{{ $historyItem->description }}</div>
                                                                                            @endif
                                                                                            @if($historyItem->performedBy)
                                                                                                <small class="text-muted">Выполнено: {{ $historyItem->performedBy->name }}</small>
                                                                                            @endif
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Нет истории</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>Нет данных аналитики. Запустите синхронизацию элементов курса.
            </div>
        @endif
    </div>

    <!-- Tab: Сертификаты и дипломы -->
    <div id="tab-certificates" class="tab-content {{ request('tab') === 'certificates' ? 'active' : '' }}">
        <div class="empty-state">
            <i class="fas fa-certificate"></i>
            <h5>Сертификаты и дипломы</h5>
            <p>Функционал сертификатов и дипломов будет добавлен в ближайшее время</p>
        </div>
    </div>
</div>

<script>
function switchTab(evt, tabName) {
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
    evt.currentTarget.classList.add('active');
}
</script>
@endsection
