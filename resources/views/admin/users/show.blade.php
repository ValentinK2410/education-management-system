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

    .course-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .course-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .course-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .course-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: #64748b;
    }

    .course-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-meta-item i {
        color: #667eea;
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

<div class="row">
    <!-- Основная информация -->
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

    <!-- Роли и биография -->
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

<!-- Преподаваемые курсы -->
@if($user->taughtCourses->count() > 0)
    <div class="info-card">
        <h5 class="info-card-title">
            <i class="fas fa-chalkboard-teacher"></i>
            Преподаваемые курсы ({{ $user->taughtCourses->count() }})
        </h5>
        <div class="row">
            @foreach($user->taughtCourses as $course)
                <div class="col-md-6 col-lg-4">
                    <div class="course-card">
                        <div class="course-title">{{ $course->name }}</div>
                        <div class="course-meta">
                            <div class="course-meta-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>{{ $course->program->name }}</span>
                            </div>
                            <div class="course-meta-item">
                                <i class="fas fa-university"></i>
                                <span>{{ $course->program->institution->name }}</span>
                            </div>
                            <div class="course-meta-item">
                                @if($course->is_active)
                                    <span class="badge-custom badge-success">Активен</span>
                                @else
                                    <span class="badge-custom badge-secondary">Неактивен</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
