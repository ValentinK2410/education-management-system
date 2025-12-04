@extends('layouts.app')

@section('title', 'Панель управления')

@push('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --info-gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --purple-gradient: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    /* Dashboard Hero */
    .dashboard-hero {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 0;
        margin-bottom: 3rem;
        border-radius: 0 0 2rem 2rem;
        position: relative;
        overflow: hidden;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
        opacity: 0.3;
    }

    .dashboard-hero-content {
        position: relative;
        z-index: 2;
    }

    .dashboard-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        font-size: 1.125rem;
        opacity: 0.95;
    }

    /* Admin Cards */
    .admin-card {
        background: white;
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .admin-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-gradient);
    }

    .admin-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .admin-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .admin-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .admin-card-icon {
        font-size: 2.5rem;
        opacity: 0.2;
        background: var(--card-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .admin-card-description {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .admin-card-btn {
        background: var(--card-gradient);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
        border: none;
    }

    .admin-card-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        color: white;
    }

    .admin-card:nth-child(1) {
        --card-gradient: var(--primary-gradient);
    }

    .admin-card:nth-child(2) {
        --card-gradient: var(--success-gradient);
    }

    .admin-card:nth-child(3) {
        --card-gradient: var(--warning-gradient);
    }

    .admin-card:nth-child(4) {
        --card-gradient: var(--info-gradient);
    }

    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .profile-header {
        background: var(--primary-gradient);
        padding: 2rem;
        text-align: center;
        color: white;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin: 0 auto 1rem;
        object-fit: cover;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .profile-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin: 0 auto 1rem;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
    }

    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .profile-email {
        font-size: 0.95rem;
        opacity: 0.9;
    }

    .profile-body {
        padding: 2rem;
    }

    .profile-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .profile-info-item:last-child {
        border-bottom: none;
    }

    .profile-info-label {
        font-weight: 600;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .profile-info-label i {
        color: #667eea;
        width: 20px;
    }

    .profile-info-value {
        color: #1e293b;
        font-weight: 500;
    }

    /* Quick Actions Card */
    .quick-actions-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .quick-actions-header {
        background: linear-gradient(to right, #f8fafc 0%, #ffffff 100%);
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .quick-actions-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .quick-actions-title i {
        color: #667eea;
    }

    .quick-actions-body {
        padding: 1.5rem;
    }

    .quick-action-btn {
        width: 100%;
        padding: 1rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        margin-bottom: 0.75rem;
        border: 2px solid transparent;
    }

    .quick-action-btn:last-child {
        margin-bottom: 0;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .quick-action-btn-primary {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
    }

    .quick-action-btn-primary:hover {
        color: white;
    }

    .quick-action-btn-success {
        background: var(--success-gradient);
        color: white;
        border-color: transparent;
    }

    .quick-action-btn-success:hover {
        color: white;
    }

    .quick-action-btn-warning {
        background: var(--warning-gradient);
        color: white;
        border-color: transparent;
    }

    .quick-action-btn-warning:hover {
        color: white;
    }

    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .dashboard-title {
            font-size: 2rem;
        }

        .dashboard-subtitle {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<!-- Dashboard Hero -->
<div class="dashboard-hero">
    <div class="container dashboard-hero-content">
        <h1 class="dashboard-title">Панель управления</h1>
        <p class="dashboard-subtitle">Добро пожаловать, {{ auth()->user()->name }}!</p>
    </div>
</div>

<div class="container pb-5">
    @if(auth()->user()->isAdmin())
        <!-- Admin Dashboard -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h2 class="fw-bold">
                    <i class="fas fa-cog me-2" style="color: #667eea;"></i>
                    Административная панель
                </h2>
                <p class="text-muted">Управление системой и контентом</p>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">Пользователи</h5>
                        <i class="fas fa-users admin-card-icon"></i>
                    </div>
                    <p class="admin-card-description">Управление пользователями системы</p>
                    <a href="{{ route('admin.users.index') }}" class="admin-card-btn">
                        <i class="fas fa-cog"></i>
                        Управлять
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">Заведения</h5>
                        <i class="fas fa-university admin-card-icon"></i>
                    </div>
                    <p class="admin-card-description">Управление учебными заведениями</p>
                    <a href="{{ route('admin.institutions.index') }}" class="admin-card-btn">
                        <i class="fas fa-cog"></i>
                        Управлять
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">Программы</h5>
                        <i class="fas fa-book admin-card-icon"></i>
                    </div>
                    <p class="admin-card-description">Управление образовательными программами</p>
                    <a href="{{ route('admin.programs.index') }}" class="admin-card-btn">
                        <i class="fas fa-cog"></i>
                        Управлять
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5 class="admin-card-title">Курсы</h5>
                        <i class="fas fa-chalkboard-teacher admin-card-icon"></i>
                    </div>
                    <p class="admin-card-description">Управление учебными курсами</p>
                    <a href="{{ route('admin.courses.index') }}" class="admin-card-btn">
                        <i class="fas fa-cog"></i>
                        Управлять
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- User Profile and Quick Actions -->
    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-8">
            <div class="profile-card">
                <div class="profile-header">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             class="profile-avatar"
                             alt="Avatar"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-avatar-placeholder" style="display: none;">
                            <i class="fas fa-user"></i>
                        </div>
                    @else
                        <div class="profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="profile-name">{{ auth()->user()->name }}</div>
                    <div class="profile-email">{{ auth()->user()->email }}</div>
                </div>
                <div class="profile-body">
                    <div class="profile-info-item">
                        <div class="profile-info-label">
                            <i class="fas fa-user"></i>
                            <span>Имя</span>
                        </div>
                        <div class="profile-info-value">{{ auth()->user()->name }}</div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">
                            <i class="fas fa-envelope"></i>
                            <span>Email</span>
                        </div>
                        <div class="profile-info-value">{{ auth()->user()->email }}</div>
                    </div>
                    @if(auth()->user()->phone)
                        <div class="profile-info-item">
                            <div class="profile-info-label">
                                <i class="fas fa-phone"></i>
                                <span>Телефон</span>
                            </div>
                            <div class="profile-info-value">{{ auth()->user()->phone }}</div>
                        </div>
                    @endif
                    <div class="profile-info-item">
                        <div class="profile-info-label">
                            <i class="fas fa-shield-alt"></i>
                            <span>Роли</span>
                        </div>
                        <div class="profile-info-value">
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge badge-custom" style="background: var(--primary-gradient); color: white; margin-right: 0.5rem;">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="profile-info-label">
                            <i class="fas fa-circle"></i>
                            <span>Статус</span>
                        </div>
                        <div class="profile-info-value">
                            @if(auth()->user()->is_active)
                                <span class="badge badge-custom" style="background: var(--success-gradient); color: white;">
                                    Активен
                                </span>
                            @else
                                <span class="badge badge-custom" style="background: var(--danger-gradient); color: white;">
                                    Неактивен
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="col-lg-4">
            <div class="quick-actions-card">
                <div class="quick-actions-header">
                    <h5 class="quick-actions-title">
                        <i class="fas fa-bolt"></i>
                        Быстрые действия
                    </h5>
                </div>
                <div class="quick-actions-body">
                    <a href="{{ route('institutions.index') }}" class="quick-action-btn quick-action-btn-primary">
                        <i class="fas fa-university"></i>
                        Учебные заведения
                    </a>
                    <a href="{{ route('programs.index') }}" class="quick-action-btn quick-action-btn-success">
                        <i class="fas fa-book"></i>
                        Программы
                    </a>
                    <a href="{{ route('courses.index') }}" class="quick-action-btn quick-action-btn-warning">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Курсы
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
