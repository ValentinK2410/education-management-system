@extends('layouts.admin')

@section('title', 'Шаблоны сертификатов')

@push('styles')
<style>
    /* Основная карточка */
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

    /* Заголовки */
    [data-theme="dark"] .container-fluid h3,
    [data-theme="dark"] .container-fluid h6,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
    }

    /* Форма поиска */
    [data-theme="dark"] .container-fluid .form-control {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .form-control:focus {
        background-color: var(--card-bg) !important;
        border-color: #6366f1 !important;
        color: var(--text-color) !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25) !important;
    }

    [data-theme="dark"] .container-fluid .form-control::placeholder {
        color: #94a3b8 !important;
        opacity: 0.6;
    }

    /* Кнопки */
    [data-theme="dark"] .container-fluid .btn-outline-secondary {
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-secondary:hover {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-danger {
        border-color: var(--danger-color) !important;
        color: var(--danger-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-danger:hover {
        background-color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
        color: white !important;
    }

    /* Таблица */
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

    /* Бейджи */
    [data-theme="dark"] .container-fluid .badge.bg-secondary {
        background-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-primary {
        background-color: var(--primary-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-light {
        background-color: var(--card-bg) !important;
        color: var(--text-color) !important;
        border: 1px solid var(--border-color) !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-light.text-dark {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-danger {
        background-color: rgba(239, 68, 68, 0.8) !important;
        color: white !important;
    }

    /* Алерт */
    [data-theme="dark"] .container-fluid .alert-info {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .alert-info strong {
        color: var(--text-color) !important;
    }

    /* Текст */
    [data-theme="dark"] .container-fluid .text-muted {
        color: #94a3b8 !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .container-fluid .text-success {
        color: rgba(16, 185, 129, 0.9) !important;
    }

    /* Кнопки действий */
    [data-theme="dark"] .container-fluid .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger {
        background-color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-certificate me-2"></i>Шаблоны сертификатов
                    </h3>
                    <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Создать шаблон
                    </a>
                </div>
                <div class="card-body">
                    <!-- Форма поиска -->
                    <form method="GET" action="{{ route('admin.certificate-templates.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text"
                                           name="search"
                                           class="form-control"
                                           placeholder="Поиск по названию или описанию..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search me-2"></i>Найти
                                    </button>
                                    @if(request('search'))
                                        <a href="{{ route('admin.certificate-templates.index') }}" class="btn btn-outline-danger">
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th>Размер</th>
                                    <th>Качество</th>
                                    <th>Фон</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $template->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $template->name }}</h6>
                                                @if($template->is_default)
                                                    <small class="text-success"><i class="fas fa-star"></i> По умолчанию</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($template->type === 'course')
                                                <span class="badge bg-info">Курс</span>
                                            @else
                                                <span class="badge bg-primary">Программа</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->width }}x{{ $template->height }}px</td>
                                        <td>{{ $template->quality }}%</td>
                                        <td>
                                            @if($template->background_type === 'color')
                                                <span class="badge bg-light text-dark">Цвет</span>
                                            @elseif($template->background_type === 'image')
                                                <span class="badge bg-light text-dark">Изображение</span>
                                            @else
                                                <span class="badge bg-light text-dark">Градиент</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.certificate-templates.edit', $template) }}"
                                                   class="btn btn-sm btn-warning" title="Редактировать">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.certificate-templates.destroy', $template) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Удалить"
                                                            onclick="return confirm('Вы уверены, что хотите удалить этот шаблон?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-certificate fa-3x mb-3"></i>
                                                <p>Шаблоны сертификатов не найдены</p>
                                                <a href="{{ route('admin.certificate-templates.create') }}" class="btn btn-primary">
                                                    Создать первый шаблон
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($templates->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
