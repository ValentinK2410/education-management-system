@extends('layouts.admin')

@section('title', 'Просмотр роли')
@section('page-title', 'Просмотр роли')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .role-header-card {
        background: var(--primary-gradient);
        color: white;
        border-radius: 1rem;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .role-icon-section {
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .role-icon-large {
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

    .role-header-info h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .role-header-info p {
        font-size: 1.125rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }

    .role-actions {
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

    .badge-primary {
        background: var(--primary-gradient);
        color: white;
    }

    .badge-info {
        background: var(--info-gradient);
        color: white;
    }

    .item-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
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
        margin-bottom: 0.5rem;
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
    }

    .item-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .item-meta-item i {
        color: #667eea;
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

    .description-section {
        background: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.5rem;
        border-left: 4px solid #667eea;
        margin-top: 1rem;
    }

    .description-text {
        color: #475569;
        line-height: 1.7;
        margin: 0;
    }

    @media (max-width: 768px) {
        .role-icon-section {
            flex-direction: column;
            text-align: center;
        }

        .role-actions {
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
<!-- Role Header Card -->
<div class="role-header-card">
    <div class="role-icon-section">
        <div class="role-icon-large">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="role-header-info">
            <h2>{{ $role->name }}</h2>
            <p><i class="fas fa-code me-2"></i>{{ $role->slug }}</p>
            @if($role->description)
                <p><i class="fas fa-info-circle me-2"></i>{{ $role->description }}</p>
            @endif
        </div>
    </div>
    <div class="role-actions">
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn-action btn-edit">
            <i class="fas fa-edit"></i>
            Редактировать
        </a>
        <a href="{{ route('admin.roles.index') }}" class="btn-action btn-back">
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
                <div class="info-value">#{{ $role->id }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-tag"></i>
                    <span>Название</span>
                </div>
                <div class="info-value">{{ $role->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-code"></i>
                    <span>Слаг</span>
                </div>
                <div class="info-value"><code>{{ $role->slug }}</code></div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-calendar"></i>
                    <span>Создана</span>
                </div>
                <div class="info-value">{{ $role->created_at->format('d.m.Y H:i') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-edit"></i>
                    <span>Обновлена</span>
                </div>
                <div class="info-value">{{ $role->updated_at->format('d.m.Y H:i') }}</div>
            </div>
            @if($role->description)
                <div class="description-section">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-align-left me-2"></i>
                        Описание
                    </h6>
                    <p class="description-text">{{ $role->description }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Статистика -->
    <div class="col-lg-6">
        <div class="info-card">
            <h5 class="info-card-title">
                <i class="fas fa-chart-bar"></i>
                Статистика
            </h5>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-key"></i>
                    <span>Разрешений</span>
                </div>
                <div class="info-value">
                    <span class="badge-custom badge-info">{{ $role->permissions->count() }}</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">
                    <i class="fas fa-users"></i>
                    <span>Пользователей</span>
                </div>
                <div class="info-value">
                    <span class="badge-custom badge-primary">{{ $role->users->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Разрешения -->
@if($role->permissions->count() > 0)
    <div class="info-card">
        <h5 class="info-card-title">
            <i class="fas fa-key"></i>
            Разрешения ({{ $role->permissions->count() }})
        </h5>
        <div class="row">
            @foreach($role->permissions as $permission)
                <div class="col-md-6 col-lg-4">
                    <div class="item-card">
                        <div class="item-title">{{ $permission->name }}</div>
                        <div class="item-meta">
                            <div class="item-meta-item">
                                <i class="fas fa-code"></i>
                                <code>{{ $permission->slug }}</code>
                            </div>
                        </div>
                        @if($permission->description)
                            <p class="text-muted small mt-2 mb-0">{{ $permission->description }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="info-card">
        <div class="empty-state">
            <i class="fas fa-key"></i>
            <h5>Разрешения не назначены</h5>
            <p>У этой роли нет назначенных разрешений</p>
        </div>
    </div>
@endif

<!-- Пользователи с этой ролью -->
@if($role->users->count() > 0)
    <div class="info-card">
        <h5 class="info-card-title">
            <i class="fas fa-users"></i>
            Пользователи с этой ролью ({{ $role->users->count() }})
        </h5>
        <div class="row">
            @foreach($role->users as $user)
                <div class="col-md-6 col-lg-4">
                    <div class="item-card">
                        <div class="item-title">
                            <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                        </div>
                        <div class="item-meta">
                            <div class="item-meta-item">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="info-card">
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h5>Пользователи не найдены</h5>
            <p>Нет пользователей с этой ролью</p>
        </div>
    </div>
@endif
@endsection
