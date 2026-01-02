@extends('layouts.admin')

@section('title', 'Архив пользователей')
@section('page-title', 'Архив пользователей')

@push('styles')
<style>
    html[data-theme="dark"] .card,
    [data-theme="dark"] .card {
        background-color: var(--card-bg, #334155) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .card-header,
    [data-theme="dark"] .card-header {
        background-color: var(--card-bg, #334155) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .card-body,
    [data-theme="dark"] .card-body {
        background-color: var(--card-bg, #334155) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .form-control,
    [data-theme="dark"] .form-control {
        background-color: var(--card-bg, #334155) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-control:focus {
        background-color: var(--card-bg, #334155) !important;
        border-color: #6366f1 !important;
        color: var(--text-color, #e2e8f0) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
    }

    html[data-theme="dark"] .form-control::placeholder,
    [data-theme="dark"] .form-control::placeholder {
        color: var(--text-color, #94a3b8) !important;
        opacity: 0.6;
    }

    html[data-theme="dark"] .table,
    [data-theme="dark"] .table {
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .table thead th,
    [data-theme="dark"] .table thead th {
        background-color: var(--dark-bg, #1e293b) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .table tbody td,
    [data-theme="dark"] .table tbody td {
        border-color: var(--border-color, #475569) !important;
        background-color: transparent !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .table-striped tbody tr:nth-of-type(odd),
    [data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--card-bg, #334155) !important;
    }

    html[data-theme="dark"] .table-striped tbody tr:nth-of-type(even),
    [data-theme="dark"] .table-striped tbody tr:nth-of-type(even) {
        background-color: var(--dark-bg, #1e293b) !important;
    }

    html[data-theme="dark"] .table-hover tbody tr:hover,
    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--dark-bg, #1e293b) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .table-hover tbody tr:hover td,
    [data-theme="dark"] .table-hover tbody tr:hover td {
        background-color: var(--dark-bg, #1e293b) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .alert-info,
    [data-theme="dark"] .alert-info {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .text-muted,
    [data-theme="dark"] .text-muted {
        color: var(--text-color, #94a3b8) !important;
        opacity: 0.8;
    }

    html[data-theme="dark"] h3,
    html[data-theme="dark"] .card-title,
    [data-theme="dark"] h3,
    [data-theme="dark"] .card-title {
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] strong,
    [data-theme="dark"] strong {
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .btn-outline-secondary,
    [data-theme="dark"] .btn-outline-secondary {
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
    }

    html[data-theme="dark"] .btn-outline-secondary:hover,
    [data-theme="dark"] .btn-outline-secondary:hover {
        background-color: var(--dark-bg, #1e293b) !important;
        border-color: var(--border-color, #475569) !important;
        color: var(--text-color, #e2e8f0) !important;
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
                        <i class="fas fa-archive me-2"></i>Выберите пользователя для просмотра истории обучения
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Поиск -->
                    <form method="GET" action="{{ route('admin.user-archive.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="Поиск по имени или email..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search me-2"></i>Найти
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.user-archive.index') }}" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(request('search'))
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Найдено результатов по запросу: <strong>"{{ request('search') }}"</strong>
                        </div>
                    @endif

                    <!-- Таблица пользователей -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $user->id }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2" style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <strong>{{ $user->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? '—' }}</td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Активен
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Неактивен
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.user-archive.show', $user) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-history me-1"></i>История обучения
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Пользователи не найдены</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
