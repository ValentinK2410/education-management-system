@extends('layouts.admin')

@section('title', __('messages.programs_list'))
@section('page-title', __('messages.educational_programs'))

@push('styles')
<style>
    /* Темная тема для страницы программ */
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

    [data-theme="dark"] .container-fluid h3,
    [data-theme="dark"] .container-fluid h6,
    [data-theme="dark"] .container-fluid .card-title {
        color: var(--text-color) !important;
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

    /* Текст */
    [data-theme="dark"] .container-fluid .text-muted {
        color: #94a3b8 !important;
        opacity: 0.8;
    }

    [data-theme="dark"] .container-fluid strong {
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

    [data-theme="dark"] .container-fluid .badge.bg-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-warning {
        background-color: rgba(245, 158, 11, 0.8) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .badge.bg-danger {
        background-color: rgba(239, 68, 68, 0.8) !important;
        color: white !important;
    }

    /* Кнопки */
    [data-theme="dark"] .container-fluid .btn-info {
        background-color: rgba(59, 130, 246, 0.8) !important;
        border-color: rgba(59, 130, 246, 0.8) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .btn-info:hover {
        background-color: rgba(59, 130, 246, 1) !important;
        border-color: rgba(59, 130, 246, 1) !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning {
        background-color: var(--warning-color) !important;
        border-color: var(--warning-color) !important;
        color: #1e293b !important;
    }

    [data-theme="dark"] .container-fluid .btn-warning:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger {
        background-color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
        color: white !important;
    }

    [data-theme="dark"] .container-fluid .btn-danger:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
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
                        <i class="fas fa-book me-2"></i>{{ __('messages.educational_programs') }}
                    </h3>
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('messages.add_program') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('messages.course_name') }}</th>
                                    <th>{{ __('messages.institution') }}</th>
                                    <th>{{ __('messages.payment_type') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $program->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $program->name }}</h6>
                                                <small class="text-muted">{{ $program->code ?? __('messages.no_code') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($program->institution)
                                                <span class="badge bg-info">{{ $program->institution->name }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_specified') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_paid)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-dollar-sign me-1"></i>{{ __('messages.paid') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-gift me-1"></i>{{ __('messages.free') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_paid && $program->price)
                                                <strong>{{ number_format($program->price, 0) }} {{ $program->currency ?? 'USD' }}</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($program->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>{{ __('messages.active') }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>{{ __('messages.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.programs.show', $program) }}"
                                                   class="btn btn-sm btn-info" title="{{ __('messages.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.programs.edit', $program) }}"
                                                   class="btn btn-sm btn-warning" title="{{ __('messages.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.programs.destroy', $program) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            title="{{ __('messages.delete') }}"
                                                            onclick="return confirm('{{ __('messages.confirm_delete_program') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-book fa-3x mb-3"></i>
                                                <p>{{ __('messages.programs_not_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($programs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $programs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
