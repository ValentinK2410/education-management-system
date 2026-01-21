@extends('layouts.admin')

@section('title', __('messages.courses_list'))
@section('page-title', __('messages.courses'))

@push('styles')
<style>
    /* Темная тема для страницы курсов */
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

    [data-theme="dark"] .container-fluid .text-warning {
        color: var(--warning-color) !important;
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

    [data-theme="dark"] .container-fluid .btn-secondary {
        background-color: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-secondary:hover {
        background-color: #475569 !important;
        border-color: #475569 !important;
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

    [data-theme="dark"] .container-fluid .btn-outline-primary {
        border-color: var(--primary-color) !important;
        color: var(--primary-color) !important;
    }

    [data-theme="dark"] .container-fluid .btn-outline-primary:hover {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: white !important;
    }

    /* Аватар */
    [data-theme="dark"] .container-fluid .avatar-title.bg-success {
        background-color: rgba(16, 185, 129, 0.8) !important;
        color: white !important;
    }

    /* Чекбоксы */
    [data-theme="dark"] .container-fluid input[type="checkbox"] {
        filter: brightness(0.8);
    }

    /* Строки с заданиями */
    [data-theme="dark"] .container-fluid .assignment-row {
        background-color: var(--dark-bg) !important;
    }

    [data-theme="dark"] .container-fluid .bg-light {
        background-color: var(--dark-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .container-fluid .assignment-card {
        background-color: var(--card-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .assignment-card .card-body {
        background-color: var(--card-bg) !important;
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .assignment-card .card-title {
        color: var(--text-color) !important;
    }

    [data-theme="dark"] .container-fluid .alert-info {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
        color: var(--text-color) !important;
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
                        <i class="fas fa-chalkboard-teacher me-2"></i>{{ __('messages.courses') }}
                        @if(!$isAdmin)
                            <small class="text-muted ms-2">({{ __('messages.my_courses') }})</small>
                        @endif
                    </h3>
                    <div class="d-flex gap-2">
                        @if($isAdmin)
                            <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                                <i class="fas fa-trash me-2"></i>{{ __('messages.delete_selected') }}
                            </button>
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>{{ __('messages.add_course') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($isAdmin)
                        @php
                            $filters = $filters ?? [];
                            $qValue = old('q', request('q', $filters['q'] ?? ''));
                            $instructorValue = old('instructor', request('instructor', $filters['instructor'] ?? ''));
                            $perPageValue = (int) request('per_page', $filters['per_page'] ?? 25);
                        @endphp
                        <form method="GET" action="{{ route('admin.courses.index') }}" class="mb-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label mb-1">Поиск курса</label>
                                    <input type="text" name="q" class="form-control" value="{{ $qValue }}"
                                           placeholder="Название / код / Moodle ID">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">Преподаватель</label>
                                    <input type="text" name="instructor" class="form-control" value="{{ $instructorValue }}"
                                           placeholder="Имя или email преподавателя">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">Показывать</label>
                                    <select name="per_page" class="form-select">
                                        @foreach([10, 25, 50, 100, 200] as $pp)
                                            <option value="{{ $pp }}" @selected($perPageValue === $pp)>{{ $pp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <a href="{{ route('admin.courses.index', ['reset' => 1]) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-undo me-1"></i>Сбросить
                                    </a>
                                    <small class="text-muted align-self-center">
                                        Настройки поиска и “показывать” сохраняются для вашего пользователя.
                                    </small>
                                </div>
                            </div>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    @if($isAdmin)
                                        <th style="width: 40px;">
                                            <input type="checkbox" id="selectAll" title="{{ __('messages.select_all') }}">
                                        </th>
                                    @endif
                                    <th>ID</th>
                                    <th>{{ __('messages.course_name') }}</th>
                                    <th>{{ __('messages.program') }}</th>
                                    <th>{{ __('messages.instructor') }}</th>
                                    <th>{{ __('messages.payment_type') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!$isAdmin && $courses->count() == 0)
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                                <p class="mb-2">{{ __('messages.not_enrolled_in_courses') }}</p>
                                                <p class="small">{{ __('messages.contact_admin_or_sync') }}</p>
                                                @if(auth()->user()->moodle_user_id)
                                                    <a href="{{ route('admin.moodle-sync.index') }}" class="btn btn-sm btn-primary mt-2">
                                                        <i class="fas fa-sync me-1"></i>{{ __('messages.sync_with_moodle') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                @forelse($courses as $course)
                                    <tr>
                                        @if($isAdmin)
                                            <td>
                                                <input type="checkbox" class="course-checkbox" value="{{ $course->id }}" data-course-name="{{ $course->name }}">
                                            </td>
                                        @endif
                                        <td><span class="badge bg-secondary">{{ $course->id }}</span></td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $course->name }}</h6>
                                                <small class="text-muted">{{ $course->code ?? __('messages.no_code') }}</small>
                                                @if(!$isAdmin)
                                                    @if(isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block mb-1">
                                                                <i class="fas fa-tasks me-1"></i>{{ __('messages.after_session') }}:
                                                            </small>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @foreach($coursesWithAssignments[$course->id] as $assignment)
                                                                    @php
                                                                        $moodleApiService = new \App\Services\MoodleApiService();
                                                                        $assignmentUrl = $moodleApiService->getAssignmentUrl(
                                                                            $assignment['cmid'] ?? null,
                                                                            $assignment['id'] ?? null,
                                                                            $course->moodle_course_id ?? null
                                                                        );
                                                                    @endphp
                                                                    @if($assignmentUrl && ($assignment['status'] === 'not_submitted' || $assignment['status'] === 'pending'))
                                                                        <a href="{{ $assignmentUrl }}" target="_blank" class="text-decoration-none">
                                                                            <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                                                  title="{{ $assignment['name'] }}: {{ $assignment['status_text'] }} - Нажмите для сдачи">
                                                                                @if($assignment['status'] === 'not_submitted')
                                                                                    <i class="fas fa-times-circle me-1"></i>{{ __('messages.not_submitted') }}
                                                                                @elseif($assignment['status'] === 'pending')
                                                                                    <i class="fas fa-clock me-1"></i>{{ __('messages.not_graded') }}
                                                                                @else
                                                                                    <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                                                @endif
                                                                            </span>
                                                                        </a>
                                                                    @else
                                                                        <span class="badge assignment-mini-badge assignment-status-{{ $assignment['status'] }}" 
                                                                              title="{{ $assignment['name'] }}: {{ $assignment['status_text'] }}">
                                                                            @if($assignment['status'] === 'not_submitted')
                                                                                        <i class="fas fa-times-circle me-1"></i>{{ __('messages.not_submitted') }}
                                                                            @elseif($assignment['status'] === 'pending')
                                                                                        <i class="fas fa-clock me-1"></i>{{ __('messages.not_graded') }}
                                                                            @else
                                                                                <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                                            @endif
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @elseif($course->moodle_course_id && auth()->user()->moodle_user_id)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>{{ __('messages.no_activities') }}
                                                            </small>
                                                        </div>
                                                    @elseif(!auth()->user()->moodle_user_id)
                                                        <div class="mt-2">
                                                            <small class="text-warning">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>{{ __('messages.synchronization_not_configured') }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($course->program)
                                                <span class="badge bg-info">{{ $course->program->name }}</span>
                                            @else
                                                <span class="text-muted">{{ __('messages.not_specified') }}</span>
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
                                                <span class="text-muted">{{ __('messages.not_assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_paid)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-dollar-sign me-1"></i>{{ __('messages.paid_course') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-gift me-1"></i>{{ __('messages.free_course') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_paid && $course->price)
                                                <strong>{{ number_format($course->price, 0) }} {{ $course->currency ?? 'USD' }}</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($course->is_active)
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
                                                <a href="{{ route('admin.courses.show', $course) }}"
                                                   class="btn btn-sm btn-info" title="{{ __('messages.view') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($isAdmin)
                                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                                       class="btn btn-sm btn-warning" title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.courses.duplicate', $course) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary"
                                                                title="{{ __('messages.duplicate_course') }}"
                                                                onclick="return confirm('{{ __('messages.create_course_copy') }}')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.courses.destroy', $course) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                title="{{ __('messages.delete') }}"
                                                                onclick="return confirm('{{ __('messages.confirm_delete_course') }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(!$isAdmin && $course->moodle_course_id && auth()->user()->moodle_user_id)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary assignment-toggle"
                                                            data-course-id="{{ $course->id }}"
                                                            title="@if(isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id])){{ __('messages.show_assignments') }}@else{{ __('messages.show_assignment_info') }}@endif">
                                                        <i class="fas fa-tasks"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if(!$isAdmin && isset($coursesWithAssignments[$course->id]) && !empty($coursesWithAssignments[$course->id]))
                                        <tr class="assignment-row" id="assignments-{{ $course->id }}" style="display: none;">
                                            <td colspan="8">
                                                <div class="p-4 bg-light border-top">
                                                    <h6 class="mb-3 fw-bold">
                                                        <i class="fas fa-tasks me-2 text-primary"></i>{{ __('messages.after_session') }}
                                                    </h6>
                                                    <div class="row g-3">
                                                        @foreach($coursesWithAssignments[$course->id] as $assignment)
                                                            <div class="col-md-12">
                                                                <div class="card assignment-card assignment-status-{{ $assignment['status'] }} shadow-sm">
                                                                    <div class="card-body py-3">
                                                                        <div class="d-flex justify-content-between align-items-start">
                                                                            <div class="flex-grow-1">
                                                                                <h6 class="card-title mb-2 fw-bold">{{ $assignment['name'] }}</h6>
                                                                                @if($assignment['submitted_at'])
                                                                                    <small class="text-muted d-block mb-1">
                                                                                        <i class="fas fa-calendar-check me-1"></i>
                                                                                        {{ __('messages.submitted') }}: {{ \Carbon\Carbon::createFromTimestamp($assignment['submitted_at'])->format('d.m.Y H:i') }}
                                                                                    </small>
                                                                                @endif
                                                                                @if($assignment['graded_at'])
                                                                                    <small class="text-muted d-block">
                                                                                        <i class="fas fa-check-double me-1"></i>
                                                                                        {{ __('messages.graded') }}: {{ \Carbon\Carbon::createFromTimestamp($assignment['graded_at'])->format('d.m.Y H:i') }}
                                                                                    </small>
                                                                                @endif
                                                                            </div>
                                                                            <div class="ms-3">
                                                                                <div class="assignment-status-badge assignment-status-{{ $assignment['status'] }}">
                                                                                    @if($assignment['status'] === 'not_submitted')
                                                                                        <i class="fas fa-times-circle me-1"></i>{{ __('messages.not_submitted') }}
                                                                                    @elseif($assignment['status'] === 'pending')
                                                                                        <i class="fas fa-clock me-1"></i>{{ __('messages.not_graded') }}
                                                                                    @else
                                                                                        <i class="fas fa-check-circle me-1"></i>{{ $assignment['status_text'] }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @elseif(!$isAdmin && $course->moodle_course_id && auth()->user()->moodle_user_id)
                                        <tr class="assignment-row" id="assignments-{{ $course->id }}" style="display: none;">
                                            <td colspan="8">
                                                <div class="p-4 bg-light border-top">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        {{ __('messages.assignments_not_found_after_session') }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                                                <p>{{ __('messages.courses_not_found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($courses->hasPages())
                        <div class="d-flex justify-content-center mt-4 pagination-wrapper">
                            {{ $courses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
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

/* Стили для уменьшения размера пагинации (Bootstrap) */
.pagination-wrapper .pagination {
    margin-bottom: 0 !important;
    font-size: 0.875rem !important;
}

.pagination-wrapper .pagination .page-link {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.4 !important;
    min-width: 32px !important;
    height: 32px !important;
    text-align: center !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.pagination-wrapper .pagination .page-item {
    margin: 0 1px !important;
}

.pagination-wrapper .pagination .page-item:first-child .page-link {
    border-top-left-radius: 0.375rem !important;
    border-bottom-left-radius: 0.375rem !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item:last-child .page-link {
    border-top-right-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3 !important;
    color: #fff !important;
    background-color: #6366f1 !important;
    border-color: #6366f1 !important;
    padding: 0.25rem 0.5rem !important;
}

.pagination-wrapper .pagination .page-link i {
    font-size: 0.75rem !important;
}

.pagination-wrapper .pagination .page-link span {
    font-size: 0.875rem !important;
}

/* Стили для Laravel Tailwind пагинации */
.pagination-wrapper nav[role="navigation"] a,
.pagination-wrapper nav[role="navigation"] span[aria-disabled="true"] span,
.pagination-wrapper nav[role="navigation"] span[aria-current="page"] span {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    line-height: 1.4 !important;
    min-width: 32px !important;
    height: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.pagination-wrapper nav[role="navigation"] svg {
    width: 1rem !important;
    height: 1rem !important;
}

.pagination-wrapper nav[role="navigation"] .text-sm {
    font-size: 0.875rem !important;
}

/* Переопределение Tailwind padding классов в пагинации */
.pagination-wrapper nav[role="navigation"] .px-4 {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}

.pagination-wrapper nav[role="navigation"] .py-2 {
    padding-top: 0.25rem !important;
    padding-bottom: 0.25rem !important;
}

.pagination-wrapper nav[role="navigation"] .px-2 {
    padding-left: 0.25rem !important;
    padding-right: 0.25rem !important;
}

/* Уменьшаем размер иконок в пагинации */
.pagination-wrapper nav[role="navigation"] .w-5 {
    width: 1rem !important;
}

.pagination-wrapper nav[role="navigation"] .h-5 {
    height: 1rem !important;
}

/* Стили для заданий */
.assignment-card {
    border-left: 4px solid;
    transition: transform 0.2s, box-shadow 0.2s;
}

.assignment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Мини-бейджи для статусов в таблице */
.assignment-mini-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    white-space: nowrap;
}

/* Красный - не сдано */
.assignment-status-not-submitted {
    border-left-color: #dc3545;
    background-color: #fff5f5;
}

.assignment-status-not-submitted .assignment-status-badge,
.assignment-mini-badge {
    border-radius: 0.375rem;
    border: 2px solid transparent;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.1);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    font-weight: 700;
    padding: 0.4rem 0.7rem;
}

.assignment-mini-badge.assignment-status-not-submitted,
.assignment-mini-badge.assignment-status-not_submitted {
    background-color: #b91c1c;
    color: #ffffff;
    border-color: #991b1b;
    box-shadow: 0 2px 6px rgba(185, 28, 28, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}

/* Желтый - не проверено */
.assignment-status-pending {
    border-left-color: #d97706;
    background-color: #fffbf0;
}

.assignment-status-pending .assignment-status-badge,
.assignment-mini-badge.assignment-status-pending {
    background-color: #d97706;
    color: #ffffff;
    border-color: #b45309;
    box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}

/* Зеленый - оценка */
.assignment-status-graded {
    border-left-color: #059669;
    background-color: #f0fff4;
}

.assignment-status-graded .assignment-status-badge,
.assignment-mini-badge.assignment-status-graded {
    background-color: #059669;
    color: #ffffff;
    border-color: #047857;
    box-shadow: 0 2px 6px rgba(5, 150, 105, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.2);
}

.assignment-status-badge {
    white-space: nowrap;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.assignment-row {
    background-color: #f8f9fa;
}
</style>

@if(!$isAdmin)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.assignment-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            const assignmentRow = document.getElementById('assignments-' + courseId);
            const icon = this.querySelector('i');
            
            if (assignmentRow.style.display === 'none') {
                assignmentRow.style.display = '';
                icon.classList.remove('fa-tasks');
                icon.classList.add('fa-chevron-up');
                this.setAttribute('title', 'Скрыть задания');
            } else {
                assignmentRow.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-tasks');
                this.setAttribute('title', 'Показать задания');
            }
        });
    });
});
</script>
@endif

@if($isAdmin)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translations for JavaScript
    const courseTranslations = {
        delete_selected: '{{ __('messages.delete_selected') }}',
        select_at_least_one: '{{ __('messages.select_at_least_one_course') }}',
        confirm_delete_courses: '{{ __('messages.confirm_delete_courses') }}',
        action_cannot_be_undone: '{{ __('messages.action_cannot_be_undone') }}',
        courses_deleted_successfully: '{{ __('messages.courses_deleted_successfully') }}',
        error_deleting_courses: '{{ __('messages.error_deleting_courses') }}'
    };
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const courseCheckboxes = document.querySelectorAll('.course-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    
    // Обработчик для "Выбрать все"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            courseCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }
    
    // Обработчики для отдельных чекбоксов
    courseCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });
    
    // Обновление состояния "Выбрать все"
    function updateSelectAllCheckbox() {
        if (selectAllCheckbox) {
            const allChecked = Array.from(courseCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(courseCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }
    
    // Обновление видимости кнопки массового удаления
    function updateBulkDeleteButton() {
        if (bulkDeleteBtn) {
            const checkedCount = Array.from(courseCheckboxes).filter(cb => cb.checked).length;
            if (checkedCount > 0) {
                bulkDeleteBtn.style.display = 'inline-block';
                bulkDeleteBtn.innerHTML = `<i class="fas fa-trash me-2"></i>${courseTranslations.delete_selected} (${checkedCount})`;
            } else {
                bulkDeleteBtn.style.display = 'none';
            }
        }
    }
    
    // Обработчик массового удаления
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedIds = Array.from(courseCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                alert(courseTranslations.select_at_least_one);
                return;
            }
            
            const courseNames = Array.from(courseCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.courseName)
                .join(', ');
            
            if (!confirm(`${courseTranslations.confirm_delete_courses}\n\n${courseNames}\n\n${courseTranslations.action_cannot_be_undone}`)) {
                return;
            }
            
            // Отправка запроса на удаление
            fetch('{{ route("admin.courses.bulk-destroy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: selectedIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Показываем сообщение об успехе
                    if (data.message) {
                        alert(data.message);
                    }
                    
                    // Перезагружаем страницу
                    window.location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при удалении курсов');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении курсов');
            });
        });
    }
});
</script>
@endpush
@endif
@endsection
